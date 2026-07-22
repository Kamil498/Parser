<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class TaniaExtract
{
    public function extract(string $html): array
    {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $crawler = new Crawler();

        $crawler->addHtmlContent($html, 'UTF-8');

        return [
            'cena' => $this->extractCena($crawler),
            'tytul' => $this->extractTytul($crawler),
            'autor' => $this->extractAutor($crawler),
            'wydawnictwo' => $this->extractWydawnictwo($crawler),
            'rok_wydania' => $this->extractRok($crawler),
        ];
    }

    private function extractCena(Crawler $crawler): ?string
    {
        $zl = $crawler->filter('#updateable_price-zl');
        $gr = $crawler->filter('#updateable_price-gr');

        if ($zl->count() === 0 || $gr->count() === 0) {
            return null;
        }

        return $zl->text() . '.' . $gr->text();
    }

    private function extractTytul(Crawler $crawler): ?string
    {
        $nodes = $crawler->filterXPath(
            '//h1[contains(@class, "product-info-title")]'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        return trim($nodes->first()->text());
    }

    private function extractAutor(Crawler $crawler): ?string
    {
        $nodes = $crawler->filterXPath(
            '//li[starts-with(normalize-space(), "Autor")]/strong/a'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        return trim($nodes->first()->text());
    }

    private function extractWydawnictwo(Crawler $crawler): ?string
    {
        $nodes = $crawler->filterXPath(
            '//tr[td[1][normalize-space()="Wydawnictwo:"]]/td[2]/a'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        return trim($nodes->first()->text());
    }

    private function extractRok(Crawler $crawler): ?string
    {
        $nodes = $crawler->filterXPath(
            '//tr[td[contains(text(), "Rok wydania:")]]/td[2]'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        return trim($nodes->first()->text());
    }
}
