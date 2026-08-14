<?php

namespace App\Support;

use NumberToWords\NumberToWords;

class MoneyToWordsConverter
{
    public function __construct(
        private NumberToWords $numberToWords
    ) {}

    public function convert(string|float $amount, string $currency): string
    {
        $currencyTransformer = $this->numberToWords->getCurrencyTransformer('pl');

        return $currencyTransformer->toWords(
            (int) round((float) $amount * 100),
            $currency
        );
    }

    public function convertPln(string|float $amount): string
    {
        return $this->convert($amount, 'PLN');
    }
}
