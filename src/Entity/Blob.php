<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CreateBlobAction;
use App\Repository\BlobRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: BlobRepository::class)]
#[ORM\Table(name: '`blob`')]
#[Vich\Uploadable]
#[ApiResource(
    iri: 'http://schema.org/MediaObject',
    normalizationContext: ['groups' => ['blob:output']],
    itemOperations: [
        'get' => [
            'path' => '/blobs/{fileName}',
        ],
    ],
    collectionOperations: [
        'get',
        'post' => [
            'controller' => CreateBlobAction::class,
            'deserialize' => false,
            'validation_groups' => ['Default', 'blob_create'],
            'openapi_context' => [
                'requestBody' => [
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
)]
class Blob
{
    /*#[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;*/

    #[ApiProperty(iri: 'http://schema.org/contentUrl')]
    #[Groups('blob:output')]
    private ?string $contentUrl = null;

    #[Vich\UploadableField(
        mapping: 'blob',
        fileNameProperty: 'fileName',
        size: 'size',
        mimeType: 'mimeType',
        originalName: 'originalName',
        dimensions: 'dimensions',
    )]
    private $file;

    #[ORM\Id]
    #[ORM\Column(type: 'string', nullable: true)]
    #[ApiProperty(identifier: true)]
    #[Groups('blob:output')]
    private ?string $fileName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups('blob:output')]
    private ?int $size = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups('blob:output')]
    private ?string $mimeType = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups('blob:output')]
    private ?string $originalName;

    #[ORM\Column(type: 'array', nullable: true)]
    #[Groups('blob:output')]
    private $dimensions;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups('blob:output')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups('blob:output')]
    private $updatedAt;

    /*public function getId(): ?string
    {
        return $this->fileName;
    }*/

    public function getContentUrl(): ?string
    {
        return $this->contentUrl;
    }

    public function setContentUrl(?string $contentUrl = null): self
    {
        $this->contentUrl = $contentUrl;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file = null): self
    {
        $this->file = $file;

        if (null !== $file) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName = null): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size = null): self
    {
        $this->size = $size;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType = null): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName = null): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }

    public function setDimensions(?array $dimensions = null): self
    {
        $this->dimensions = $dimensions;

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
