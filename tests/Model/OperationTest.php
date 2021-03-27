<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Model;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Model\Operation;
use PHPUnit\Framework\TestCase;

class OperationTest extends TestCase
{
    /**
     * @covers \App\CommissionTask\Model\Operation::__construct
     * @dataProvider dataProviderForConstructSuccessTesting
     *
     * @param string $type
     * @param string $date
     * @param Operation $operation
     */
    public function testConstructSuccess(string $type, string $date, Operation $operation)
    {
        static::assertEquals($operation, new Operation($type, $date));
    }

    /**
     * @covers \App\CommissionTask\Model\Operation::__construct
     */
    public function testConstructThrowsException()
    {
        $unsupportedType = 'now';

        $this->expectException(UnsupportedOperationTypeException::class);
        $this->expectExceptionMessage("Unsupported operation type was provided $unsupportedType");

        new Operation($unsupportedType);
    }

    public function dataProviderForConstructSuccessTesting(): array
    {
        $operationsTypes = AppConfig::getInstance()->get('operations.types');
        $date = '2020-02-02';

        return array_map(static function (string $type) use ($date) {
            return [$type, $date, new Operation($type, $date)];
        }, $operationsTypes);
    }
}