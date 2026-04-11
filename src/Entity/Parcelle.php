<?php

namespace App\Entity;

use App\Repository\ParcelleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Document;

#[ORM\Entity(repositoryClass: ParcelleRepository::class)]
class Parcelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'parcelles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Victime $victime = null;

    #[ORM\ManyToOne(inversedBy: 'parcelles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Avenue $avenue = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(nullable: true)]
    private ?int $homme = null;

    #[ORM\Column(nullable: true)]
    private ?int $femme = null;

    #[ORM\Column(nullable: true)]
    private ?int $enfant = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observation = null;

    #[ORM\ManyToOne(inversedBy: 'parcelles')]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $datecreated = null;

    #[ORM\OneToMany(
        mappedBy: 'parcelle',
        targetEntity: Document::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->datecreated = new \DateTime(); // auto
    }

    public function __toString(): string
    {
        return $this->numero ?? 'Parcelle #' . $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVictime(): ?Victime
    {
        return $this->victime;
    }

    public function setVictime(?Victime $victime): static
    {
        $this->victime = $victime;
        return $this;
    }

    public function getAvenue(): ?Avenue
    {
        return $this->avenue;
    }

    public function setAvenue(?Avenue $avenue): static
    {
        $this->avenue = $avenue;
        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getHomme(): ?int
    {
        return $this->homme;
    }

    public function setHomme(?int $homme): static
    {
        $this->homme = $homme;
        return $this;
    }

    public function getFemme(): ?int
    {
        return $this->femme;
    }

    public function setFemme(?int $femme): static
    {
        $this->femme = $femme;
        return $this;
    }

    public function getEnfant(): ?int
    {
        return $this->enfant;
    }

    public function setEnfant(?int $enfant): static
    {
        $this->enfant = $enfant;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): static
    {
        $this->observation = $observation;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getDatecreated(): ?\DateTimeInterface
    {
        return $this->datecreated;
    }

    public function setDatecreated(\DateTimeInterface $datecreated): static
    {
        $this->datecreated = $datecreated;
        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setParcelle($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getParcelle() === $this) {
                $document->setParcelle(null);
            }
        }

        return $this;
    }
}