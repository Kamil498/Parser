<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class TaniaExtract
{

    public function extract(string $html): array{
        $crawler = new Crawler($html);
        return [

            'cena'=>$this->extractCena(
                $crawler),
            'tytul' => $this->extractTytul(
                $crawler,
                'h1'),

            'autor'=>$this->extractAutor(
                $crawler),

            'wydawnictwo'=>$this->extractWydawnictwo(
                $crawler),

            'rok'=>$this->extractRok(
                $crawler),
        ];
    }

    private function extractCena(Crawler $crawler): string
    {
        $nodes=$crawler->filterXPath(
            '//span[@class="product-price updateable_price"]/text()'
        );
        return trim(
            $nodes->first()->text()
        );
    }

    private function extractTytul(Crawler $crawler, string $selector): ?string
    {
        if ($crawler->filter($selector)->count() === 0) {
            return null;
        }

        return trim(
            $crawler->filter($selector)->first()->text()
        );
    }

    private function extractAutor(Crawler $crawler): ?string
    {
        $nodes=$crawler->filterXPath(
            '//a[@href, "/autor/"]/text()'
        );

        return trim(
            $nodes->first()->text()
        );
    }

    private function extractWydawnictwo(Crawler $crawler): ?string{
        $nodes=$crawler->filterXPath(
            '//a[@href, "/wydawnictwo/"]/text()'
        );

        return trim(
            $nodes->first()->text()
        );
    }

    private function extractRok(Crawler $crawler): ?string{
        $nodes=$crawler->filterXPath(
        '//tr[td[contains(text(), "Rok wydania:")]]/td[2]'
        );
        if ($nodes->count() === 0) {
            return null;
        }

    }
}
