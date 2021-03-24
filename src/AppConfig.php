<?php

declare(strict_types=1);

namespace App\CommissionTask;

use Aimeos\Map;

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

    private function init(): void
    {
        $this->config = Map::from(require __DIR__ . '/../config/app.php');

    }

    public static function getInstance(): AppConfig
    {
        return self::$instance ?: new self();
    }

    public function get(string $key, $default = null)
    {
        return $this->config->get($key, $default);
    }
}