<?php

namespace App\Entity;

use App\Repository\CurrencyRateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CurrencyRateRepository::class)
 */
class CurrencyRate
{
    /**
     * @ORM\Column(type="bigint", options={"default":"nextval('currency_rate_id_seq')"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Currency::class, inversedBy="currencyRates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency_id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $value;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrencyId(): ?Currency
    {
        return $this->currency_id;
    }

    public function setCurrencyId(?Currency $currency_id): self
    {
        $this->currency_id = $currency_id;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }
}
