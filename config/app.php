<?php

declare(strict_types=1);

use Aimeos\Map;
use App\CommissionTask\Model\Person;
use App\CommissionTask\Service\Currency;

Map::delimiter('.');

return [
    'currencies' => [
        'main' => Currency::EUR,
        'supported' => [
            Currency::EUR,
            Currency::USD,
            Currency::JPY,
        ]
    ],
    'persons' => [
        'types' => [
            Person::TYPE_LEGAL,
            Person::TYPE_NATURAL,
        ]
    ]
];