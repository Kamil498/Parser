<?php

namespace App\Service;

use Symfony\Component\Panther\Client;

class PageDownloader
{
    public function download(string $url): string
    {
        $client = Client::createChromeClient();

        try {
            $client->request('GET', $url);

            sleep(5);

            $html = $client->getPageSource();

            $encoding = mb_detect_encoding(
                $html,
                ['UTF-8', 'ISO-8859-1', 'Windows-1250'],
                true
            );

            if ($encoding && $encoding !== 'UTF-8') {
                $html = mb_convert_encoding(
                    $html,
                    'UTF-8',
                    $encoding
                );
            }

            return $html;

        } finally {
            $client->quit();
        }
    }
}
