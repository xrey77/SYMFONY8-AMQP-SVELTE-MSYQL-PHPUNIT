<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    // private ?Category $category = null; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // #[Groups(['product:read'])] 
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    // #[Groups(['product:read'])]     
    private ?string $descriptions = null;

    // Fixed: 'length' is for strings; use 'type: integer' or leave blank for ints
    #[ORM\Column(options: ["default" => 0])]
    // #[Groups(['product:read'])]     
    private ?int $qty = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $unit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ["default" => 0])]
    // #[Groups(['product:read'])]     
    private ?string $costprice = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ["default" => 0])]
    private ?string $sellprice = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ["default" => 0])]
    private ?string $saleprice = '0.00';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $productpicture = null;

    #[ORM\Column(options: ["default" => 0])]
    private ?int $alertstocks = 0;

    #[ORM\Column(options: ["default" => 0])]
    private ?int $criticalstocks = 0;

    #[ORM\Column]    
    private ?\DateTimeImmutable $createdAt = null;
    
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    // --- Getters and Setters ---

    public function getId(): ?int { return $this->id; }

    public function getDescriptions(): ?string { return $this->descriptions; }
    public function setDescriptions(string $descriptions): static { $this->descriptions = $descriptions; return $this; }

    public function getQty(): ?int { return $this->qty; }
    public function setQty(int $qty): static { $this->qty = $qty; return $this; }

    public function getUnit(): ?string { return $this->unit; }
    public function setUnit(?string $unit): static { $this->unit = $unit; return $this; }

    public function getCostprice(): ?string { return $this->costprice; }
    public function setCostprice(string $costprice): static { $this->costprice = $costprice; return $this; }

    public function getSellprice(): ?string { return $this->sellprice; }
    public function setSellprice(string $sellprice): static { $this->sellprice = $sellprice; return $this; }

    public function getSaleprice(): ?string { return $this->saleprice; }
    public function setSaleprice(string $saleprice): static { $this->saleprice = $saleprice; return $this; }

    public function getProductpicture(): ?string { return $this->productpicture; }
    public function setProductpicture(?string $productpicture): static { $this->productpicture = $productpicture; return $this; }

    public function getAlertstocks(): ?int { return $this->alertstocks; }
    public function setAlertstocks(int $alertstocks): static { $this->alertstocks = $alertstocks; return $this; }

    public function getCriticalstocks(): ?int { return $this->criticalstocks; }
    public function setCriticalstocks(int $criticalstocks): static { $this->criticalstocks = $criticalstocks; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): static { $this->category = $category; return $this; }

    // --- Lifecycle Callbacks ---

    #[ORM\PrePersist]
    public function setInitialTimestamps(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate] // Changed from PrePersist to PreUpdate for the update field
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
