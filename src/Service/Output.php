<?php

declare(strict_types=1);

namespace App\CommissionTask\Service;

class Output
{
    public static function error(string $message)
    {
        echo "\033[31m{$message}\033[0m".PHP_EOL;
    }

    public static function warning(string $message)
    {
        echo "\033[33m{$message}\033[0m".PHP_EOL;
    }

    public static function info(string $message)
    {
        echo $message.PHP_EOL;
    }
}
