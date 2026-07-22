<?php

namespace App\Enum;

enum Shop: String{
    case EMPIK='Empik';
    case BONITO = 'Bonito';
    case TANIAKSIAZKA = 'tania ksiazka';
    case BOOKLAND = 'Bookland';


public static function fromUrl(string $url): self
{
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    $host = preg_replace('/^www\./', '', $host);

    return match ($host) {
        'empik.com' => self::EMPIK,
        'bonito.pl' => self::BONITO,
        'taniaksiazka.pl' => self::TANIAKSIAZKA,
        'bookland.com.pl' => self::BOOKLAND,
        default => throw new \InvalidArgumentException(
            'Nieobsługiwany sklep: '.$host
        ),
    };
}
}
