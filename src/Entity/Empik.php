<?php

namespace App\Entity;

use App\Repository\EmpikRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: EmpikRepository::class)]
class Empik
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $ean = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tytul = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $autor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $wydawnictwo = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $rok_wydania = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $cena = null;

    #[ORM\Column(length: 500)]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    private ?string $shop = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEan(): ?string
    {
        return $this->ean;
    }

    public function setEan(?string $ean): static
    {
        $this->ean = $ean;

        return $this;
    }

    public function getTytul(): ?string
    {
        return $this->tytul;
    }

    public function setTytul(?string $tytul): static
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
        return $this->rok_wydania;
    }

    public function setRokWydania(?int $rok_wydania): static
    {
        $this->rok_wydania = $rok_wydania;

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

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getShop(): ?string
    {
        return $this->shop;
    }

    public function setShop(string $shop): static
    {
        $this->shop = $shop;

        return $this;
    }
    public function __construct()
    {
        $this->priceHistory = new ArrayCollection();
    }

}
