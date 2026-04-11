<?php

namespace App\Entity;

use App\Repository\QuartierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuartierRepository::class)]
class Quartier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quartiers')]
    private ?Commune $commune = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Avenue>
     */
    #[ORM\OneToMany(targetEntity: Avenue::class, mappedBy: 'quartier')]
    private Collection $avenues;

    public function __construct()
    {
        $this->avenues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune(?Commune $commune): static
    {
        $this->commune = $commune;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Avenue>
     */
    public function getAvenues(): Collection
    {
        return $this->avenues;
    }

    public function addAvenue(Avenue $avenue): static
    {
        if (!$this->avenues->contains($avenue)) {
            $this->avenues->add($avenue);
            $avenue->setQuartier($this);
        }

        return $this;
    }

    public function removeAvenue(Avenue $avenue): static
    {
        if ($this->avenues->removeElement($avenue)) {
            // set the owning side to null (unless already changed)
            if ($avenue->getQuartier() === $this) {
                $avenue->setQuartier(null);
            }
        }

        return $this;
    }
}
