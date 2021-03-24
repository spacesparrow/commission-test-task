<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;

class Person
{
    public const TYPE_NATURAL = 'natural';
    public const TYPE_LEGAL = 'legal';

    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /**
     * Person constructor.
     *
     * @param int $id
     * @param string $type
     *
     * @throws UnsupportedPersonTypeException
     */
    public function __construct(int $id, string $type)
    {
        $this->checkType($type);

        $this->id = $id;
        $this->type = $type;
    }

    /**
     * @param string $type
     *
     * @throws UnsupportedPersonTypeException
     */
    private function checkType(string $type): void
    {
        if (!in_array($type, AppConfig::getInstance()->get('persons.types'), true)) {
            throw new UnsupportedPersonTypeException($type);
        }
    }
}