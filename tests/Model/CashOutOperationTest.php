<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Model;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Model\CashOutOperation;
use App\CommissionTask\Model\Operation;
use App\CommissionTask\Model\Person;
use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;
use Brick\Money\Money;
use DateTime;
use PHPUnit\Framework\TestCase;

class CashOutOperationTest extends TestCase
{
    /**
     * @covers       \App\CommissionTask\Model\CashOutOperation::__construct
     * @dataProvider dataProviderForConstructSuccessTesting
     *
     * @param string $date
     * @param int $userId
     * @param string $userType
     * @param string $amount
     * @param string $currency
     * @param int $sequenceNumber,
     * @param Money $alreadyUsedThisWeek
     */
    public function testConstructSuccess(
        string $date,
        int $userId,
        string $userType,
        string $amount,
        string $currency,
        int $sequenceNumber,
        Money $alreadyUsedThisWeek
    ) {
        $cashOutOperation = new CashOutOperation(
            $userId,
            $userType,
            $amount,
            $currency,
            $sequenceNumber,
            $alreadyUsedThisWeek,
            $date
        );

        static::assertSame(Operation::TYPE_CASH_OUT, $cashOutOperation->getType());
        static::assertEquals(new DateTime($date), $cashOutOperation->getDate());
        static::assertEquals(new Person($userId, $userType), $cashOutOperation->getUser());
        static::assertEquals(BigDecimal::of($amount), $cashOutOperation->getAmount());
        static::assertEquals(new Currency($currency), $cashOutOperation->getCurrency());
        static::assertSame($sequenceNumber, $cashOutOperation->getSequenceNumber());
        static::assertEquals($alreadyUsedThisWeek, $cashOutOperation->getAlreadyUsedThisWeek());
    }

    /**
     * @covers       \App\CommissionTask\Model\CashOutOperation::__construct
     * @dataProvider dataProviderForConstructThrowsExceptionTesting
     *
     * @param string $date
     * @param int $userId
     * @param string $userType
     * @param string $amount
     * @param string $currency
     * @param int $sequenceNumber
     * @param Money $alreadyUsedThisWeek
     * @param string $exception
     * @param string $exceptionMessage
     */
    public function testConstructThrowsException(
        string $date,
        int $userId,
        string $userType,
        string $amount,
        string $currency,
        int $sequenceNumber,
        Money $alreadyUsedThisWeek,
        string $exception,
        string $exceptionMessage
    ) {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        new CashOutOperation(
            $userId,
            $userType,
            $amount,
            $currency,
            $sequenceNumber,
            $alreadyUsedThisWeek,
            $date
        );
    }

    /**
     * @covers       \App\CommissionTask\Model\CashOutOperation::validateCommission
     * @dataProvider dataProviderForValidateCommissionLegalPersonTesting
     *
     * @param CashOutOperation $operation
     * @param BigDecimal $expectedCommission
     */
    public function testValidateCommissionLegalPerson(CashOutOperation $operation, BigDecimal $expectedCommission)
    {
        $method = new \ReflectionMethod(CashOutOperation::class, 'validateCommission');
        $method->setAccessible(true);

        static::assertTrue(
            $method->invokeArgs($operation, [$expectedCommission])
                ->isEqualTo($operation->getCommission())
        );
    }

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

    public function dataProviderForValidateCommissionLegalPersonTesting(): array
    {
        $config = AppConfig::getInstance();
        $percent = $config->get('commissions.cash_out.default_percent');

        return [
            'less than default in EUR for legal' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_LEGAL,
                    (string)50.00,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(50.00)->multipliedBy($percent)
            ],
            'equal to default in EUR for legal' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_LEGAL,
                    (string)167.00,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(167.00)->multipliedBy($percent)
            ],
            'more than default in EUR for legal' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_LEGAL,
                    (string)200.00,
                    Currency::EUR,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(200.00)->multipliedBy($percent)
            ],
            'less than default in USD for legal' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_LEGAL,
                    (string)50.00,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(50.00)->multipliedBy($percent)
            ],
            'equal to default in USD for legal' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_LEGAL,
                    (string)192.00,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(192.00)->multipliedBy($percent)
            ],
            'more than default in USD for legal' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_LEGAL,
                    (string)500.00,
                    Currency::USD,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(500.00)->multipliedBy($percent)
            ],
            'less than default in JPY for legal' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_LEGAL,
                    (string)15000.00,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(15000.00)->multipliedBy($percent)
            ],
            'equal to default in JPY for legal' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_LEGAL,
                    (string)21600.00,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(21600.00)->multipliedBy($percent)
            ],
            'more than default in JPY for legal' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_LEGAL,
                    (string)50000.00,
                    Currency::JPY,
                    0,
                    Money::zero(Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(50000.00)->multipliedBy($percent)
            ],
            'for natural when operations number per week exceeded' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)50.00,
                    Currency::EUR,
                    4,
                    Money::zero( Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(50.00)->multipliedBy($percent)
            ],
            'for natural when operations amount per week exceeded' => [
                new CashOutOperation(
                    1,
                    Person::TYPE_NATURAL,
                    (string)50.00,
                    Currency::EUR,
                    0,
                    Money::of(1000, Currency::EUR),
                    '2014-12-31'
                ),
                BigDecimal::of(50.00)->multipliedBy($percent)
            ],
        ];
    }
}
