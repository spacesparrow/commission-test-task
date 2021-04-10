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
     * @param int $id
     * @param string $type
     * @throws UnsupportedPersonTypeException
     */
    public function __construct(int $id, string $type)
    {
        $this->checkType($type);

        $this->id = $id;
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->checkType($type);

        $this->type = $type;
    }

    /**
     * Check if provided person type exists in config
     *
     * @param string $type
     * @throws UnsupportedPersonTypeException
     */
    private function checkType(string $type)
    {
        if (!in_array($type, AppConfig::getInstance()->get('persons.types'), true)) {
            throw new UnsupportedPersonTypeException($type);
        }
    }
}
