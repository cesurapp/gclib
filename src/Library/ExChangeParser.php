<?php

namespace App\Library;

class ExChangeParser
{
    /**
     * European Central Bank XML to Array Parser
     *
     * @param string $xmlUrl
     *
     * @return array
     */
    public static function ecbBank(string $xmlUrl = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml'): array
    {
        $xml = simplexml_load_string(file_get_contents($xmlUrl));
        $array = [];

        foreach ($xml->Cube->Cube as $date) {
            $time = (string)$date->attributes()->time;

            $array[$time] = [
                'date' => $time,
                'base' => 'EUR',
                'rates' => []
            ];

            foreach ($date->Cube as $currency) {
                $attr = $currency->attributes();
                $array[$time]['rates'][(string)$attr->currency] = (string)$attr->rate;
            }
        }

        // Array Response
        return $array;
    }

    /**
     * Central Bank of Turkey XML to Array Parser
     *
     * @param string $xmlUrl
     *
     * @return array
     */
    public static function tcmbBank(string $xmlUrl = 'http://www.tcmb.gov.tr/kurlar/today.xml'): array
    {
        $xml = (array)simplexml_load_string(file_get_contents($xmlUrl));

        $array[$xml['@attributes']['Tarih']] = [
            'date' => $xml['@attributes']['Tarih'],
            'base' => 'TRY',
            'rates' => []
        ];
        foreach ($xml['Currency'] as $currency) {
            $currency = (array)$currency;
            $array[$xml['@attributes']['Tarih']]['rates'][$currency['@attributes']['Kod']] = (string)$currency['BanknoteSelling'] ?: (string)$currency['ForexSelling'];
        }

        return $array;
    }

    /**
     * Base Currency Converter
     *
     * @param string $base
     * @param array $rates
     *
     * @return array
     */
    public static function baseConverter(string $base, array $rates): array
    {
        $base = strtoupper($base);

        if (is_array($rates)) {
            foreach ($rates as $date => $items) {
                // Break Same Currency
                if ($base === $items['base'] || !isset($items['rates'][$base])) {
                    break;
                }

                // Add Default Currency
                if (!isset($items['rates'][$items['base']])) {
                    $items['rates'][$items['base']] = 1;
                }

                // Calculate Base
                $baseRate = $items['rates'][$base];
                foreach ($items['rates'] as $unit => $rate) {
                    $rates[$date]['rates'][$unit] = $rate ? $rate / $baseRate : '';
                }
                unset($rates[$date]['rates'][$base]);
                $rates[$date]['base'] = $base;
            }
        }

        return $rates;
    }
}