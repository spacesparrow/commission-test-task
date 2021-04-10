<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Service\Currency;
use Brick\Math\RoundingMode;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use DateTime;

class OperationsHistory
{
    /** @var Money[] */
    private $operations;

    /**
     * OperationsHistory constructor.
     */
    public function __construct()
    {
        $this->operations = [];
    }

    /**
     * @return array|Money[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * Write operation to the history
     *
     * @param Operation $operation
     * @return $this
     */
    public function push(Operation $operation): OperationsHistory
    {
        $week = $operation->getDate()->format('Y-W') . "#{$operation->getUser()->getId()}";
        $this->operations[$week][] = $operation;

        return $this;
    }

    /**
     * Get amount that person already used during the week
     * Get week from provided date, filter by operation type if needed
     *
     * @param Person $user
     * @param DateTime $date
     * @param string|null $operationType
     * @return Money
     * @throws CurrencyConversionException
     * @throws MoneyMismatchException
     * @throws UnknownCurrencyException
     */
    public function getAmountUsedInWeekForUser(Person $user, DateTime $date, string $operationType = null): Money
    {
        $this->checkOperationType($operationType);

        $week = $date->format('Y-W') . "#{$user->getId()}";
        $amountInEur = Money::zero(Currency::EUR);

        if (empty($this->operations[$week])) {
            return $amountInEur;
        }

        $weekOperations = $this->operations[$week];
        $operations = $operationType
            ? $this->filterWeekByOperationType($weekOperations, $operationType)
            : $weekOperations;

        /** @var Operation $operation */
        foreach ($operations as $operation) {
            $amountInEur = $amountInEur->plus(
                Currency::convert(
                    $operation->getAmount()->toFloat(),
                    $operation->getCurrency()->getCurrencyCode(),
                    Currency::EUR
                ),
                RoundingMode::UP
            );
        }

        return $amountInEur;
    }

    /**
     * Get operations count person already performed during the week
     * Get week from provided date, filter by operation type if needed
     *
     * @param Person $user
     * @param DateTime $date
     * @param string|null $operationType
     * @return int
     */
    public function getOperationsCountInWeekForUser(Person $user, DateTime $date, string $operationType = null): int
    {
        $this->checkOperationType($operationType);

        $week = $date->format('Y-W') . "#{$user->getId()}";

        if (empty($this->operations[$week])) {
            return 0;
        }

        $weekOperations = $this->operations[$week];
        $operations = $operationType
            ? $this->filterWeekByOperationType($weekOperations, $operationType)
            : $weekOperations;

        return count($operations);
    }

    /**
     * Perform filter by operation type if needed in public methods
     *
     * @param array $operations
     * @param string $operationType
     * @return array
     */
    private function filterWeekByOperationType(array $operations, string $operationType): array
    {
        return array_filter($operations, static function (Operation $operation) use ($operationType) {
            return $operation->getType() === $operationType;
        });
    }

    /**
     * Check if provided operation type exists in config
     *
     * @param string|null $operationType
     * @throws UnsupportedOperationTypeException
     */
    private function checkOperationType(string $operationType = null)
    {
        if (
            $operationType
            && !in_array($operationType, [Operation::TYPE_CASH_IN, Operation::TYPE_CASH_OUT], true)) {
            throw new UnsupportedOperationTypeException($operationType);
        }
    }
}
