<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Service\Currency;
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
    protected $user;

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

    /**
     * Operation constructor.
     *
     * @param int $userId
     * @param string $userType
     * @param string $amount
     * @param string $currencyCode
     * @param string $date
     * @param string $type
     * @param int $sequenceNumber
     * @param Money $alreadyUsedThisWeek
     *
     * @throws Exception
     * @throws UnsupportedOperationTypeException
     * @throws UnsupportedPersonTypeException
     * @throws UnsupportedCurrencyException
     */
    public function __construct(
        int $userId,
        string $userType,
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
        $this->user = new Person($userId, $userType);
        $this->currency = new Currency($currencyCode);
        $this->amount = BigDecimal::of($amount);
        $this->config = AppConfig::getInstance();
        $this->sequenceNumber = $sequenceNumber;
        $this->alreadyUsedThisWeek = $alreadyUsedThisWeek;
    }

    public function getCommission(): BigDecimal
    {
        $amountForCommission = $this->getAmountForCommission();
        $commissionPercent = $this->config->get("commissions.{$this->type}.default_percent");
        $commission = $amountForCommission->multipliedBy($commissionPercent);

        return $this->validateCommission($commission);
    }

    private function checkType(string $type)
    {
        if (!in_array($type, AppConfig::getInstance()->get('operations.types'), true)) {
            throw new UnsupportedOperationTypeException($type);
        }
    }

    abstract protected function validateCommission(BigDecimal $actualCommission): BigDecimal;

    abstract protected function getAmountForCommission(): BigDecimal;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @return Person
     */
    public function getUser(): Person
    {
        return $this->user;
    }

    /**
     * @return BigDecimal
     */
    public function getAmount(): BigDecimal
    {
        return $this->amount;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return int
     */
    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    /**
     * @return Money
     */
    public function getAlreadyUsedThisWeek(): Money
    {
        return $this->alreadyUsedThisWeek;
    }
}
