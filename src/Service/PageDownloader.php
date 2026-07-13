<?php

namespace App\Service;

class PageDownloader
{
    public function download(string $url): string
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,

            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language: pl-PL,pl;q=0.9,en-US;q=0.8',
                'Cache-Control: no-cache',
                'Upgrade-Insecure-Requests: 1',
            ],

            CURLOPT_USERAGENT =>
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/138 Safari/537.36',
        ]);

        $html = curl_exec($curl);



        if ($html === false) {
            throw new \RuntimeException(curl_error($curl));
        }

        curl_close($curl);

        return $html;
    }
}
