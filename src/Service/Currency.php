<?php

declare(strict_types=1);

namespace App\CommissionTask\Service;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedCurrencyException;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Context\CustomContext;
use Brick\Money\CurrencyConverter;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\ExchangeRateProvider\BaseCurrencyProvider;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Brick\Money\Money;

class Currency
{
    const EUR = 'EUR';
    const USD = 'USD';
    const JPY = 'JPY';

    /** @var string */
    private $code;

    public function __construct(string $code)
    {
        $this->checkCurrencySupported($code);

        $this->code = $code;
    }

    /**
     * @param $amount
     * @param $from
     * @param $to
     * @return BigDecimal
     *
     * @throws CurrencyConversionException
     * @throws UnknownCurrencyException
     */
    public static function convert($amount, $from, $to): BigDecimal
    {
        if ($from === $to) {
            return BigDecimal::of($amount);
        }

        $config = AppConfig::getInstance();
        $scale = $config->get('scale');
        $mainCurrencyCode = $config->get('currencies.main');
        $rates = $config->get('currencies.exchange_rates');
        $provider = new ConfigurableProvider();

        foreach ($rates as $currencyCode => $rate) {
            $provider->setExchangeRate($mainCurrencyCode, $currencyCode, $rate);
        }

        $provider = new BaseCurrencyProvider($provider, 'EUR');
        $converter = new CurrencyConverter($provider, new CustomContext($scale));

        return $converter->convert(Money::of($amount, $from), $to, RoundingMode::UP)->getAmount();
    }

    public function getCurrencyCode(): string
    {
        return $this->code;
    }

    public function setCurrencyCode(string $code)
    {
        $this->checkCurrencySupported($code);

        $this->code = $code;
    }

    private function checkCurrencySupported(string $code)
    {
        if (!in_array($code, AppConfig::getInstance()->get('currencies.supported'), true)) {
            throw new UnsupportedCurrencyException($code);
        }
    }
}