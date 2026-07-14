<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class ProductExtractor
{
    public function extract(string $html): array
    {
        $crawler = new Crawler($html);

        return [
            'tytul' => $this->getText(
                $crawler,
                'h1'
            ),

            'autor' => $this->author(
                $crawler,
            ),

            'wydawnictwo' => $this->getXPathText(
                $crawler,
                '//dt[contains(normalize-space(), "Wydawnictwo")]/following-sibling::dd[1]'
            ),

            'rok_wydania' => $this->getXPathInt(
                $crawler,
                '//dt[contains(normalize-space(), "Rok wydania")]/following-sibling::dd[1]'
            ),

            'cena' => $this->extractPrice(
                $crawler
            ),
        ];
    }

    private function getXPathInt(Crawler $crawler, string $xpath): ?int
    {
        $text = $this->getXPathText($crawler, $xpath);

        if ($text === null) {
            return null;
        }

        return (int) trim($text);
    }

    private function getXPathText(Crawler $crawler, string $xpath): ?string
    {
        $nodes = $crawler->filterXPath($xpath);

        if ($nodes->count() === 0) {
            return null;
        }

        return trim($nodes->first()->text());
    }


    private function getText(
        Crawler $crawler,
        string $selector
    ): ?string {

        if ($crawler->filter($selector)->count() === 0) {
            return null;
        }

        return trim(
            $crawler->filter($selector)->first()->text()
        );
    }


    private function extractPrice(
        Crawler $crawler
    ): ?string {

        $nodes = $crawler->filterXPath(
            '//span[contains(@class,"ProductPrice-PriceValue")]'
        );


        if ($nodes->count() === 0) {
            return null;
        }


        $price = $nodes->first()->text();


        $price = preg_replace(
            '/[^0-9,.]/',
            '',
            $price
        );


        if (!$price) {
            return null;
        }


        return str_replace(
            ',',
            '.',
            $price
        );

    }
    private function author(Crawler $crawler): ?string
    {
        $authors = $crawler
            ->filterXPath('//ul[contains(@class,"AuthorLink")]//a')
            ->each(fn (Crawler $node) => trim($node->text()));

        if (empty($authors)) {
            return null;
        }

        return implode(', ', $authors);
    }
}
