<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use Exception;

/**
 * Class CashOutOperation
 * @package App\CommissionTask\Model
 */
class CashOutOperation extends Operation
{
    /**
     * CashOutOperation constructor.
     *
     * @param Person $person
     * @param string $amount
     * @param string $currencyCode
     * @param int $sequenceNumber
     * @param Money $alreadyUsedThisWeek
     * @param string $date
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
            Operation::TYPE_CASH_OUT,
            $sequenceNumber,
            $alreadyUsedThisWeek
        );
    }

    /**
     * Validate calculated commission
     * For operations with type cash_out there are two different flow based on person type
     * If person type is natural then return original calculated commission
     * If person type is legal then compare with configured min amount and return highest amount
     * Throws exception if person type was not equal to configured values
     *
     * @param BigDecimal $actualCommission
     * @return BigDecimal
     * @throws CurrencyConversionException
     * @throws UnknownCurrencyException
     * @throws UnsupportedPersonTypeException
     */
    protected function validateCommission(BigDecimal $actualCommission): BigDecimal
    {
        if ($this->person->getType() === Person::TYPE_NATURAL) {
            return $actualCommission;
        }

        if ($this->person->getType() === Person::TYPE_LEGAL) {
            $allowedCommissionBase =
                $this->config->get("commissions.$this->type.min_legal_person_amount");
            $limitedCommissionConverted = Currency::convert(
                $allowedCommissionBase,
                Currency::EUR,
                $this->currency->getCurrencyCode()
            );

            return $actualCommission->isGreaterThanOrEqualTo($limitedCommissionConverted)
                ? $actualCommission
                : $limitedCommissionConverted;
        }

        throw new UnsupportedPersonTypeException($this->person->getType());
    }

    /**
     * Get amount for commission calculations
     * For operations with type cash_out there are two different flow based on person type
     * If person type is legal then return operation amount
     * If person type is natural then
     *  - return full amount if person already had three or more cash_out operations during the week
     *  - return full amount if person exceeded week discount 1000 EUR
     *  - return difference between operation amount and 1000 EUR if person had not exceeded week discount 1000 EUR
     * Throws exception if person type was not equal to configured values
     *
     * @return BigDecimal
     * @throws CurrencyConversionException
     * @throws UnknownCurrencyException
     * @throws MoneyMismatchException
     * @throws UnsupportedPersonTypeException
     */
    protected function getAmountForCommission(): BigDecimal
    {
        if ($this->person->getType() === Person::TYPE_LEGAL) {
            return $this->amount;
        }

        if ($this->person->getType() === Person::TYPE_NATURAL) {
            $amountInEur = Currency::convert($this->amount, $this->currency->getCurrencyCode(), Currency::EUR);
            $maxAllowedAmount = BigDecimal::of(
                $this->config->get("commissions.$this->type.max_natural_person_amount")
            );
            $maxAllowedAmountCurrency = $this->config->get(
                "commissions.$this->type.max_natural_person_amount_currency"
            );
            $maxAllowedAmountInEur = Currency::convert($maxAllowedAmount, $maxAllowedAmountCurrency, Currency::EUR);
            $willBeUsedThisWeek = $this->alreadyUsedThisWeek->plus($amountInEur, RoundingMode::UP);

            if ($this->sequenceNumber > $this->config->get("commissions.$this->type.max_natural_person_count")) {
                return $this->amount;
            }

            if ($this->alreadyUsedThisWeek->isGreaterThanOrEqualTo($maxAllowedAmount)) {
                return $this->amount;
            }

            if ($willBeUsedThisWeek->isGreaterThan($maxAllowedAmountInEur)) {
                $amountForCommission = $willBeUsedThisWeek
                    ->minus($maxAllowedAmountInEur, RoundingMode::UP)
                    ->getAmount();

                return Currency::convert($amountForCommission, Currency::EUR, $this->currency->getCurrencyCode());
            }

            return BigDecimal::zero();
        }

        throw new UnsupportedPersonTypeException($this->person->getType());
    }
}
