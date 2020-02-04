<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Widgets;

use Splash\Bundle\Models\AbstractStandaloneWidget;
use Splash\Connectors\Shopify\Services\ShopifyConnector;
use Splash\Core\SplashCore      as Splash;

/**
 * Shopify Config SelfTest
 */
class SelfTest extends AbstractStandaloneWidget
{
    //====================================================================//
    // Define Standard Options for this Widget
    // Override this array to change default options for your widget
    public static $OPTIONS = array(
        "Width" => self::SIZE_DEFAULT,
        'UseCache' => true,
        'CacheLifeTime' => 1,
    );

    /**
     * {@inheritdoc}
     */
    protected static $NAME = "Server SelfTest";

    /**
     * {@inheritdoc}
     */
    protected static $DESCRIPTION = "Results of your Server SelfTests";

    /**
     * {@inheritdoc}
     */
    protected static $ICO = "fa fa-info-circle";

    /**
     * @var ShopifyConnector
     */
    protected $connector;

    /**
     * Class Constructor
     *
     * @param ShopifyConnector $connector
     */
    public function __construct(ShopifyConnector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Return requested Customer Data
     *
     * @param array $params Widget Inputs Parameters
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($params = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        //====================================================================//
        // Setup Widget Core Informations
        //====================================================================//

        $this->setTitle($this->getName());
        $this->setIcon($this->getIcon());

        //====================================================================//
        // Build Intro Text Block
        //====================================================================//
        $this->buildIntroBlock();

        //====================================================================//
        // Build SlefTest Results Block
        //====================================================================//
        $this->connector->selfTest();
        $this->buildNotificationsBlock();

        //====================================================================//
        // Set Blocks to Widget
        $blocks = $this->blocksFactory()->render();
        if ($blocks) {
            $this->setBlocks($blocks);
        }

        //====================================================================//
        // Publish Widget
        return $this->render();
    }

    /**
     * Block Building - Text Intro
     *
     * @return void
     */
    private function buildIntroBlock(): void
    {
        //====================================================================//
        // Into Text Block
        $this->blocksFactory()->addTextBlock("This widget summarize SelfTest of your Shopify Account Config");
    }

    /**
     * Block Building - Notifications Parameters
     *
     * @return void
     */
    private function buildNotificationsBlock(): void
    {
        //====================================================================//
        // Get Log
        $log = Splash::log();
        //====================================================================//
        // If test was passed
        if (empty($log->err)) {
            $this->blocksFactory()->addNotificationsBlock(array("success" => "Self-Test Passed!"));
        }
        //====================================================================//
        // Add Error Notifications
        foreach ($log->err as $text) {
            $this->blocksFactory()->addNotificationsBlock(array("error" => $text));
        }
        //====================================================================//
        // Add Warning Notifications
        foreach ($log->war as $text) {
            $this->blocksFactory()->addNotificationsBlock(array("warning" => $text));
        }
        //====================================================================//
        // Add Success Notifications
        foreach ($log->msg as $text) {
            $this->blocksFactory()->addNotificationsBlock(array("success" => $text));
        }
        //====================================================================//
        // Add Debug Notifications
        foreach ($log->deb as $text) {
            $this->blocksFactory()->addNotificationsBlock(array("info" => $text));
        }
    }
}
