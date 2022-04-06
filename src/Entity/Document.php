<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

// TODO Implement security on this class
#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ApiResource(
    attributes: ["security" => "is_granted('ROLE_USER')"],
    normalizationContext: ['groups' => ['document:output']],
    denormalizationContext: ['groups' => ['document:input']],
)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['document:output'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['document:output', 'document:input'])]
    private $title;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['document:output', 'document:input'])]
    private $description;

    #[ORM\OneToMany(mappedBy: 'document', targetEntity: Blob::class, orphanRemoval: true)]
    #[Groups(['document:output'])]
    private $blobs;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['document:output'])]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['document:output'])]
    private $updatedAt;

    public function __construct()
    {
        $this->blobs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Blob>
     */
    public function getBlobs(): Collection
    {
        return $this->blobs;
    }

    public function addBlob(Blob $blob): self
    {
        if (!$this->blobs->contains($blob)) {
            $this->blobs[] = $blob;
            $blob->setDocument($this);
        }

        return $this;
    }

    public function removeBlob(Blob $blob): self
    {
        if ($this->blobs->removeElement($blob)) {
            // set the owning side to null (unless already changed)
            if ($blob->getDocument() === $this) {
                $blob->setDocument(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
