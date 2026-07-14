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

            return $html;

        } finally {
            $client->quit();
        }
    }
}
