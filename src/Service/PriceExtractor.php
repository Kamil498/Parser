<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class PriceExtractor
{
    public function extract(string $html): float
    {
        $crawler = new Crawler($html);

        $price = $crawler->filterXPath(
            '//span[contains(@class,"ProductPrice-PriceValue")]/text()'
        )->text();


        $price = preg_replace(
            '/[^0-9,.]/',
            '',
            $price
        );


        return (float)str_replace(
            ',',
            '.',
            $price
        );
    }
}
