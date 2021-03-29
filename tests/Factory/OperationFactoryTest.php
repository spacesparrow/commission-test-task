<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Factory;

use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Factory\OperationFactory;
use App\CommissionTask\Model\Operation;
use App\CommissionTask\Model\Person;
use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;
use DateTime;
use PHPUnit\Framework\TestCase;

class OperationFactoryTest extends TestCase
{
    /**
     * @covers       \App\CommissionTask\Factory\OperationFactory::create
     * @dataProvider dataProviderForCreateSuccessTesting
     *
     * @param string $date
     * @param int $userId
     * @param string $userType
     * @param string $operationType
     * @param string $amount
     * @param string $currency
     */
    public function testCreateSuccess(
        string $date,
        int $userId,
        string $userType,
        string $operationType,
        string $amount,
        string $currency
    ){
        $operation = OperationFactory::create(
            $userId,
            $userType,
            $amount,
            $currency,
            $operationType,
            $date
        );

        static::assertSame($operationType, $operation->getType());
        static::assertEquals(new DateTime($date), $operation->getDate());
        static::assertEquals(new Person($userId, $userType), $operation->getUser());
        static::assertEquals(BigDecimal::of($amount), $operation->getAmount());
        static::assertEquals(new Currency($currency), $operation->getCurrency());
    }

    /**
     * @covers \App\CommissionTask\Factory\OperationFactory::create
     * @dataProvider dataProviderForCreateThrowsExceptionTesting
     *
     * @param string $date
     * @param int $userId
     * @param string $userType
     * @param string $operationType
     * @param string $amount
     * @param string $currency
     * @param string $exception
     * @param string $exceptionMessage
     */
    public function testCreateThrowsException(
        string $date,
        int $userId,
        string $userType,
        string $operationType,
        string $amount,
        string $currency,
        string $exception,
        string $exceptionMessage
    ){
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        OperationFactory::create(
            $userId,
            $userType,
            $amount,
            $currency,
            $operationType,
            $date
        );
    }

    public function dataProviderForCreateSuccessTesting(): array
    {
        return [
            'cash_in natural person EUR' => [
                '2014-12-31',
                1,
                Person::TYPE_NATURAL,
                Operation::TYPE_CASH_IN,
                (string)1200.00,
                Currency::EUR
            ],
            'cash_in natural person USD' => [
                '2015-05-03',
                4,
                Person::TYPE_NATURAL,
                Operation::TYPE_CASH_IN,
                (string)125.00,
                Currency::USD
            ],
            'cash_in natural person JPY' => [
                '2015-01-06',
                2,
                Person::TYPE_NATURAL,
                Operation::TYPE_CASH_IN,
                (string)5.67,
                Currency::JPY
            ],
            'cash_in legal person EUR' => [
                '2016-07-28',
                3,
                Person::TYPE_LEGAL,
                Operation::TYPE_CASH_IN,
                (string)100.50,
                Currency::EUR
            ],
            'cash_in legal person USD' => [
                '2016-11-25',
                1,
                Person::TYPE_LEGAL,
                Operation::TYPE_CASH_IN,
                (string)554.35,
                Currency::USD
            ],
            'cash_in legal person JPY' => [
                '2017-02-28',
                1,
                Person::TYPE_LEGAL,
                Operation::TYPE_CASH_IN,
                (string)34.05,
                Currency::JPY
            ],
            'cash_out natural person EUR' => [
                '2014-12-31',
                1,
                Person::TYPE_NATURAL,
                Operation::TYPE_CASH_OUT,
                (string)1200.00,
                Currency::EUR
            ],
            'cash_out natural person USD' => [
                '2015-05-03',
                4,
                Person::TYPE_NATURAL,
                Operation::TYPE_CASH_OUT,
                (string)125.00,
                Currency::USD
            ],
            'cash_out natural person JPY' => [
                '2015-01-06',
                2,
                Person::TYPE_NATURAL,
                Operation::TYPE_CASH_OUT,
                (string)5.67,
                Currency::JPY
            ],
            'cash_out legal person EUR' => [
                '2016-07-28',
                3,
                Person::TYPE_LEGAL,
                Operation::TYPE_CASH_OUT,
                (string)100.50,
                Currency::EUR
            ],
            'cash_out legal person USD' => [
                '2016-11-25',
                1,
                Person::TYPE_LEGAL,
                Operation::TYPE_CASH_OUT,
                (string)554.35,
                Currency::USD
            ],
            'cash_out legal person JPY' => [
                '2017-02-28',
                1,
                Person::TYPE_LEGAL,
                Operation::TYPE_CASH_OUT,
                (string)34.05,
                Currency::JPY
            ],
        ];
    }

    public function dataProviderForCreateThrowsExceptionTesting(): array
    {
        return [
            'UnsupportedPersonTypeException' => [
                '2014-12-31',
                1,
                'person',
                Operation::TYPE_CASH_IN,
                (string)1200.00,
                Currency::EUR,
                UnsupportedPersonTypeException::class,
                sprintf('Unsupported person type was provided %s', 'person')
            ],
            'UnsupportedCurrencyException' => [
                '2014-12-31',
                1,
                Person::TYPE_LEGAL,
                Operation::TYPE_CASH_IN,
                (string)1200.00,
                'UAH',
                UnsupportedCurrencyException::class,
                sprintf('Unsupported currency was provided %s', 'UAH')
            ],
            'UnsupportedOperationTypeException' => [
                '2014-12-31',
                1,
                Person::TYPE_NATURAL,
                'cash',
                (string)1200.00,
                Currency::EUR,
                UnsupportedOperationTypeException::class,
                sprintf(
                    'Unsupported operation type was provided %s',
                    'cash'
                )
            ]
        ];
    }
}