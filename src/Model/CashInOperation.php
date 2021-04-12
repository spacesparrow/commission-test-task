<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use Exception;

/**
 * Class CashInOperation.
 */
class CashInOperation extends Operation
{
    /**
     * CashInOperation constructor.
     *
     * @throws Exception
     * @throws UnsupportedOperationTypeException
     * @throws UnsupportedPersonTypeException
     * @throws UnsupportedCurrencyException
     */
    public function __construct(
        Person $person,
        string $amount,
        string $currencyCode,
        int $sequenceNumber,
        Money $alreadyUsedThisWeek,
        string $date = 'now'
    ) {
        parent::__construct(
            $person,
            $amount,
            $currencyCode,
            $date,
            Operation::TYPE_CASH_IN,
            $sequenceNumber,
            $alreadyUsedThisWeek
        );
    }

    /**
     * Validate calculated commission
     * For operations with type cash_in compare with configured max amount and return lowest value.
     *
     * @throws CurrencyConversionException
     * @throws UnknownCurrencyException
     */
    protected function validateCommission(BigDecimal $actualCommission): BigDecimal
    {
        $allowedCommissionBase = $this->config->get("commissions.$this->type.max_amount");
        $limitedCommissionConverted = Currency::convert(
            $allowedCommissionBase,
            Currency::EUR,
            $this->currency->getCurrencyCode()
        );

        return $actualCommission->isLessThanOrEqualTo($limitedCommissionConverted)
            ? $actualCommission
            : $limitedCommissionConverted;
    }

    /**
     * Get amount for commission calculations
     * For operations with type cash_in equals to operation amount.
     */
    protected function getAmountForCommission(): BigDecimal
    {
        return $this->amount;
    }
}
