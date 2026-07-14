<?php

namespace App\Entity;

use App\Repository\ProductBookRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductBookRepository::class)]
class ProductBook
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tytul = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $autor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $wydawnictwo = null;

    #[ORM\Column(name: 'rok_wydania', type: Types::SMALLINT, nullable: true)]
    private ?int $rokWydania = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $cena = null;

    #[ORM\Column(length: 500)]
    private ?string $url = null;

    #[ORM\Column(length: 50)]
    private ?string $shop = null;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getTytul(): ?string
    {
        return $this->tytul;
    }

    public function setTytul(string $tytul): static
    {
        $this->tytul = $tytul;

        return $this;
    }


    public function getAutor(): ?string
    {
        return $this->autor;
    }

    public function setAutor(?string $autor): static
    {
        $this->autor = $autor;

        return $this;
    }


    public function getWydawnictwo(): ?string
    {
        return $this->wydawnictwo;
    }

    public function setWydawnictwo(?string $wydawnictwo): static
    {
        $this->wydawnictwo = $wydawnictwo;

        return $this;
    }


    public function getRokWydania(): ?int
    {
        return $this->rokWydania;
    }

    public function setRokWydania(?int $rokWydania): static
    {
        $this->rokWydania = $rokWydania;

        return $this;
    }


    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }


    public function getCena(): ?string
    {
        return $this->cena;
    }

    public function setCena(?string $cena): static
    {
        $this->cena = $cena;

        return $this;
    }


    public function getUrl(): ?string
    {
        return $this->url;
    }
    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getShop(): ?string
    {
        return $this->shop;
    }
    public function setShop(?string $shop): static
    {
        $this->shop = $shop;

        return $this;
    }
}
