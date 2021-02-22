<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Shopify\Objects\Core;

use DateTime;
use DateTimeInterface;

/**
 * Objects Metadata Fields
 */
trait DatesTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildDatesFields(): void
    {
        //====================================================================//
        // TRACEABILITY INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->Identifier("created_at")
            ->Name("Date Created")
            ->Group("Meta")
            ->MicroData("http://schema.org/DataFeedItem", "dateCreated")
            ->isListed()
            ->isReadOnly();

        //====================================================================//
        // Last Change Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->Identifier("updated_at")
            ->Name("Last modification")
            ->Group("Meta")
            ->MicroData("http://schema.org/DataFeedItem", "dateModified")
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getDatesFields($key, $fieldName): void
    {
        //====================================================================//
        // Does the Field Exists?
        if (!in_array($fieldName, array('created_at', 'updated_at'), true)) {
            return;
        }
        //====================================================================//
        // Insert in Response
        $date = new DateTime($this->object[$fieldName]);
        $this->out[$fieldName] = $date->format(SPL_T_DATETIMECAST);
        //====================================================================//
        // Clear Key Flag
        unset($this->in[$key]);
    }

    /**
     * Convert Date to List DateTime String
     *
     * @param DateTimeInterface|string $input
     *
     * @return string
     */
    protected static function toDateTimeString($input): string
    {
        if ($input instanceof DateTimeInterface) {
            return $input->format(SPL_T_DATETIMECAST);
        }

        return (new DateTime($input))->format(SPL_T_DATETIMECAST);
    }
}
