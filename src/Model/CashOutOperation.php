<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;

class CashOutOperation extends Operation
{
    public function validateCommission(BigDecimal $actualCommission): BigDecimal
    {
        if ($this->user->getType() === Person::TYPE_LEGAL) {
            $allowedCommissionBase =
                $this->config->get("commissions.{$this->type}.{$this->user->getType()}.min_legal_person_amount");
            $limitedCommissionConverted = Currency::convert(
                $allowedCommissionBase,
                Currency::EUR,
                $this->currency
            );

            return $actualCommission->isGreaterThanOrEqualTo($limitedCommissionConverted)
                ? $actualCommission
                : $limitedCommissionConverted;
        }
    }
}