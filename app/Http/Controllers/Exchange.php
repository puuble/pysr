<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Money;

class Exchange
{
    const EXCHANGE_RATES_URL = "https://developers.paysera.com/tasks/api/currency-exchange-rates";

    const DEFAULT_CURRENCY = "EUR";

    protected $rates;

    protected $exchange;


    /**
     * Start to get Content while initialize
     * 
     */
    public function __construct()
    {
        $this->setExchangeRates();
    }


    /**
     * Basic set content with URL for exchanges
     * 
     * @return array|null
     */
    public function setExchangeRates()
    {
        $resource = file_get_contents(self::EXCHANGE_RATES_URL);
        if ($resource) {
            $resource = json_decode($resource, 1);
        } else {
            return null;
        }

        $this->rates = $resource;
    }

    /**
     * Get Exchange Pairs with array
     * 
     * @param $currency string|false
     * 
     * @return array
     */
    public function getExchangeRates($currency = false)
    {
        return  $currency ? $this->rates['rates'][$currency] : $this->rates['rates'];
    }

    /**
     * Get Exchange from URL
     * 
     * @return float $exchange
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * Set Exchange with Base Money  if not in EURO we get the rates on URL
     * @param Money $base
     * 
     * @return static
     */
    public function setExchange(Money $base)
    {
        $this->exchange = $base;
        $currency = $base->getCurrency();

        if ($currency != self::DEFAULT_CURRENCY) {
            $pair =  $this->getExchangeRates($currency);
            if (is_null($pair)) {
                throw new \Exception("Check the exchange URL", 1);
            }
            $this->exchange = $base->exchange($pair);
        }
        return $this;
    }
}
