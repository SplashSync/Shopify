<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Shopify Account Edit Form
 */
class EditFormType extends AbstractShopifyType
{
    /**
     * Build Shopify Edit Form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addWsHost($builder);
        $this->addWarehouseField($builder, $options);
        $this->addTokenField($builder, $options);
    }
}
