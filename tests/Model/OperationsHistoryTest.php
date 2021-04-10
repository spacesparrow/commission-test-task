<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Model;

use App\CommissionTask\Model\CashInOperation;
use App\CommissionTask\Model\CashOutOperation;
use App\CommissionTask\Model\Operation;
use App\CommissionTask\Model\OperationsHistory;
use App\CommissionTask\Model\Person;
use App\CommissionTask\Service\Currency;
use Brick\Math\RoundingMode;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;

class OperationsHistoryTest extends TestCase
{
    /**
     * @covers \App\CommissionTask\Model\OperationsHistory::__construct
     */
    public function testConstruct()
    {
        static::assertAttributeEmpty('operations', new OperationsHistory());
    }

    /**
     * @covers \App\CommissionTask\Model\OperationsHistory::push
     * @throws Exception
     */
    public function testPush()
    {
        $history = new OperationsHistory();
        $operation = new CashOutOperation(
            1,
            Person::TYPE_NATURAL,
            (string)1200.00,
            Currency::EUR,
            0,
            Money::zero(Currency::EUR),
            '2014-12-31'
        );

        static::assertAttributeEmpty('operations', $history);
        $history->push($operation);
        static::assertAttributeNotEmpty('operations', $history);
        $key = $operation->getDate()->format('Y-W') . "#{$operation->getPerson()->getId()}";
        static::assertArrayHasKey($key, $history->getOperations());
        static::assertContains($operation, $history->getOperations()[$key]);
        static::assertCount(1, $history->getOperations());
        static::assertCount(1, $history->getOperations()[$key]);

        $operation = new CashInOperation(
            1,
            Person::TYPE_NATURAL,
            (string)50.00,
            Currency::USD,
            0,
            Money::zero(Currency::EUR),
            '2014-12-31'
        );
        $history->push($operation);
        $key = $operation->getDate()->format('Y-W') . "#{$operation->getPerson()->getId()}";
        static::assertArrayHasKey($key, $history->getOperations());
        static::assertContains($operation, $history->getOperations()[$key]);
        static::assertCount(1, $history->getOperations());
        static::assertCount(2, $history->getOperations()[$key]);

        $operation = new CashInOperation(
            1,
            Person::TYPE_NATURAL,
            (string)50.00,
            Currency::USD,
            0,
            Money::zero(Currency::EUR),
            '2016-12-31'
        );
        $history->push($operation);
        $key = $operation->getDate()->format('Y-W') . "#{$operation->getPerson()->getId()}";
        static::assertArrayHasKey($key, $history->getOperations());
        static::assertContains($operation, $history->getOperations()[$key]);
        static::assertCount(2, $history->getOperations());
        static::assertCount(1, $history->getOperations()[$key]);
    }

    /**
     * @covers       \App\CommissionTask\Model\OperationsHistory::getAmountUsedInWeekForPerson
     * @dataProvider dataProviderForGetAmountUsedInWeekForPersonTesting
     *
     * @param OperationsHistory $history
     * @param Money $expectedAmount
     * @param Person $person
     * @param DateTime $date
     * @param string|null $operationType
     * @throws CurrencyConversionException
     * @throws MoneyMismatchException
     * @throws UnknownCurrencyException
     */
    public function testGetAmountUsedInWeekForPerson(
        OperationsHistory $history,
        Money $expectedAmount,
        Person $person,
        DateTime $date,
        string $operationType = null
    ) {
        static::assertEquals($expectedAmount, $history->getAmountUsedInWeekForPerson($person, $date, $operationType));
    }

    /**
     * @covers       \App\CommissionTask\Model\OperationsHistory::getOperationsCountInWeekForPerson()
     * @dataProvider dataProviderForGetOperationsCountInWeekForPersonTesting
     *
     * @param OperationsHistory $history
     * @param int $expectedCount
     * @param Person $person
     * @param DateTime $date
     * @param string|null $operationType
     */
    public function testGetOperationsCountInWeekForPerson(
        OperationsHistory $history,
        int $expectedCount,
        Person $person,
        DateTime $date,
        string $operationType = null
    ) {
        static::assertSame($expectedCount, $history->getOperationsCountInWeekForPerson($person, $date, $operationType));
    }

    /**
     * @return array[]
     * @throws CurrencyConversionException
     * @throws MoneyMismatchException
     * @throws UnknownCurrencyException
     * @throws Exception
     */
    public function dataProviderForGetAmountUsedInWeekForPersonTesting(): array
    {
        return [
            'empty operations' => [
                new OperationsHistory(),
                Money::zero(Currency::EUR),
                new Person(1, Person::TYPE_LEGAL),
                new DateTime('now')
            ],
            'one operation in EUR' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                Money::of(50, Currency::EUR),
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31')
            ],
            'one operation in EUR filtered by type' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                Money::zero(Currency::EUR),
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31'),
                Operation::TYPE_CASH_OUT
            ],
            'two operations in EUR' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    ))
                    ->push(new CashOutOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                Money::of(100, Currency::EUR),
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31')
            ],
            'two operations in EUR and one filtered by type' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    ))
                    ->push(new CashOutOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                Money::of(50, Currency::EUR),
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31'),
                Operation::TYPE_CASH_IN
            ],
            'two operations in EUR and one filtered by week' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    ))
                    ->push(new CashOutOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2015-01-15'
                    )),
                Money::of(50, Currency::EUR),
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31')
            ],
            'two operations in USD' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::USD,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    ))
                    ->push(new CashOutOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)100,
                        Currency::USD,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                Money::of(
                    Currency::convert(150, Currency::USD, Currency::EUR),
                    Currency::EUR,
                    null,
                    RoundingMode::UP
                ),
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31')
            ],
            'one operation in EUR and one in USD' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    ))
                    ->push(new CashOutOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)100,
                        Currency::USD,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                Money::of(50, Currency::EUR)
                    ->plus(Money::of(
                        Currency::convert(100, Currency::USD, Currency::EUR),
                        Currency::EUR,
                        null,
                        RoundingMode::UP
                    )),
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31')
            ]
        ];
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function dataProviderForGetOperationsCountInWeekForPersonTesting(): array
    {
        return [
            'empty operations' => [
                new OperationsHistory(),
                0,
                new Person(1, Person::TYPE_LEGAL),
                new DateTime('now')
            ],
            'one operation in week' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                1,
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31')
            ],
            'one operation in week filtered by type' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                0,
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31'),
                Operation::TYPE_CASH_OUT
            ],
            'two operations in week' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    ))
                    ->push(new CashOutOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                2,
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31')
            ],
            'two operations in week and one filtered by type' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    ))
                    ->push(new CashOutOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                1,
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31'),
                Operation::TYPE_CASH_IN
            ],
            'two operations and one filtered by week' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    ))
                    ->push(new CashOutOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::EUR,
                        0,
                        Money::zero(Currency::EUR),
                        '2015-01-15'
                    )),
                1,
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31')
            ],
            'two operations in week for different persons' => [
                (new OperationsHistory())
                    ->push(new CashInOperation(
                        1,
                        Person::TYPE_NATURAL,
                        (string)50,
                        Currency::USD,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    ))
                    ->push(new CashOutOperation(
                        2,
                        Person::TYPE_LEGAL,
                        (string)100,
                        Currency::USD,
                        0,
                        Money::zero(Currency::EUR),
                        '2014-12-31'
                    )),
                1,
                new Person(1, Person::TYPE_NATURAL),
                new DateTime('2014-12-31')
            ],
        ];
    }
}
