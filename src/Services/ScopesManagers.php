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

namespace Splash\Connectors\Shopify\Services;

use Splash\Connectors\Shopify\Models\ShopifyHelper as API;

class ScopesManagers
{
    /**
     * Default Scopes for Shopify Connector
     */
    const DEFAULT_SCOPES = array(
        // Access to Customer and Saved Search.
        'read_customers', 'write_customers',
        // Access to Product, product variant, Product Image, Collect, Custom Collection, and Smart Collection.
        'read_products', 'write_products',
        // Access to Product Stocks Levels
        'read_inventory', 'write_inventory',
        // Access to Order, Transaction and Fulfillment.
        'read_orders', 'write_orders',
        // Access to Fulfillment
        'read_fulfillments', 'write_fulfillments',
    );

    /**
     * Scopes for Logistics Mode
     */
    const LOGISTIC_SCOPES = array(
        // Access to Stock Locations
        'read_locations',
        // Access to Fulfillment
        'read_assigned_fulfillment_orders', 'write_assigned_fulfillment_orders',
    );

    /**
     * Scopes for Full Order History Mode
     *
     * Restricted Shopify scope: 'read_orders' only exposes the last 60 days,
     * 'read_all_orders' unlocks older orders but requires Shopify approval.
     */
    const ALL_ORDERS_SCOPES = array(
        // Access to Orders older than 60 days
        'read_all_orders',
    );

    /**
     * @param bool $allOrdersApproved Deployment is approved by Shopify for the read_all_orders scope
     */
    public function __construct(
        private readonly bool $allOrdersApproved = false,
    ) {
    }

    /**
     * Get the Full List of Scopes Required by this Connector.
     *
     * @param ShopifyConnector $connector
     *
     * @return string[]
     */
    public function getRequiredScopes(ShopifyConnector $connector): array
    {
        //====================================================================//
        // Default Scopes
        $scopes = self::DEFAULT_SCOPES;
        //====================================================================//
        // Logistic Mode Scopes
        if ($connector->hasLogisticMode()) {
            $scopes = array_merge($scopes, self::LOGISTIC_SCOPES);
        }
        //====================================================================//
        // Full Order History Scope (restricted, gated by deployment context)
        if ($this->hasAllOrders($connector)) {
            $scopes = array_merge($scopes, self::ALL_ORDERS_SCOPES);
        }

        return $scopes;
    }

    /**
     * Get Shopify Access Scope from APi
     *
     * @return bool
     */
    public function fetchAccessScopes(ShopifyConnector $connector): bool
    {
        //====================================================================//
        // Get Lists of Available Scopes from Api
        $response = API::getRaw('oauth/access_scopes', array(), 'access_scopes');
        if (!is_array($response)) {
            return false;
        }
        //====================================================================//
        // Reformat results
        $response = array_map(function ($scopeItem) {
            return array_shift($scopeItem);
        }, $response);
        //====================================================================//
        // Store in Connector Settings
        $connector->setParameter("Scopes", $response);

        return true;
    }

    /**
     * Get Shopify Access Scope from Parameters
     *
     * @return string[]
     */
    public function getAccessScopes(ShopifyConnector $connector): array
    {
        //====================================================================//
        // Get from Connector Settings
        $scopes = $connector->getParameter("Scopes", null);
        //====================================================================//
        // From API if Empty
        if (null === $scopes) {
            if (!$this->fetchAccessScopes($connector)) {
                return array();
            }
            $scopes = $connector->getParameter("Scopes", null);
        }

        return is_array($scopes) ? $scopes : array();
    }

    /**
     * Get List of Missing Access Scopes.
     *
     * @return string[]
     */
    public function getMissingScopes(ShopifyConnector $connector) : array
    {
        return array_diff(
            $this->getRequiredScopes($connector),
            $this->getAccessScopes($connector)
        );
    }

    /**
     * Check if the read_all_orders Scope (orders older than 60 days) is Allowed.
     *
     * Restricted Shopify scope: only requested when BOTH conditions hold:
     *  - the deployment is approved by Shopify (validated cloud, ENV flag), AND
     *  - the connector is NOT a Private/Custom App. Shopify does not allow a
     *    self-hosted custom App to be created with this scope, so we never
     *    request it there (it would break the Oauth2 authorization).
     *
     * @param ShopifyConnector $connector
     *
     * @return bool
     */
    private function hasAllOrders(ShopifyConnector $connector): bool
    {
        return $this->allOrdersApproved && !$connector->hasPrivateAppCredentials();
    }
}
