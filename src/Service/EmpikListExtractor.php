<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class EmpikListExtractor
{

    public function extract(string $html): array
    {
        $crawler= new Crawler($html);

        $links=[];

        $crawler->filter('a[data-ta="product-tile-link"]')->each(
            function (Crawler $crawler) use (&$links) {

                $href=$crawler->attr('href');

                if(!$href){
                    return null;
                }

                if(!str_starts_with($href,'http')){
                    $href = 'https://www.empik.com' . $href;
                }

                $links[]=strtok($href, '?');
            }
        );

        return array_values(array_unique($links));
    }
}
