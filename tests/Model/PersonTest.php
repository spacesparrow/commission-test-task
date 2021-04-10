<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Model;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Model\Person;
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{
    /**
     * @covers       \App\CommissionTask\Model\Person::__construct
     * @dataProvider dataProviderForConstructSuccessTesting
     *
     * @param int $id
     * @param string $type
     * @param Person $person
     */
    public function testConstructSuccess(int $id, string $type, Person $person)
    {
        static::assertEquals($person, new Person($id, $type));
    }

    /**
     * @covers \App\CommissionTask\Model\Person::__construct
     */
    public function testConstructThrowsException()
    {
        $unsupportedType = 'new';

        $this->expectException(UnsupportedPersonTypeException::class);
        $this->expectExceptionMessage("Unsupported person type was provided $unsupportedType");

        new Person(1, $unsupportedType);
    }

    /**
     * @return array
     */
    public function dataProviderForConstructSuccessTesting(): array
    {
        $personTypes = AppConfig::getInstance()->get('persons.types');
        $i = 0;

        return array_map(static function (string $type) use ($i) {
            return [++$i, $type, new Person($i, $type)];
        }, $personTypes);
    }
}
