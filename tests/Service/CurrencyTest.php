<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Service;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @covers       \App\CommissionTask\Service\Currency::__construct
     * @dataProvider dataProviderForConstructorSuccessTesting
     *
     * @param string $currency
     * @param Currency $expected
     */
    public function testConstructSuccess(string $currency, Currency $expected)
    {
        static::assertEquals($expected, new Currency($currency));
    }

    /**
     * @covers \App\CommissionTask\Service\Currency::__construct
     */
    public function testConstructThrowsException()
    {
        $unsupportedCurrency = 'new';

        $this->expectException(UnsupportedCurrencyException::class);
        $this->expectExceptionMessage("Unsupported currency was provided $unsupportedCurrency");

        new Currency($unsupportedCurrency);
    }

    /**
     * @covers       \App\CommissionTask\Service\Currency::convert
     * @dataProvider dataProviderForConvertTesting
     *
     * @param float $amount
     * @param string $from
     * @param string $to
     * @param BigDecimal $expected
     */
    public function testConvert(float $amount, string $from, string $to, BigDecimal $expected)
    {
        static::assertEquals($expected->toFloat(), Currency::convert($amount, $from, $to)->toFloat());
    }

    public function dataProviderForConstructorSuccessTesting(): array
    {
        $allowedCurrencies = AppConfig::getInstance()->get('currencies.supported');

        return array_map(static function (string $currency) {
            return [$currency, new Currency($currency)];
        }, $allowedCurrencies);
    }

    public function dataProviderForConvertTesting(): array
    {
        $config = AppConfig::getInstance();
        $rates = $config->get('currencies.exchange_rates');
        $scale = $config->get('scale');

        return [
            '15 EUR in USD' => [
                15,
                Currency::EUR,
                Currency::USD,
                BigDecimal::of(15)
                    ->multipliedBy(BigDecimal::of($rates[Currency::USD]))
            ],
            '15 USD in EUR' => [
                15,
                Currency::USD,
                Currency::EUR,
                BigDecimal::of(15)
                    ->dividedBy(
                        BigDecimal::of($rates[Currency::USD]),
                        $scale,
                        RoundingMode::UP
                    )
            ],
            '15 USD in USD' => [
                15,
                Currency::USD,
                Currency::USD,
                BigDecimal::of(15)
            ],
            '15 EUR in JPY' => [
                15,
                Currency::EUR,
                Currency::JPY,
                BigDecimal::of(15)
                    ->multipliedBy(BigDecimal::of($rates[Currency::JPY]))
            ],
            '15 JPY in EUR' => [
                15,
                Currency::JPY,
                Currency::EUR,
                BigDecimal::of(15)
                    ->dividedBy(
                        BigDecimal::of($rates[Currency::JPY]),
                        $scale,
                        RoundingMode::UP
                    )
            ]
        ];
    }
}
