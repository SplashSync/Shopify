<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slince\Shopify\Middleware\MiddlewareInterface;

/**
 * Shopify REST API Throttle Middleware
 *
 * Replaces the broken {@see \Slince\Shopify\Middleware\DelayMiddleware} (which reads a
 * non-existent header `http_x_shopify_shop_api_call_limit` instead of the real
 * `X-Shopify-Shop-Api-Call-Limit` and is therefore a silent no-op).
 *
 * ## Shopify rate limiting model — leaky bucket
 *
 * Shopify REST Admin API uses a per-shop leaky bucket:
 *  - Standard shops:   bucket size 40, leak rate 2 calls/s
 *  - Plus shops:       bucket size 80, leak rate 4 calls/s
 *
 * Each response carries `X-Shopify-Shop-Api-Call-Limit: <used>/<total>`
 * (e.g. "32/40"). When the bucket overflows, Shopify replies with HTTP 429
 * and a `Retry-After: <seconds>` header.
 *
 * ## What this middleware does
 *
 *  1. **Reactive throttling** — reads the call-limit header on every response
 *     and remembers the latest known bucket usage. Before the next request,
 *     if the bucket usage is above `softThreshold`, the middleware sleeps
 *     proportionally to how far past the threshold we are. The further past
 *     threshold, the longer we wait — letting the bucket drain naturally
 *     instead of pushing it to 429. The per-slot wait is auto-tuned to the
 *     leak rate of the observed shop tier (Standard 2/s vs Plus 4/s) by
 *     looking at the bucket size reported in the header — no manual config.
 *
 *  2. **429 retry with Retry-After** — when a request returns 429, the
 *     middleware sleeps for the duration indicated by the `Retry-After`
 *     header (or a 2-second fallback) and retries up to `maxRetries` times.
 *     The retry happens *inside* the middleware so the Slince Client never
 *     sees the 429 and never throws a `TooManyRequestsException` — unless
 *     all retries are exhausted, in which case the final 429 is returned
 *     and the Client throws normally.
 *
 *  3. **Multi-tenant safe** — all state (last bucket, sleep deadline) is
 *     instance-level, not class-level static. Multiple Slince Clients can
 *     coexist in the same process without their throttling state colliding.
 *
 * ## Why retry inside the middleware
 *
 * The Slince Client pipeline is:
 *
 *     middleware->handle()           ← we run here, can intercept the 429
 *     -> raiseException()            ← throws TooManyRequestsException on 429
 *
 * If we let a 429 response leak out of the middleware, the Client throws
 * before we ever get a chance to retry. The handle() loop below ensures we
 * either return a non-429 response (success path) or exhaust our retries
 * before letting the 429 reach `raiseException()`.
 *
 * ## Re-sending the same request is safe
 *
 *  - PSR-7 RequestInterface is immutable, so the request object can be re-used
 *  - The body for POST/PUT lives in the Client's `$options` array (passed via
 *    the `$next` closure), which Guzzle re-serializes on every send()
 *  - HTTP 429 semantically means "I rejected this, please retry" — unlike 5xx
 *    where partial server-side processing is possible. So retrying writes
 *    (POST/PUT/DELETE) on 429 is intentionally allowed
 *
 * ## Wiring
 *
 *     new \Slince\Shopify\Client($shop, $credential, array(
 *         // ... existing options
 *         'middlewares' => new \Slince\Shopify\Middleware\MiddlewareChain(array(
 *             new ShopifyThrottleMiddleware(),
 *         )),
 *     ));
 *
 * If the `middlewares` option is omitted, the Slince Client falls back to the
 * broken `DelayMiddleware`. Always pass an explicit chain when using this class.
 */
class ShopifyThrottleMiddleware implements MiddlewareInterface
{
    //==============================================================================
    // Defaults
    //==============================================================================

    /**
     * Default soft threshold — start preemptive sleeping at 75 % of bucket usage.
     * Picked to give ~10 calls of headroom on a standard 40-bucket before 429.
     */
    private const DEFAULT_SOFT_THRESHOLD = 0.75;

    /**
     * Default maximum number of in-middleware retries on HTTP 429.
     * 3 × Retry-After (~6 s typical) is enough to absorb a short burst,
     * past that we let the Slince Client throw normally.
     */
    private const DEFAULT_MAX_RETRIES = 3;

