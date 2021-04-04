<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Service;

use App\CommissionTask\AppConfig;
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
     * @covers \App\CommissionTask\Service\Math::round
     * @dataProvider dataProviderForRoundTesting
     *
     * @param BigDecimal $amount
     * @param float $expectedRoundedAmount
     */
    public function testRound(BigDecimal $amount, float $expectedRoundedAmount)
    {
        static::assertSame($expectedRoundedAmount, $this->math->round($amount));
    }

    public function dataProviderForRoundTesting(): array
    {
        return [
            '0 digits after dot' => [
                BigDecimal::of(5),
                5.00
            ],
            '1 digit after dot' => [
                BigDecimal::of(5.1),
                5.10
            ],
            '2 digits after dot' => [
                BigDecimal::of(5.12),
                5.12
            ],
            '3 digits after dot' => [
                BigDecimal::of(0.023),
                0.03
            ],
            '4 digits after dot' => [
                BigDecimal::of(5.1234),
                5.13
            ],
            '5 digits after dot' => [
                BigDecimal::of(5.54321),
                5.55
            ],
            '6 digits after dot' => [
                BigDecimal::of(55.043210),
                55.05
            ],
            '7 digits after dot' => [
                BigDecimal::of(123.5506432),
                123.56
            ],
            '8 digits after dot' => [
                BigDecimal::of(5432.61059875),
                5432.62
            ],
            '9 digits after dot' => [
                BigDecimal::of(5432.610598751),
                5432.62
            ],
            '10 digits after dot' => [
                BigDecimal::of(5432.6705987512),
                5432.68
            ],
        ];
    }
}
