<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ApiResource]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks] 
class Sale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ["default" => 0])]
    private ?string $salesamount;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private \DateTimeImmutable $salesdate;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getSalesmount(): ?string
    {
        return $this->salesamount;
    }

    public function setSalesamount(string $salesamount): static
    {
        $this->salesamount = $salesamount;

        return $this;
    }

    public function getSalesdate(): ?\DateTimeImmutable
    {
        return $this->salesdate;
    }

    public function setSalesdate(\DateTimeImmutable $salesdate): self
    {
        $this->salesdate = $salesdate;
        return $this;
    }

}
