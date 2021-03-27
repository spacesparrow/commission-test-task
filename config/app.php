<?php

declare(strict_types=1);

use Aimeos\Map;
use App\CommissionTask\Model\Operation;
use App\CommissionTask\Model\Person;
use App\CommissionTask\Service\Currency;

Map::delimiter('.');

return [
    'scale' => 5,
    'currencies' => [
        'main' => Currency::EUR,
        'supported' => [
            Currency::EUR,
            Currency::USD,
            Currency::JPY,
        ],
        'exchange_rates' => [
            Currency::USD => 1.1497,
            Currency::JPY => 129.53,
        ],
    ],
    'persons' => [
        'types' => [
            Person::TYPE_LEGAL,
            Person::TYPE_NATURAL,
        ],
    ],
    'operations' => [
        'types' => [
            Operation::TYPE_CASH_IN,
            Operation::TYPE_CASH_OUT,
        ],
    ],
];