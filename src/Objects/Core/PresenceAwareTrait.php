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

namespace Splash\Connectors\Shopify\Objects\Core;

/**
 * Splash Presence Flag, for Objects that Splash may Track
 *
 * A virtual field: Shopify knows nothing about it, it only tells Splash that
 * the object is there. Written by Splash, never sent to the Api, never read
 * back from it.
 */
trait PresenceAwareTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildPresenceAwareFields(): void
    {
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("presence_flag")
            ->name("Presence Flag")
            ->group("Meta")
            ->microData("http://splashync.com/schemas", "Presence")
            ->setPreferWrite()
            ->isWriteOnly()
        ;
    }

    /**
     * Write Given Fields
     */
    protected function setPresenceAwareFields(string $fieldName, bool $fieldData): void
    {
        if ("presence_flag" != $fieldName) {
            return;
        }
        $this->setSimple($fieldName, $fieldData);

        unset($this->in[$fieldName]);
    }
}
