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

/**
 * Shopify Bridge Connector Bundles
 */
return array(
    //==============================================================================
    // SPLASH BUNDLES
    Splash\Metadata\SplashMetadataBundle::class => array("all" => true),
    //==============================================================================
    // SHOPIFY OAUTH2 CLIENT BUNDLE
    KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle::class => array("all" => true),
);
