<?php

declare(strict_types=1);

namespace App\CommissionTask;

use Aimeos\Map;
use Exception;

class AppConfig
{
    /** @var AppConfig|null */
    private static $instance;

    /** @var Map */
    private $config;

    private function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->config = Map::from(require __DIR__.'/../config/app.php');
    }

    public static function getInstance(): AppConfig
    {
        return self::$instance ?? new static();
    }

    public function get(string $key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }
}
