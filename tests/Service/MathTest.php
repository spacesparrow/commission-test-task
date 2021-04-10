<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Service;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;
use PHPUnit\Framework\TestCase;
use \App\CommissionTask\Service\Math;

class MathTest extends TestCase
{
    /**
     * @var Math
     */
    private $math;

    public function setUp()
    {
        $this->math = new Math(AppConfig::getInstance()->get('rounding_scale'));
    }

    /**
     * @covers       \App\CommissionTask\Service\Math::round
     * @dataProvider dataProviderForRoundTesting
     *
     * @param BigDecimal $amount
     * @param string $currency
     * @param string $expectedRoundedAmount
     */
    public function testRound(BigDecimal $amount, string $currency, string $expectedRoundedAmount)
    {
        static::assertSame($expectedRoundedAmount, $this->math->round($amount, $currency));
    }

    /**
     * @return array[]
     */
    public function dataProviderForRoundTesting(): array
    {
        return [
            '0 digits after dot in EUR' => [
                BigDecimal::of(5),
                Currency::EUR,
                '5.00'
            ],
            '1 digit after dot in EUR' => [
                BigDecimal::of(5.1),
                Currency::EUR,
                '5.10'
            ],
            '2 digits after dot in EUR' => [
                BigDecimal::of(5.12),
                Currency::EUR,
                '5.12'
            ],
            '3 digits after dot in EUR' => [
                BigDecimal::of(0.023),
                Currency::EUR,
                '0.03'
            ],
            '4 digits after dot in EUR' => [
                BigDecimal::of(5.1234),
                Currency::EUR,
                '5.13'
            ],
            '5 digits after dot in EUR' => [
                BigDecimal::of(5.54321),
                Currency::EUR,
                '5.55'
            ],
            '6 digits after dot in EUR' => [
                BigDecimal::of(55.043210),
                Currency::EUR,
                '55.05'
            ],
            '7 digits after dot in EUR' => [
                BigDecimal::of(123.5506432),
                Currency::EUR,
                '123.56'
            ],
            '8 digits after dot in EUR' => [
                BigDecimal::of(5432.61059875),
                Currency::EUR,
                '5432.62'
            ],
            '9 digits after dot in EUR' => [
                BigDecimal::of(5432.610598751),
                Currency::EUR,
                '5432.62'
            ],
            '10 digits after dot in EUR' => [
                BigDecimal::of(5432.6705987512),
                Currency::EUR,
                '5432.68'
            ],
            '0 digits after dot in USD' => [
                BigDecimal::of(5),
                Currency::USD,
                '5.00'
            ],
            '1 digit after dot in USD' => [
                BigDecimal::of(5.1),
                Currency::USD,
                '5.10'
            ],
            '2 digits after dot in USD' => [
                BigDecimal::of(5.12),
                Currency::USD,
                '5.12'
            ],
            '3 digits after dot in USD' => [
                BigDecimal::of(0.023),
                Currency::USD,
                '0.03'
            ],
            '4 digits after dot in USD' => [
                BigDecimal::of(5.1234),
                Currency::USD,
                '5.13'
            ],
            '5 digits after dot in USD' => [
                BigDecimal::of(5.54321),
                Currency::USD,
                '5.55'
            ],
            '6 digits after dot in USD' => [
                BigDecimal::of(55.043210),
                Currency::USD,
                '55.05'
            ],
            '7 digits after dot in USD' => [
                BigDecimal::of(123.5506432),
                Currency::USD,
                '123.56'
            ],
            '8 digits after dot in USD' => [
                BigDecimal::of(5432.61059875),
                Currency::USD,
                '5432.62'
            ],
            '9 digits after dot in USD' => [
                BigDecimal::of(5432.610598751),
                Currency::USD,
                '5432.62'
            ],
            '10 digits after dot in USD' => [
                BigDecimal::of(5432.6705987512),
                Currency::USD,
                '5432.68'
            ],
            '0 digits after dot in JPY' => [
                BigDecimal::of(5),
                Currency::JPY,
                '5'
            ],
            '1 digit after dot in JPY' => [
                BigDecimal::of(5.1),
                Currency::JPY,
                '6'
            ],
            '2 digits after dot in JPY' => [
                BigDecimal::of(5.12),
                Currency::JPY,
                '6'
            ],
            '3 digits after dot in JPY' => [
                BigDecimal::of(0.023),
                Currency::JPY,
                '1'
            ],
            '4 digits after dot in JPY' => [
                BigDecimal::of(5.1234),
                Currency::JPY,
                '6'
            ],
            '5 digits after dot in JPY' => [
                BigDecimal::of(5.54321),
                Currency::JPY,
                '6'
            ],
            '6 digits after dot in JPY' => [
                BigDecimal::of(55.043210),
                Currency::JPY,
                '56'
            ],
            '7 digits after dot in JPY' => [
                BigDecimal::of(123.5506432),
                Currency::JPY,
                '124'
            ],
            '8 digits after dot in JPY' => [
                BigDecimal::of(5432.61059875),
                Currency::JPY,
                '5433'
            ],
            '9 digits after dot in JPY' => [
                BigDecimal::of(5432.610598751),
                Currency::JPY,
                '5433'
            ],
            '10 digits after dot in JPY' => [
                BigDecimal::of(5432.6705987512),
                Currency::JPY,
                '5433'
            ],
        ];
    }
}
