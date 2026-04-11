<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Fichier optionnel
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichier = null;

    // Le type de document est lié à TypeDocument
    #[ORM\ManyToOne(inversedBy: 'documents', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Parcelle $parcelle = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: true)]
    private ?\App\Entity\TypeDocument $typedocument = null;

    // Date d'upload automatique
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $dateUpload = null;

    public function __construct()
    {
        // Initialise automatiquement la date d'upload si besoin
        $this->dateUpload = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(?string $fichier): static
    {
        $this->fichier = $fichier;
        return $this;
    }

    public function getParcelle(): ?Parcelle
    {
        return $this->parcelle;
    }

    public function setParcelle(?Parcelle $parcelle): static
    {
        $this->parcelle = $parcelle;
        return $this;
    }

    public function getTypedocument(): ?\App\Entity\TypeDocument
    {
        return $this->typedocument;
    }

    public function setTypedocument(?\App\Entity\TypeDocument $typedocument): static
    {
        $this->typedocument = $typedocument;
        return $this;
    }

    public function getDateUpload(): ?\DateTime
    {
        return $this->dateUpload;
    }

    public function setDateUpload(?\DateTime $dateUpload = null): static
    {
        // Si aucune date fournie, mettre la date actuelle
        $this->dateUpload = $dateUpload ?? new \DateTime();
        return $this;
    }
}