<?php

declare(strict_types=1);

namespace App\CommissionTask\Service;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class Math
{
    /** @var int  */
    private $scale;

    /**
     * Math constructor.
     *
     * @param int $scale
     */
    public function __construct(int $scale)
    {
        $this->scale = $scale;
    }

    /**
     * Round amount based on currency
     * For USD and EUR - two digits after dot
     * For JPY - zero digits after dot
     *
     * @param BigDecimal $amount
     * @param string $currency
     * @return float
     */
    public function round(BigDecimal $amount, string $currency): float
    {
        $scale = $currency === Currency::JPY ? 0 : $this->scale;

        return $amount->toScale($scale, RoundingMode::UP)->toFloat();
    }
}
