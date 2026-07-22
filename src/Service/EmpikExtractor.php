<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class EmpikExtractor
{
    public function extract(string $html): array
    {
        $crawler = new Crawler($html);

        return [
            'cena' => $this->cena($crawler),

            'rok_wydania' => $this->rok($crawler),

            'tytul' => $this->tytul(
                $crawler,
                'h1'
            ),

            'autor' => $this->autor($crawler),

            'wydawnictwo' => $this->wydawnictwo($crawler),
        ];
    }


    private function cena(Crawler $crawler): ?float
    {
        $nodes = $crawler->filterXPath(
            '//span[contains(@data-ta, "price")]'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        $price = $this->normalizeText(
            $nodes->first()->text()
        );

        $price = preg_replace(
            '/[^0-9,.]/',
            '',
            $price
        );

        if ($price === '') {
            return null;
        }

        return (float) str_replace(',', '.', $price);
    }


    private function rok(Crawler $crawler): ?int
    {
        $nodes = $crawler->filterXPath(
            '//div[div[normalize-space()="Data premiery:"]]/div[@data-ta="attribute-value"]/div'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        $text = $this->normalizeText(
            $nodes->first()->text()
        );

        if (preg_match('/\b(19|20)\d{2}\b/', $text, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }


    private function tytul(Crawler $crawler, string $selector): ?string
    {
        if ($crawler->filter($selector)->count() === 0) {
            return null;
        }

        return $this->normalizeText(
            $crawler->filter($selector)->first()->text()
        );
    }


    private function autor(Crawler $crawler): ?string
    {
        $nodes = $crawler->filterXPath(
            '//tr[th[normalize-space()="Autor:"]]/td//a'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        return $this->normalizeText(
            $nodes->first()->text()
        );
    }


    private function wydawnictwo(Crawler $crawler): ?string
    {
        $nodes = $crawler->filterXPath(
            '//tr[th[normalize-space()="Wydawnictwo:"]]/td//a'
        );

        if ($nodes->count() === 0) {
            return null;
        }

        return $this->normalizeText(
            $nodes->first()->text()
        );
    }


    private function normalizeText(string $text): string
    {
        $text = html_entity_decode(
            $text,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        // zamiana twardych spacji &nbsp;
        $text = str_replace(
            "\xc2\xa0",
            ' ',
            $text
        );

        return trim($text);
    }
}
