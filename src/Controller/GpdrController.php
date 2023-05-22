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

namespace Splash\Connectors\Shopify\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Splash\Bundle\Models\Local\ActionsTrait;
use Splash\Connectors\Shopify\OAuth2\ShopifyAdapter;
use Splash\Connectors\Shopify\Objects;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Manage GPDR Actions for Shopify Connector
 */
class GpdrController extends AbstractController
{
    use ActionsTrait;

    /**
     * @var string
     */
    private string $topic;

    /**
     * OAuth2 Clients Registry
     *
     * @var ClientRegistry
     */
    private ClientRegistry $clientRegistry;

    /**
     * @var Swift_Mailer
     */
    private Swift_Mailer $mailer;

    /**
     * @param Swift_Mailer $mailer
     */
    public function __construct(ClientRegistry $clientRegistry, Swift_Mailer $mailer)
    {
        $this->clientRegistry = $clientRegistry;
        $this->mailer = $mailer;
    }

    /**
     * Catch Mandatory GPDR User Requests
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request): JsonResponse
    {
        //==============================================================================
        // Safety Check
        if (!$this->verify($request)) {
            throw new UnauthorizedHttpException('Malformed or missing data');
        }
        //==============================================================================
        // Extract Data
        $data = empty($request->request->all())
            ? json_decode($request->getContent(), true, 512, \JSON_BIGINT_AS_STRING)
            : $request->request->all()
        ;
        if (empty($data) || !is_array($data)) {
            throw new BadRequestHttpException('Malformed or missing data');
        }
        //==============================================================================
        // Push an email to site Admins
        try {
            $this->sendEmail($this->topic, $data);
        } catch (\Throwable $ex) {
            $this->topic = $ex->getMessage();
        }
        //==============================================================================
        // Return OK Response
        return new JsonResponse(array(
            'success' => true,
            'topic' => $this->topic,
            'message' => "Your request has been recorded and will be send to webmaster"
        ));
    }

    /**
     * Verify Request Headers
     *
     * @param Request $request
     *
     * @return bool
     */
    private function verify(Request $request) : bool
    {
        //====================================================================//
        // Verify Request is POST
        if (!$request->isMethod('POST')) {
            return false;
        }
        //====================================================================//
        // Verify User Node Domain is Ok with Identifier
        if (empty($request->headers->get("X-Shopify-Shop-Domain"))) {
            return false;
        }
        //====================================================================//
        // Verify Request HMAC
        $adapter = $this->clientRegistry->getClient('shopify')->getOAuth2Provider();
        //==============================================================================
        // Safety Check
        if (!($adapter instanceof ShopifyAdapter) || !$adapter->validateWebhookHmac($request)) {
            return false;
        }
        //====================================================================//
        // Verify WebHook Type is Provided & is Valid
        $topic = $request->headers->get("X-Shopify-Topic");
        if (empty($topic) || !is_string($topic) || (!in_array($topic, Objects\WebHook::getGpdrTopics(), true))) {
            return false;
        }
        $this->topic = $topic;

        return true;
    }

    /**
     * Send GPDR User Requests by Email
     *
     * @param string $reason
     * @param array  $data
     *
     * @return void
     */
    private function sendEmail(string $reason, array $data): void
    {
        //==============================================================================
        // Push an email to site Admins
        $message = (new Swift_Message('Hello Email'))
            ->setFrom('shopify@splashsync.com')
            ->setTo('contact@splashsync.com')
            ->setSubject("[SHOPIFY] GPDR Request - ".$reason)
            ->setBody(json_encode($data), 'text/plain')
        ;

        $this->mailer->send($message);
    }
}
