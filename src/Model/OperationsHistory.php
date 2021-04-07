<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Service\Currency;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use DateTime;

class OperationsHistory
{
    /** @var Money[] */
    private $operations;

    public function __construct()
    {
        $this->operations = [];
    }

    public function getOperations(): array
    {
        return $this->operations;
    }

    public function push(Operation $operation): OperationsHistory
    {
        $week = $operation->getDate()->format('Y-W') . "#{$operation->getUser()->getId()}";
        $this->operations[$week][] = $operation;

        return $this;
    }

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

    private function filterWeekByOperationType(array $operations, string $operationType): array
    {
        return array_filter($operations, static function (Operation $operation) use ($operationType) {
            return $operation->getType() === $operationType;
        });
    }

    private function checkOperationType(string $operationType = null)
    {
        if (
            $operationType
            && !in_array($operationType, [Operation::TYPE_CASH_IN, Operation::TYPE_CASH_OUT], true)) {
            throw new UnsupportedOperationTypeException($operationType);
        }
    }
}