    /**
     * Fallback delay (seconds) when the 429 response carries no usable
     * Retry-After header. Aligned with Shopify's default 2 calls/s leak rate
     * so that 1 fallback wait roughly drains 4 bucket slots.
     */
    private const FALLBACK_RETRY_AFTER_S = 2.0;

    /**
     * Default bucket size assumed before we have seen any response. 40 is the
     * Shopify Admin REST baseline for non-Plus shops. Will be overwritten the
     * first time we see a real `X-Shopify-Shop-Api-Call-Limit` header.
     */
    private const DEFAULT_BUCKET_TOTAL = 40;

    /**
     * Per-slot wait factor (microseconds) for **standard** shops (bucket = 40,
     * leak = 2 calls/s). 500_000 µs ≈ inverse of the 2/s leak rate, so each
     * excess slot waits ~one leak cycle.
     */
    private const STANDARD_PER_SLOT_WAIT_US = 500_000;

    /**
     * Per-slot wait factor (microseconds) for **Plus** shops (bucket = 80,
     * leak = 4 calls/s). 250_000 µs ≈ inverse of the 4/s leak rate. Selected
     * automatically when {@see updateBucketState()} reports a bucket >= 80,
     * which Shopify documents as the Plus signature.
     */
    private const PLUS_PER_SLOT_WAIT_US = 250_000;

    /**
     * Bucket size at or above which we assume a Shopify Plus shop and switch
     * to the faster leak rate. 80 is the documented Plus bucket; treating
     * values above as Plus too keeps us safe if Shopify ever raises the cap.
     */
    private const PLUS_BUCKET_THRESHOLD = 80;

    /**
     * Real Shopify response header carrying the call-limit ratio.
     * Case-insensitive at the PSR-7 layer, but underscore-vs-hyphen matters —
     * this is the exact form documented by Shopify.
     */
    private const HEADER_CALL_LIMIT = 'X-Shopify-Shop-Api-Call-Limit';

    //==============================================================================
    // Configuration
    //==============================================================================

    /**
     * @var float Bucket usage ratio (0..1) above which preemptive sleeps kick in
     */
    private float $softThreshold;

    /**
     * @var int Max number of retries on HTTP 429 before giving up
     */
    private int $maxRetries;

    //==============================================================================
    // State (instance-level — never use static here, multi-tenant unsafe)
    //==============================================================================

    /**
     * @var int Last observed `used` value from the call-limit header
     */
    private int $lastUsed = 0;

    /**
     * @var int Last observed `total` value from the call-limit header (bucket size)
     */
    private int $lastTotal = self::DEFAULT_BUCKET_TOTAL;

    /**
     * @param float $softThreshold ratio in [0..1]; clamped to safe bounds
     * @param int   $maxRetries    >= 0; 0 disables retries (one shot, no replay)
     */
    public function __construct(
        float $softThreshold = self::DEFAULT_SOFT_THRESHOLD,
        int $maxRetries = self::DEFAULT_MAX_RETRIES
    ) {
        //====================================================================//
        // Clamp threshold to a sensible window — below 0.5 is overly cautious
        // and produces large delays for no reason; above 0.95 is so close to
        // the bucket cap that we will trip 429s instead of preventing them.
        $this->softThreshold = max(0.5, min(0.95, $softThreshold));
        $this->maxRetries = max(0, $maxRetries);
    }

    /**
     * {@inheritDoc}
     *
     * Flow:
     *  1. waitIfNeeded()    — preemptive sleep if previous response showed us close to the cap
     *  2. $next($request)   — actual HTTP call (Slince wraps Guzzle to catch RequestException)
     *  3. updateBucket()    — read the new bucket state from the response
     *  4. on 429 + retries left, sleep Retry-After and loop back to step 2
     *  5. otherwise return the response — Slince's raiseException() will throw if status >= 300
     */
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $this->waitIfNeeded();

