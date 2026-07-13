<?php

namespace App\Enum;

enum Shop: String{
    case EMPIK='Empik';
    case ALLEGRO = 'Allegro';
    case BONITO = 'Bonito';
    case TANTIS = 'Tantis';
    case SWIAT_KSIAZKI = 'Świat Książki';
    case AMAZON = 'Amazon';
    case BOOKLAND = 'Bookland';


public static function fromUrl(string $url): self
{
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    $host = preg_replace('/^www\./', '', $host);

    return match ($host) {
        'empik.com' => self::EMPIK,
        'allegro.pl' => self::ALLEGRO,
        'bonito.pl' => self::BONITO,
        'tantis.pl' => self::TANTIS,
        'swiatksiazki.pl' => self::SWIAT_KSIAZKI,
        'amazon.com' => self::AMAZON,
        'bookland.com.pl' => self::BOOKLAND,
        default => throw new \InvalidArgumentException(
            'Nieobsługiwany sklep: '.$host
        ),
    };
}
}
