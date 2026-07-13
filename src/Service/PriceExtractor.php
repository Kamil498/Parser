<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class PriceExtractor
{
    public function extract(string $html): float
    {
        $crawler = new Crawler($html);

        $nodes = $crawler->filterXPath(
            '//span[contains(@class,"ProductPrice-PriceValue")]'
        );
        dump($crawler->filter('span')->count());

        if ($nodes->count() === 0) {
            throw new \RuntimeException(
                'Price not found'
            );
        }

        $price = $nodes->first()->text();

        $price = preg_replace(
            '/[^0-9,.]/',
            '',
            $price
        );

        if ($price === '') {
            throw new \RuntimeException(
                'Price value empty'
            );
        }

        return (float) str_replace(',', '.', $price);
    }
}
