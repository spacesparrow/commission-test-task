<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Model;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Model\CashInOperation;
use App\CommissionTask\Model\Operation;
use App\CommissionTask\Model\Person;
use App\CommissionTask\Service\Currency;
use App\CommissionTask\Service\Math;
use Brick\Math\BigDecimal;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

class CashInOperationTest extends TestCase
{
    /**
     * @covers       \App\CommissionTask\Model\CashInOperation::__construct
     * @dataProvider dataProviderForConstructSuccessTesting
     *
     * @param string $date
     * @param int $personId
     * @param string $personType
     * @param string $amount
     * @param string $currency
     * @param int $sequenceNumber
     * @param Money $alreadyUsedThisWeek
     * @throws Exception
     */
    public function testConstructSuccess(
        string $date,
        int $personId,
        string $personType,
        string $amount,
        string $currency,
        int $sequenceNumber,
        Money $alreadyUsedThisWeek
    ) {
        $cashInOperation = new CashInOperation(
            $personId,
            $personType,
            $amount,
            $currency,
            $sequenceNumber,
            $alreadyUsedThisWeek,
            $date
        );

        static::assertSame(Operation::TYPE_CASH_IN, $cashInOperation->getType());
        static::assertEquals(new DateTime($date), $cashInOperation->getDate());
        static::assertEquals(new Person($personId, $personType), $cashInOperation->getPerson());
        static::assertEquals(BigDecimal::of($amount), $cashInOperation->getAmount());
        static::assertEquals(new Currency($currency), $cashInOperation->getCurrency());
        static::assertSame($sequenceNumber, $cashInOperation->getSequenceNumber());
        static::assertEquals($alreadyUsedThisWeek, $cashInOperation->getAlreadyUsedThisWeek());
    }

    /**
     * @covers       \App\CommissionTask\Model\CashInOperation::__construct
     * @dataProvider dataProviderForConstructThrowsExceptionTesting
     *
     * @param string $date
     * @param int $personId
     * @param string $personType
     * @param string $amount
     * @param string $currency
     * @param int $sequenceNumber
     * @param Money $alreadyUsedThisWeek
     * @param string $exception
     * @param string $exceptionMessage
     * @throws Exception
     */
    public function testConstructThrowsException(
        string $date,
        int $personId,
        string $personType,
        string $amount,
        string $currency,
        int $sequenceNumber,
        Money $alreadyUsedThisWeek,
        string $exception,
        string $exceptionMessage
    ) {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        new CashInOperation(
            $personId,
            $personType,
            $amount,
            $currency,
            $sequenceNumber,
            $alreadyUsedThisWeek,
            $date
        );
    }

    /**
     * @covers       \App\CommissionTask\Model\CashInOperation::validateCommission
     * @dataProvider dataProviderForValidateCommissionTesting
     *
     * @param CashInOperation $operation
     * @param BigDecimal $expectedCommission
     * @throws ReflectionException
     */
    public function testValidateCommission(CashInOperation $operation, BigDecimal $expectedCommission)
    {
        $method = new ReflectionMethod(CashInOperation::class, 'validateCommission');
        $method->setAccessible(true);

        static::assertTrue(
            $method->invokeArgs($operation, [$expectedCommission])
                ->isEqualTo($operation->getCommission())
        );
    }

    /**
     * @covers       \App\CommissionTask\Model\CashInOperation::getAmountForCommission
     * @dataProvider dataProviderForGetAmountForCommissionTesting
     *
     * @param CashInOperation $operation
     * @param BigDecimal $expectedAmount
     * @throws ReflectionException
     */
    public function testGetAmountForCommission(CashInOperation $operation, BigDecimal $expectedAmount)
    {
        $method = new ReflectionMethod(CashInOperation::class, 'getAmountForCommission');
        $method->setAccessible(true);

        static::assertTrue(
            $method->invokeArgs($operation, [])
                ->isEqualTo($expectedAmount)
        );
    }

