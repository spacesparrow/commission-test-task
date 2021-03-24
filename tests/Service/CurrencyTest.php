<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Service;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Service\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @covers \App\CommissionTask\Service\Currency::__construct
     * @dataProvider dataProviderForConstructorSuccess
     *
     * @param string $currency
     * @param Currency $expected
     */
    public function testConstructSuccess(string $currency, Currency $expected): void
    {
        static::assertEquals($expected, new Currency($currency));
    }

    /**
     * @covers \App\CommissionTask\Service\Currency::__construct
     */
    public function testConstructThrowsException(): void
    {
        $unsupportedCurrency = 'new';

        $this->expectException(UnsupportedCurrencyException::class);
        $this->expectExceptionMessage("Unsupported currency was provided $unsupportedCurrency");

        new Currency($unsupportedCurrency);
    }

    public function dataProviderForConstructorSuccess(): array
    {
        $allowedCurrencies = AppConfig::getInstance()->get('currencies.supported');

        return array_map(static function (string $currency) {
            return [$currency, new Currency($currency)];
        }, $allowedCurrencies);
    }
}