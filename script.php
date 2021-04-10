<?php

declare(strict_types=1);

namespace App;

use App\CommissionTask\Factory\OperationFactory;
use App\CommissionTask\Model\Operation;
use App\CommissionTask\Model\OperationsHistory;
use App\CommissionTask\Model\Person;
use App\CommissionTask\Service\Output;
use DateTime;
use InvalidArgumentException;
use Throwable;

require_once 'vendor/autoload.php';

try {
    if (empty($argv[1]) || !file_exists($argv[1]) || !is_readable($argv[1])) {
        throw new InvalidArgumentException('You should provide path to a file with input data.');
    }

    $file = fopen($argv[1], 'rb');
    $history = new OperationsHistory();

    while (($data = fgetcsv($file)) !== false) {
        $operationData = array_combine(
            ['date', 'person_id', 'person_type', 'operation_type', 'amount', 'currency'],
            $data
        );
        $person = new Person((int)$operationData['person_id'], $operationData['person_type']);
        $date = new DateTime($operationData['date']);
        $sequenceNumber = 1 + $history->getOperationsCountInWeekForPerson(
            $person,
            $date,
            Operation::TYPE_CASH_OUT
        );
        $alreadyUsedThisWeek = $history->getAmountUsedInWeekForPerson(
            $person,
            $date,
            Operation::TYPE_CASH_OUT
        );
        $operation = OperationFactory::create(
            $person,
            $operationData['amount'],
            $operationData['currency'],
            $operationData['operation_type'],
            $sequenceNumber,
            $alreadyUsedThisWeek,
            $operationData['date']
        );
        $history->push($operation);

        Output::info($operation->getRoundedCommission());
    }
} catch (Throwable $exception) {
    Output::error($exception->getMessage());
}
