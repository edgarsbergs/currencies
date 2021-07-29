<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CurrencyRepository::class)
 * @ORM\Table(indexes={
 *     @Index(name="slug_idx", columns={"slug"})
 * })
 */
class Currency
{
    /**
     * @ORM\Column(type="bigint", options={"default":"nextval('currency_id_seq')"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=CurrencyRate::class, mappedBy="currency_id", orphanRemoval=true)
     * @ORM\OrderBy({"timestamp" = "DESC"})
     */
    private $currencyRates;

    public function __construct()
    {
        $this->currencyRates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|CurrencyRate[]
     */
    public function getCurrencyRates(): Collection
    {
        return $this->currencyRates;
    }

    public function addCurrencyRate(CurrencyRate $currencyRate): self
    {
        if (!$this->currencyRates->contains($currencyRate)) {
            $this->currencyRates[] = $currencyRate;
            $currencyRate->setCurrencyId($this);
        }

        return $this;
    }

    public function removeCurrencyRate(CurrencyRate $currencyRate): self
    {
        if ($this->currencyRates->removeElement($currencyRate)) {
            // set the owning side to null (unless already changed)
            if ($currencyRate->getCurrencyId() === $this) {
                $currencyRate->setCurrencyId(null);
            }
        }

        return $this;
    }
}
