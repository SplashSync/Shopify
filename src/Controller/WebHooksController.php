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

use Psr\Log\LoggerInterface;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Connectors\Shopify\Objects;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Splash Shopify Connector WebHooks Controller
 */
class WebHooksController extends AbstractController
{
    /**
     * @var AbstractConnector
     */
    private AbstractConnector $connector;

    /**
     * @var string
     */
    private string $topic;

    /**
     * @var array
     */
    private array $data;

    //====================================================================//
    //  SHOPIFY WEBHOOKS MANAGEMENT
    //====================================================================//

    /**
     * Execute WebHook Actions for A MailJet Node
     *
     * @param LoggerInterface   $logger
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @throws BadRequestHttpException
     *
     * @return JsonResponse
     */
    public function indexAction(LoggerInterface $logger, Request $request, AbstractConnector $connector): JsonResponse
    {
        //====================================================================//
        // For Shopify ping GET
        if ($request->isMethod('GET')) {
            $logger->notice(__CLASS__.'::'.__FUNCTION__.' Shopify Ping.', $request->attributes->all());

            return $this->prepareResponse(200);
        }

        //==============================================================================
        // Safety Check
        if (!$this->verify($request, $connector)) {
            throw new BadRequestHttpException('Malformed or missing data');
        }

        //====================================================================//
        // Read Request Parameters
        if (!$this->extractData($request)) {
            throw new BadRequestHttpException('Malformed or missing data');
        }

        //====================================================================//
        // Log Shopify Request
        $logger->warning(
            __CLASS__.'::'.__FUNCTION__.' Shopify WebHook Received ',
            (isset($this->data) ? $this->data : array())
        );

        //==============================================================================
        // Commit Changes
        $this->executeCommits();

        return $this->prepareResponse(200);
    }

    /**
     * Execute Changes Commits
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function executeCommits() : void
    {
        switch ($this->topic) {
            //====================================================================//
            // Customer & Address WebHooks
            //====================================================================//
            case 'customers/create':
                $this->executeCustomerCommit($this->data, SPL_A_CREATE, "Created");

                return;
            case 'customers/update':
            case 'customers/disable':
            case 'customers/enable':
                $this->executeCustomerCommit($this->data, SPL_A_UPDATE, "Updated");

                return;
            case 'customers/delete':
                $this->executeCustomerCommit($this->data, SPL_A_DELETE, "Deleted");

                return;
            //====================================================================//
            // Products WebHooks
            //====================================================================//
            case 'products/create':
                $this->executeProductCommit($this->data, SPL_A_CREATE, "Created");

                return;
            case 'products/update':
                $this->executeProductCommit($this->data, SPL_A_UPDATE, "Updated");

                return;
            case 'products/delete':
                $this->executeProductCommit($this->data, SPL_A_DELETE, "Deleted");

                return;
            //====================================================================//
            // Order & Invoices WebHooks
            //====================================================================//
            case 'orders/create':
                $this->executeOrderCommit($this->data, SPL_A_CREATE, "Created");

                return;
            case 'orders/cancelled':
            case 'orders/fulfilled':
            case 'orders/paid':
            case 'orders/partially_fulfilled':
            case 'orders/updated':
                $this->executeOrderCommit($this->data, SPL_A_UPDATE, "Updated");

                return;
            case 'orders/delete':
                $this->executeOrderCommit($this->data, SPL_A_DELETE, "Deleted");

                return;
            default:
                //====================================================================//
                // RGPD WebHooks
                //====================================================================//
                return;
        }
    }

    /**
     * Execute Changes Commits for Customers
     *
     * @param array  $data
     * @param string $action
     * @param string $comment
     */
    private function executeCustomerCommit(array $data, string $action, string $comment) : void
    {
        //==============================================================================
        // Commit Change For ThirdParty
        $this->connector->commit(
            'ThirdParty',
            (string) $data['id'],
            $action,
            'Shopify API',
            'Shopify Customer '.$comment
        );
        //==============================================================================
        // Safety Check
        if (empty($data['addresses'])) {
            return;
        }
        //==============================================================================
        // Commit Change For ThirdParty Addresses
        foreach ($data['addresses'] as $address) {
            $this->connector->commit(
                'Address',
                Objects\Address::getObjectId((string) $data['id'], (string) $address['id']),
                $action,
                'Shopify API',
                'Shopify Customer Address '.$comment
            );
        }
    }

    /**
     * Execute Changes Commits fro Products
     *
     * @param array  $data
     * @param string $action
     * @param string $comment
     */
    private function executeProductCommit(array $data, string $action, string $comment) : void
    {
        //==============================================================================
        // Safety Check
        if (empty($data['variants'])) {
            return;
        }
        //==============================================================================
        // Commit Change For Product Variants
        foreach ($data['variants'] as $variant) {
            $this->connector->commit(
                'Product',
                Objects\Product::getObjectId((string) $data['id'], (string) $variant['id']),
                $action,
                'Shopify API',
                'Shopify Product '.$comment
            );
        }
    }

    /**
     * Execute Changes Commits Orders & Invoices
     *
     * @param array  $data
     * @param string $action
     * @param string $comment
     */
    private function executeOrderCommit(array $data, string $action, string $comment) : void
    {
        $this->connector->commit(
            'Order',
            (string) $data['id'],
            $action,
            'Shopify API',
            'Shopify Order was '.$comment
        );
        $this->connector->commit(
            'Invoice',
            (string) $data['id'],
            $action,
            'Shopify API',
            'Shopify Invoice was '.$comment
        );
    }

    /**
     * Verify Request Headers
     *
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return bool
     */
    private function verify(Request $request, AbstractConnector $connector) : bool
    {
        //====================================================================//
        // Verify Request is POST
        if (!$request->isMethod('POST')) {
            return false;
        }

        //====================================================================//
        // Verify User Node Domain is Ok with Identifier
        $headerHost = $request->headers->get("X-Shopify-Shop-Domain");
        /** @var ShopifyConnector $connector */
        if (empty($headerHost) || ($connector->getShopifyDomain() != $headerHost)) {
            return false;
        }

        //====================================================================//
        // Verify WebHook Type is Provided & is Valid
        $topic = $request->headers->get("X-Shopify-Topic");
        if (empty($topic) || !is_string($topic) || (!in_array($topic, Objects\WebHook::getTopics(), true))) {
            return false;
        }
        $this->topic = $topic;

        //====================================================================//
        // Store Connector for Further Usages
        $this->connector = $connector;

        return true;
    }

    /**
     * Extract Data from Request
     *
     * @param Request $request
     *
     * @return bool
     */
    private function extractData(Request $request): bool
    {
        //====================================================================//
        // Detect GPDR Topics
        if (in_array($this->topic, Objects\WebHook::getGpdrTopics(), true)) {
            return true;
        }

        $data = empty($request->request->all())
            ? json_decode($request->getContent(), true, 512, \JSON_BIGINT_AS_STRING)
            : $request->request->all()
        ;

        if (!is_array($data) || empty($data["id"]) || !is_scalar($data["id"])) {
            return false;
        }

        $this->data = $data;

        return true;
    }

    /**
     * @param int $status
     *
     * @return JsonResponse
     */
    private function prepareResponse($status)
    {
        return new JsonResponse(array('success' => true), $status);
    }
}
