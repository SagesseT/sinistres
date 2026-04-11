<?php

namespace App\Entity;

use App\Repository\AvenueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvenueRepository::class)]
class Avenue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'avenues')]
    private ?Quartier $quartier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Parcelle>
     */
    #[ORM\OneToMany(targetEntity: Parcelle::class, mappedBy: 'avenue')]
    private Collection $parcelles;

    public function __construct()
    {
        $this->parcelles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuartier(): ?Quartier
    {
        return $this->quartier;
    }

    public function setQuartier(?Quartier $quartier): static
    {
        $this->quartier = $quartier;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Parcelle>
     */
    public function getParcelles(): Collection
    {
        return $this->parcelles;
    }

    public function addParcelle(Parcelle $parcelle): static
    {
        if (!$this->parcelles->contains($parcelle)) {
            $this->parcelles->add($parcelle);
            $parcelle->setAvenue($this);
        }

        return $this;
    }

    public function removeParcelle(Parcelle $parcelle): static
    {
        if ($this->parcelles->removeElement($parcelle)) {
            // set the owning side to null (unless already changed)
            if ($parcelle->getAvenue() === $this) {
                $parcelle->setAvenue(null);
            }
        }

        return $this;
    }
}
