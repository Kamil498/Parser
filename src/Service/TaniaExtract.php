<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class TaniaExtract
{

    public function extract(string $html): array{
        $crawler = new Crawler($html);

        $crawler->addHtmlContent($html, 'UTF-8');
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

            'rok_wydania'=>$this->extractRok(
                $crawler),
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
        dump($nodes->count());

        if ($nodes->count() === 0) {
            return null;
        }

        return trim($nodes->first()->text());
    }

    private function extractRok(Crawler $crawler): ?string{
        $nodes=$crawler->filterXPath(
        '//tr[td[contains(text(), "Rok wydania:")]]/td[2]'
        );
        if ($nodes->count() === 0) {
            return null;
        }

        return trim(
            $nodes->first()->text()
        );

    }
}
