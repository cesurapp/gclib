<?php

namespace App\Controller;

use App\Library\Cache;
use App\Library\Controller;
use App\Library\ExChangeParser;
use Symfony\Contracts\Cache\ItemInterface;

class ExRateController extends Controller
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var array
     */
    private $storage = [];

    /**
     * @var array
     */
    private $storageInvalid = [];

    public function __construct()
    {
        $this->cache = new Cache();
    }

    /**
     * Get Latest Rates
     *
     * @param string $bank
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function latestRate(string $bank): void
    {
        $this->loadRates($bank);

        // Base Currency
        if (!$base = $this->request->get['base']) {
            switch ($bank) {
                case 'tcmb':
                    $base = 'TRY';
                    break;
                case 'ecb':
                    $base = 'EUR';
                    break;
            }
        }

        // Create Latest Rates
        $firstKey = array_key_first($this->storage[$bank]);
        $rates = ExChangeParser::baseConverter($base, [$firstKey => $this->storage[$bank][$firstKey]]);

        // Symbol
        if (isset($this->request->get['symbol'])) {
            $symbols = array_flip(explode(',', $this->request->get['symbol']));
            $rates[$firstKey]['rates'] = array_intersect_key($rates[$firstKey]['rates'], $symbols);
        }

        // Response
        $this->jsonResponse($rates);
    }

    /**
     * Custom Rates
     *
     * @param string $bank
     * @param string $date
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function customRate(string $bank, string $date): void
    {
        $this->loadRates($bank);

        // Base Currency
        if (!$base = $this->request->get['base']) {
            switch ($bank) {
                case 'tcmb':
                    $base = 'TRY';
                    break;
                case 'ecb':
                    $base = 'EUR';
                    break;
            }
        }

        if (isset($this->storage[$bank][$date])) {
            $rates = ExChangeParser::baseConverter($base, [$date => $this->storage[$bank][$date]]);

            // Symbol
            if (isset($this->request->get['symbol'])) {
                $symbols = array_flip(explode(',', $this->request->get['symbol']));
                $rates[$date]['rates'] = array_intersect_key($rates[$date]['rates'], $symbols);
            }

            $this->jsonResponse($rates);
        } else {
            $this->errorResponse();
        }
    }

    /**
     * Custom Rate Ranges
     *
     * @param string $bank
     * @param string $dateStart
     * @param string $dateEnd
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function customRateRange(string $bank, string $dateStart, string $dateEnd): void
    {
        $this->loadRates($bank);

        // Base Currency
        if (!$base = $this->request->get['base']) {
            switch ($bank) {
                case 'tcmb':
                    $base = 'TRY';
                    break;
                case 'ecb':
                    $base = 'EUR';
                    break;
            }
        }

        $rates = $this->storage[$bank];
        $dateStart = str_replace('-', '', $dateStart);
        $dateEnd = str_replace('-', '', $dateEnd);
        $rates = array_filter($rates, static function ($rDate) use ($dateStart, $dateEnd) {
            $rDate = str_replace('-', '', $rDate);
            if ($rDate >= $dateStart && $rDate <= $dateEnd) {
                return true;
            }
            return false;
        }, ARRAY_FILTER_USE_KEY);

        if (count($rates) > 0) {
            $rates = ExChangeParser::baseConverter($base, $rates);

            $this->jsonResponse($rates);
            return;
        }

        $this->errorResponse();
    }

    /**
     * Load Rates for Central Bank
     *
     * @param string $bank
     * @param int $cacheTimeout
     * @return mixed
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function loadRates(string $bank, int $cacheTimeout = 60)
    {
        // Invalidate Variable Cache
        if (isset($this->storageInvalid[$bank]) && $this->storageInvalid[$bank] > time()) {
            $this->storage[$bank];
        }

        if (!isset($this->storage[$bank])) {
            try {
                $this->storage[$bank] = $this->cache->get($bank, static function (ItemInterface $item) use ($bank, $cacheTimeout) {
                    $item->expiresAfter($cacheTimeout * 60);

                    if ($bank === 'tcmb') {
                        return ExChangeParser::tcmbBank();
                    }

                    return ExChangeParser::ecbBank();
                });

                // Invalidate Time
                $this->storageInvalid[$bank] = time() + (60 * 60);
            } catch (\Exception $e) {
                $this->storage[$bank] = false;
            }
        }

        return $this->storage[$bank];
    }
}