<?php

namespace App\Service;

class PageDownloader
{
    public function download(string $url): string
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0',
            CURLOPT_TIMEOUT => 15,
        ]);

        $html = curl_exec($curl);

        if ($html === false) {
            throw new \RuntimeException(
                curl_error($curl)
            );
        }

        curl_close($curl);

        return $html;
    }
}