        $attempts = 0;
        while (true) {
            //====================================================================//
            // Send through the rest of the chain (or to Guzzle if we're last)
            $response = $next($request);

            //====================================================================//
            // Always update bucket state — even a 429 response carries the header,
            // so we keep our preemptive throttling accurate across retries
            $this->updateBucketState($response);

            //====================================================================//
            // Success path: any non-429 response is returned immediately. The
            // Slince Client will then call raiseException() and throw the right
            // typed exception (BadRequest, Unauthorized, ...) if status >= 300.
            if (429 !== $response->getStatusCode()) {
                return $response;
            }

            //====================================================================//
            // 429 with no retries left: return the response as-is. The Client
            // will then throw TooManyRequestsException — same behavior as if
            // we had no throttling middleware at all.
            if ($attempts >= $this->maxRetries) {
                return $response;
            }

            //====================================================================//
            // 429 with retries available: sleep for Retry-After and try again.
            // The same $request is reusable: PSR-7 requests are immutable and
            // the body lives in the captured $options of the $next closure.
            $this->sleepForRetryAfter($response);
            $attempts++;
        }
    }

    //==============================================================================
    // Internal — preemptive throttling
    //==============================================================================

    /**
     * Sleep before the next request if the last observed bucket usage was high.
     *
     * The wait is **proportional** to how many bucket slots we are above the
     * threshold, so:
     *  - At threshold (e.g. 30/40)        → no sleep
     *  - 1 slot over (31/40)              → ~500 ms sleep
     *  - 5 slots over (35/40)             → ~2.5 s sleep
     *
     * That matches Shopify's 2 calls/s leak rate: each 500 ms cycle frees up
     * one bucket slot, so the wait roughly aligns "we wanted X extra slots →
     * we wait X leak cycles".
     */
    private function waitIfNeeded(): void
    {
        if ($this->lastTotal <= 0) {
            return;
        }

        $thresholdSlot = (int) floor($this->softThreshold * $this->lastTotal);
        $excess = $this->lastUsed - $thresholdSlot;
        if ($excess <= 0) {
            return;
        }

        usleep($excess * $this->perSlotWaitUs());
    }

    /**
     * Per-slot wait factor in microseconds, picked from the observed bucket size.
     *
     * Shopify documents two bucket sizes that map 1:1 to leak rates:
     *  - Standard shop: 40 / 2 calls/s → 500 ms per leaked slot
     *  - Plus shop:     80 / 4 calls/s → 250 ms per leaked slot
     *
     * We auto-detect via the call-limit header rather than asking the caller
     * to declare the shop tier — the header is authoritative and present on
     * every response, so the very first request gets the right scaling on
     * its second call.
     */
    private function perSlotWaitUs(): int
    {
        return $this->lastTotal >= self::PLUS_BUCKET_THRESHOLD
            ? self::PLUS_PER_SLOT_WAIT_US
            : self::STANDARD_PER_SLOT_WAIT_US;
    }

    //==============================================================================
    // Internal — 429 retry helpers
    //==============================================================================

    /**
     * Sleep for the duration specified by the response's `Retry-After` header,
     * with a safe fallback when the header is missing or unparseable.
     *
     * Shopify documents `Retry-After` as integer seconds. Per RFC 7231 it can
     * also be an HTTP-date, but in practice Shopify never uses that form, so
     * we just (float)cast and check it's > 0.
     */
    private function sleepForRetryAfter(ResponseInterface $response): void
    {
        $retryAfter = (float) $response->getHeaderLine('Retry-After');
        if ($retryAfter <= 0.0) {
            $retryAfter = self::FALLBACK_RETRY_AFTER_S;
        }

        usleep((int) ($retryAfter * 1_000_000));
    }

    //==============================================================================
    // Internal — bucket state tracking
    //==============================================================================

    /**
     * Read the call-limit header from a response and update our tracking state.
     *
     * The header format is `<used>/<total>` (e.g. `"32/40"`). If the header is
     * missing or the format is unexpected, we leave the previous state intact
     * — better to keep the last known value than to silently reset to defaults
     * and lose our throttling smarts on the next call.
     */
    private function updateBucketState(ResponseInterface $response): void
    {
        if (!$response->hasHeader(self::HEADER_CALL_LIMIT)) {
            return;
        }

        $line = $response->getHeaderLine(self::HEADER_CALL_LIMIT);
        if (!preg_match('#^(\d+)/(\d+)$#', $line, $match)) {
            return;
        }

        $this->lastUsed = (int) $match[1];
        $this->lastTotal = (int) $match[2];
    }
}
