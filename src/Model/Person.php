<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;

class Person
{
    const TYPE_NATURAL = 'natural';
    const TYPE_LEGAL = 'legal';

    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /**
     * Person constructor.
     *
     * @throws UnsupportedPersonTypeException
     */
    public function __construct(int $id, string $type)
    {
        $this->checkType($type);

        $this->id = $id;
        $this->type = $type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->checkType($type);

        $this->type = $type;
    }

    /**
     * Check if provided person type exists in config.
     *
     * @throws UnsupportedPersonTypeException
     */
    private function checkType(string $type)
    {
        if (!in_array($type, AppConfig::getInstance()->get('persons.types'), true)) {
            throw new UnsupportedPersonTypeException($type);
        }
    }
}
