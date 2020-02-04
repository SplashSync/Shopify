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

namespace Splash\Connectors\Shopify\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base Form Type for Shopify Connectors Servers
 */
abstract class AbstractShopifyType extends AbstractType
{
    /**
     * Add Warehouse Selector Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return self
     */
    public function addWarehouseField(FormBuilderInterface $builder, array $options)
    {
        //==============================================================================
        // Check Shopify Locations Lists is Available
        if (!isset($options["data"]["LocationsMap"]) || empty($options["data"]["LocationsMap"])) {
            return $this;
        }

        $builder
            //==============================================================================
            // Shopify List Option Selector
            ->add('LocationId', ChoiceType::class, array(
                'label' => "var.location.label",
                // 'help_block' => "var.location.desc",
                'required' => true,
                'translation_domain' => "ShopifyBundle",
                'choice_translation_domain' => false,
                'choices' => array_flip($options["data"]["LocationsMap"]),
            ))
        ;

        return $this;
    }

    /**
     * Add Api Key Field to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addTokenField(FormBuilderInterface $builder, array $options)
    {
        $builder
            //==============================================================================
            // Shopify Api Key Option Authentification
            ->add('Token', TextType::class, array(
                'label' => "var.token.label",
                // 'help_block' => "var.token.desc",
                'required' => false,
                'translation_domain' => "ShopifyBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add Remote Host Url Field.
     *
     * @param FormBuilderInterface $builder
     *
     * @return self
     */
    protected function addWsHost(FormBuilderInterface $builder)
    {
        $builder
            ->add('WsHost', TextType::class, array(
                'label' => 'var.url.label',
                // 'help_block' => 'var.url.desc',
                'translation_domain' => 'ShopifyBundle',
            ))
        ;

        return $this;
    }
}
