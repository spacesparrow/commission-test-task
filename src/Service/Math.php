<?php

declare(strict_types=1);

namespace App\CommissionTask\Service;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class Math
{
    /** @var int */
    private $scale;

    /**
     * Math constructor.
     */
    public function __construct(int $scale)
    {
        $this->scale = $scale;
    }

    /**
     * Round amount based on currency
     * For USD and EUR - two digits after dot
     * For JPY - zero digits after dot.
     */
    public function round(BigDecimal $amount, string $currency): string
    {
        $scale = $currency === Currency::JPY ? 0 : $this->scale;

        return (string) $amount->toScale($scale, RoundingMode::UP);
    }
}
