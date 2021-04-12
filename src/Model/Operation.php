<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Service\Currency;
use App\CommissionTask\Service\Math;
use Brick\Math\BigDecimal;
use Brick\Money\Money;
use DateTime;
use Exception;

abstract class Operation
{
    const TYPE_CASH_IN = 'cash_in';
    const TYPE_CASH_OUT = 'cash_out';

    /** @var string */
    protected $type;

    /** @var DateTime */
    protected $date;

    /** @var Person */
    protected $person;

    /** @var BigDecimal */
    protected $amount;

    /** @var Currency */
    protected $currency;

    /** @var AppConfig */
    protected $config;

    /** @var int */
    protected $sequenceNumber;

    /** @var Money */
    protected $alreadyUsedThisWeek;

    /** @var Math */
    private $math;

    /**
     * Operation constructor.
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
        string $date,
        string $type,
        int $sequenceNumber,
        Money $alreadyUsedThisWeek
    ) {
        $this->checkType($type);

        $this->type = $type;
        $this->date = new DateTime($date);
        $this->person = $person;
        $this->currency = new Currency($currencyCode);
        $this->amount = BigDecimal::of($amount);
        $this->config = AppConfig::getInstance();
        $this->sequenceNumber = $sequenceNumber;
        $this->alreadyUsedThisWeek = $alreadyUsedThisWeek;
        $this->math = new Math($this->config->get('rounding_scale'));
    }

    /**
     * Calculate commission
     * Get amount based on operation and person type and then validate based on rules.
     */
    public function getCommission(): BigDecimal
    {
        $amountForCommission = $this->getAmountForCommission();
        $commissionPercent = $this->config->get("commissions.$this->type.default_percent");
        $commission = $amountForCommission->multipliedBy($commissionPercent);

        return $this->validateCommission($commission);
    }

    public function getRoundedCommission(): string
    {
        $commission = $this->getCommission();

        return $this->math->round($commission->toBigDecimal(), $this->currency->getCurrencyCode());
    }

    /**
     * Check if provided operation type exists in config.
     *
     * @throws UnsupportedOperationTypeException
     */
    private function checkType(string $type)
    {
        if (!in_array($type, AppConfig::getInstance()->get('operations.types'), true)) {
            throw new UnsupportedOperationTypeException($type);
        }
    }

    abstract protected function validateCommission(BigDecimal $actualCommission): BigDecimal;

    abstract protected function getAmountForCommission(): BigDecimal;

    public function getType(): string
    {
        return $this->type;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getPerson(): Person
    {
        return $this->person;
    }

    public function getAmount(): BigDecimal
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    public function getAlreadyUsedThisWeek(): Money
    {
        return $this->alreadyUsedThisWeek;
    }
}
