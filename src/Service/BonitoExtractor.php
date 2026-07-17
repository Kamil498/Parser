<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class BonitoExtractor
{
    public function extract(string $html): array
    {
        $crawler = new Crawler($html);

        return [
            'cena' => $this->extractPrice(
                $crawler),

            'tytul' => $this->extractTitle(
                $crawler,
                'h1'),

            'autor' => $this->autor(
                $crawler),

            'wydawnictwo' => $this->wydawnictwo(
                $crawler),

            'rok' => $this->rok(
                $crawler),

        ];
    }

    private function rok(Crawler $crawler): ?int
    {
        $nodes = $crawler->filterXPath(
            '//a[contains(@href, "/szukaj/")]'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        return (int) trim($nodes->first()->text());
    }

    private function wydawnictwo(Crawler $crawler): ?string
    {
        $nodes = $crawler->filterXPath(
            '//a[contains(@href, "/wydawnictwo/")]'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        return trim($nodes->first()->text());
    }

    private function autor(Crawler $crawler): ?string
    {
        $nodes = $crawler->filterXPath(
            '//a[contains(@href, "/autor/")]'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        return trim($nodes->first()->text());
    }

    private function extractTitle(Crawler $crawler, string $selector): ?string
    {
        if ($crawler->filter($selector)->count() === 0) {
            return null;
        }

        return trim(
            $crawler->filter($selector)->first()->text()
        );
    }

    private function extractPrice(Crawler $crawler): ?string
    {
        $nodes = $crawler->filterXPath(
            '//div[@itemprop="price"]'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        return $nodes->first()->attr('content');
    }
}
