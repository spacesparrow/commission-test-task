<?php

declare(strict_types=1);

namespace App\CommissionTask\Service;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class Math
{
    /** @var int  */
    private $scale;

    public function __construct(int $scale)
    {
        $this->scale = $scale;
    }

    public function round(BigDecimal $amount): float
    {
        return $amount->toScale($this->scale, RoundingMode::UP)->toFloat();
    }
}
