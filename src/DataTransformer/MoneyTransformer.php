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

namespace Splash\Connectors\Shopify\DataTransformer;

use Splash\Models\Helpers\PricesHelper;

class MoneyTransformer extends AbstractJsonDataTransformer
{
    const AMOUNT = "amount";

    const CURRENCY = "currency_code";

    /**
     * @inheritDoc
     */
    public function transform($value): ?array
    {
        if (!$arrayValue = $this->transformJson($value)) {
            return null;
        }

        return PricesHelper::encode(
            (float) ($arrayValue[self::AMOUNT] ?? 0.0),
            0.0,
            null,
            $arrayValue[self::CURRENCY] ?? "EUR"
        );
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value): ?string
    {
        if (!is_array($value) || !PricesHelper::isValid($value)) {
            return null;
        }

        return $this->reverseTransformJson(array(
            self::AMOUNT => (string) PricesHelper::taxExcluded($value),
            self::CURRENCY => (string) ($value["code"] ?? "EUR")
        ));
    }
}