    /**
     * @covers       \App\CommissionTask\Model\CashInOperation::getCommission
     * @dataProvider dataProviderForGetCommissionTesting
     *
     * @param CashInOperation $operation
     * @param BigDecimal $expectedCommission
     */
    public function testGetCommission(CashInOperation $operation, BigDecimal $expectedCommission)
    {
        static::assertTrue(
            $expectedCommission->isEqualTo($operation->getCommission())
        );
    }

    /**
     * @covers       \App\CommissionTask\Model\CashInOperation::getRoundedCommission
     * @dataProvider dataProviderForGetRoundedCommissionTesting
     *
     * @param CashInOperation $operation
     * @param string $expectedCommission
     */
    public function testGetRoundedCommission(CashInOperation $operation, string $expectedCommission)
    {
        static::assertSame($expectedCommission, $operation->getRoundedCommission());
    }

    /**
     * @return array[]
     */
    public function dataProviderForConstructSuccessTesting(): array
    {
        return [
            'natural person EUR' => [
                '2014-12-31',
                1,
                Person::TYPE_NATURAL,
                (string)1200.00,
                Currency::EUR,
                0,
                Money::zero(Currency::EUR)
            ],
            'natural person USD' => [
                '2015-05-03',
                4,
                Person::TYPE_NATURAL,
                (string)125.00,
                Currency::USD,
                0,
                Money::zero(Currency::EUR)
            ],
            'natural person JPY' => [
                '2015-01-06',
                2,
                Person::TYPE_NATURAL,
                (string)5.67,
                Currency::JPY,
                0,
                Money::zero(Currency::EUR)
            ],
            'legal person EUR' => [
                '2016-07-28',
                3,
                Person::TYPE_LEGAL,
                (string)100.50,
                Currency::EUR,
                0,
                Money::zero(Currency::EUR)
            ],
            'legal person USD' => [
                '2016-11-25',
                1,
                Person::TYPE_LEGAL,
                (string)554.35,
                Currency::USD,
                0,
                Money::zero(Currency::EUR)
            ],
            'legal person JPY' => [
                '2017-02-28',
                1,
                Person::TYPE_LEGAL,
                (string)34.05,
                Currency::JPY,
                0,
                Money::zero(Currency::EUR)
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function dataProviderForConstructThrowsExceptionTesting(): array
    {
        return [
            'UnsupportedPersonTypeException' => [
                '2014-12-31',
                1,
                'person',
                (string)1200.00,
                Currency::EUR,
                0,
                Money::zero(Currency::EUR),
                UnsupportedPersonTypeException::class,
                sprintf('Unsupported person type was provided %s', 'person')
            ],
            'UnsupportedCurrencyException' => [
                '2014-12-31',
                1,
                Person::TYPE_LEGAL,
                (string)1200.00,
                'UAH',
                0,
                Money::zero(Currency::EUR),
                UnsupportedCurrencyException::class,
                sprintf('Unsupported currency was provided %s', 'UAH')
            ]
        ];
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function dataProviderForValidateCommissionTesting(): array
    {
        $config = AppConfig::getInstance();
        $percent = $config->get('commissions.cash_in.default_percent');

        return [
            'less that default in EUR' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)200.00,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(200.00)->multipliedBy($percent)
            ],
            'equal to default in EUR' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)16666.67,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(16666.67)->multipliedBy($percent)
            ],
            'more than default in EUR' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)25000,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(25000)->multipliedBy($percent)
            ],
            'less that default in USD' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)200.00,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(200.00)->multipliedBy($percent)
            ],
            'equal to default in USD' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)19161.67,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(19161.67)->multipliedBy($percent)
            ],
            'more than default in USD' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)25000,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(25000)->multipliedBy($percent)
            ],
            'less that default in JPY' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)200.00,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(200.00)->multipliedBy($percent)
            ],
            'equal to default in JPY' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)2158833.7651,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(2158833.7651)->multipliedBy($percent)
            ],
            'more than default in JPY' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)3238250,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(3238250)->multipliedBy($percent)
            ],
        ];
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function dataProviderForGetAmountForCommissionTesting(): array
    {
        return [
            'operation in EUR' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)200.00,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(200.00)
            ],
            'operation in USD' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)16666.67,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(16666.67)
            ],
            'operation in JPY' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)25000,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(25000)
            ],
        ];
    }

    /**
     * @return array[]
     * @throws CurrencyConversionException
     * @throws UnknownCurrencyException
     * @throws Exception
     */
    public function dataProviderForGetCommissionTesting(): array
    {
        $config = AppConfig::getInstance();
        $percent = $config->get('commissions.cash_in.default_percent');
        $maxAmountInEur = $config->get('commissions.cash_in.max_amount');
        $maxAmountInUsd = Currency::convert(BigDecimal::of($maxAmountInEur), Currency::EUR, Currency::USD);
        $maxAmountInJpy = Currency::convert(BigDecimal::of($maxAmountInEur), Currency::EUR, Currency::JPY);

        return [
            'less that default in EUR' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)200.00,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(200.00)->multipliedBy($percent)
            ],
            'equal to default in EUR' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)16666.67,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of($maxAmountInEur)
            ],
            'more than default in EUR' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)25000,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of($maxAmountInEur)
            ],
            'less that default in USD' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)200.00,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(200.00)->multipliedBy($percent)
            ],
            'equal to default in USD' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)19161.67,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $maxAmountInUsd
            ],
            'more than default in USD' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)25000,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $maxAmountInUsd
            ],
            'less that default in JPY' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)200.00,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(200.00)->multipliedBy($percent)
            ],
            'equal to default in JPY' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)2158833.7651,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $maxAmountInJpy
            ],
            'more than default in JPY' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)3238250,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $maxAmountInJpy
            ],
        ];
    }

    /**
     * @return array[]
     * @throws CurrencyConversionException
     * @throws UnknownCurrencyException
     * @throws Exception
     */
    public function dataProviderForGetRoundedCommissionTesting(): array
    {
        $config = AppConfig::getInstance();
        $roundingScale = $config->get('rounding_scale');
        $math = new Math($roundingScale);
        $percent = $config->get('commissions.cash_in.default_percent');
        $maxAmountInEur = BigDecimal::of($config->get('commissions.cash_in.max_amount'));
        $maxAmountInUsd = Currency::convert(BigDecimal::of($maxAmountInEur), Currency::EUR, Currency::USD);
        $maxAmountInJpy = Currency::convert(BigDecimal::of($maxAmountInEur), Currency::EUR, Currency::JPY);

        return [
            'less that default in EUR' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)200.00,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $math->round(
                    BigDecimal::of(200.00)->multipliedBy($percent),
                    Currency::EUR
                )
            ],
            'equal to default in EUR' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)16666.67,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $math->round(
                    $maxAmountInEur,
                    Currency::EUR
                )
            ],
            'more than default in EUR' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)25000,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $math->round(
                    $maxAmountInEur,
                    Currency::EUR
                )
            ],
            'less that default in USD' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)200.00,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $math->round(
                    BigDecimal::of(200.00)->multipliedBy($percent),
                    Currency::USD
                )
            ],
            'equal to default in USD' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)19161.67,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $math->round(
                    $maxAmountInUsd,
                    Currency::USD
                )
            ],
            'more than default in USD' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)25000,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $math->round(
                    $maxAmountInUsd,
                    Currency::USD
                )
            ],
            'less that default in JPY' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)200.00,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $math->round(
                    BigDecimal::of(200.00)->multipliedBy($percent),
                    Currency::JPY
                )
            ],
            'equal to default in JPY' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)2158833.7651,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $math->round(
                    $maxAmountInJpy,
                    Currency::JPY
                )
            ],
            'more than default in JPY' => [
                new CashInOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)3238250,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                $math->round(
                    $maxAmountInJpy,
                    Currency::JPY
                )
            ],
        ];
    }
}
