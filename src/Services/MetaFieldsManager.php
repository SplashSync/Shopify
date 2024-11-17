<?php

namespace Splash\Connectors\Shopify\Services;

use Splash\Connectors\Shopify\Models\ShopifyHelper as API;
use Splash\Core\SplashCore as Splash;

/**
 * Manage Access to MetaFields
 */
class MetaFieldsManager
{

    /**
     * {@inheritdoc}
     */
    public function getList(string $ownerType) : bool
    {

        dump(API::post("graphql", array(
            "query" => "query { 
                metafieldDefinitions(first: 1, ownerType: PRODUCT) { 
                    nodes {
                        id
                        key
                        namespace
                        description
                        type { name, valueType }
                        validations { type }
                    }
                    pageInfo {
                      endCursor
                      hasNextPage
                      hasPreviousPage
                      startCursor
                    }
                }
            }"
        ), "data"));

        dump(API::post("graphql", array(
            "query" => 'query { 
                metafieldDefinitions(first: 250, after: "eyJsYXN0X2lkIjoyNzQ2Nzc0MzI4NSwibGFzdF92YWx1ZSI6IjI3NDY3NzQzMjg1In0=", ownerType: PRODUCT) { 
                    nodes {
                        id
                        key
                        namespace
                        description
                        type { name, valueType }
                        validations { type }
                    }
                    pageInfo {
                      endCursor
                      hasNextPage
                      hasPreviousPage
                      startCursor
                    }
                }
            }'
        ), "data"));

        dd(Splash::log()->getRawLog());

        //====================================================================//
        // Get Shop Informations
        if (!$this->fetchShopInformations()) {
            return false;
        }
        //====================================================================//
        // Get Scopes Informations
        if (!$this->fetchAccessScopes()) {
            return false;
        }
        //====================================================================//
        // Get List of Available Countries
        if (!$this->fetchCountriesLists()) {
            return false;
        }
        //====================================================================//
        // Get List of Available Stock Locations
        if (!$this->fetchLocationsLists()) {
            return false;
        }

        //====================================================================//
        // Update Connector Settings
        $this->updateConfiguration();

        return true;
    }
}