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

namespace Splash\Connectors\Shopify\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
    public function addWarehouseField(FormBuilderInterface $builder, array $options): self
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
                'help' => "var.location.desc",
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
    public function addTokenField(FormBuilderInterface $builder, array $options): self
    {
        $builder
            //==============================================================================
            // Shopify Api Key Option Authentification
            ->add('Token', TextType::class, array(
                'label' => "var.token.label",
                'help' => "var.token.desc",
                'required' => false,
                'translation_domain' => "ShopifyBundle",
            ))
        ;

        return $this;
    }

    /**
     * Add Logistic Form Fields to FormBuilder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return self
     */
    public function addLogisticFields(FormBuilderInterface $builder, array $options): self
    {
        //==============================================================================
        // Check Shopify Locations Lists is Available
        if (!isset($options["data"]["LogisticMode"]) || empty($options["data"]["LogisticMode"])) {
            return $this;
        }

        $builder
            //==============================================================================
            // Shopify List Option Selector
            ->add('LogisticNotify', CheckboxType::class, array(
                'label' => "var.notify.label",
                'help' => "var.notify.desc",
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
    protected function addWsHost(FormBuilderInterface $builder): self
    {
        $builder
            ->add('WsHost', TextType::class, array(
                'label' => 'var.url.label',
                'help' => 'var.url.desc',
                'translation_domain' => 'ShopifyBundle',
            ))
        ;

        return $this;
    }

    /**
     * Add Private API Key.
     *
     * @param FormBuilderInterface $builder
     *
     * @return self
     */
    protected function addPrivateApiEnable(FormBuilderInterface $builder): self
    {
        $builder
            ->add('apiPrivate', CheckboxType::class, array(
                'label' => 'var.api-private.label',
                'help' => 'var.api-private.desc',
                'translation_domain' => 'ShopifyBundle',
                'required' => true,
            ))
        ;

        return $this;
    }

    /**
     * Add Private API Key.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return self
     */
    protected function addPrivateApiKey(FormBuilderInterface $builder, array $options): self
    {
        //==============================================================================
        // Check Shopify Private API is Enabled
        if (empty($options["data"]["apiPrivate"])) {
            return $this;
        }

        $builder
            ->add('apiKey', TextType::class, array(
                'label' => 'var.api-key.label',
                'help' => 'var.api-key.desc',
                'translation_domain' => 'ShopifyBundle',
                'required' => true,
            ))
        ;

        return $this;
    }

    /**
     * Add Private API Secret.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return self
     */
    protected function addPrivateApiSecret(FormBuilderInterface $builder, array $options): self
    {
        //==============================================================================
        // Check Shopify Private API is Enabled
        if (empty($options["data"]["apiPrivate"])) {
            return $this;
        }

        $builder
            ->add('apiSecret', TextType::class, array(
                'label' => 'var.api-secret.label',
                'help' => 'var.api-secret.desc',
                'translation_domain' => 'ShopifyBundle',
                'required' => true,
            ))
        ;

        return $this;
    }
}
