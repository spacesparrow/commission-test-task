<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

class CashOutOperation extends Operation
{
    public function __construct(
        int $userId,
        string $userType,
        string $amount,
        string $currencyCode,
        int $sequenceNumber,
        Money $alreadyUserThisWeek,
        string $date = 'now'
    ) {
        parent::__construct(
            $userId,
            $userType,
            $amount,
            $currencyCode,
            $date,
            Operation::TYPE_CASH_OUT,
            $sequenceNumber,
            $alreadyUserThisWeek
        );
    }

    protected function validateCommission(BigDecimal $actualCommission): BigDecimal
    {
        if ($this->user->getType() === Person::TYPE_NATURAL) {
            return $actualCommission;
        }

        if ($this->user->getType() === Person::TYPE_LEGAL) {
            $allowedCommissionBase =
                $this->config->get("commissions.{$this->type}.min_legal_person_amount");
            $limitedCommissionConverted = Currency::convert(
                $allowedCommissionBase,
                Currency::EUR,
                $this->currency->getCurrencyCode()
            );

            return $actualCommission->isGreaterThanOrEqualTo($limitedCommissionConverted)
                ? $actualCommission
                : $limitedCommissionConverted;
        }

        throw new UnsupportedOperationTypeException($this->type);
    }

    protected function getAmountForCommission(): BigDecimal
    {
        if ($this->user->getType() === Person::TYPE_LEGAL) {
            return $this->amount;
        }

        $amountInEur = Currency::convert($this->amount, $this->currency->getCurrencyCode(), Currency::EUR);
        $maxAllowedAmount = BigDecimal::of(
            $this->config->get("commissions.{$this->type}.max_natural_person_amount")
        );
        $maxAllowedAmountCurrency = $this->config->get(
            "commissions.{$this->type}.max_natural_person_amount_currency"
        );
        $maxAllowedAmountInEur = Currency::convert($maxAllowedAmount, $maxAllowedAmountCurrency, Currency::EUR);
        $willBeUsedThisWeek = $this->alreadyUsedThisWeek->plus($amountInEur, RoundingMode::UP);

        if ($this->user->getType() === Person::TYPE_NATURAL) {
            if ($this->sequenceNumber > $this->config->get("commissions.{$this->type}.max_natural_person_count")) {
                return $this->amount;
            }

            if ($willBeUsedThisWeek->isGreaterThan($maxAllowedAmountInEur)) {
                return $willBeUsedThisWeek->minus($maxAllowedAmountInEur, RoundingMode::UP)->getAmount();
            }

            return BigDecimal::zero();
        }

        throw new UnsupportedOperationTypeException($this->type);
    }
}
