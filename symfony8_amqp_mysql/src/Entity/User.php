<?php

namespace App\Entity;

use \Deprecated;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use DateTimeImmutable;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'Email Address is already taken.')]
class User implements TwoFactorInterface, UserInterface, PasswordAuthenticatedUserInterface 
{


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id;

    #[ORM\Column(length: 20, nullable: true)]    
    private ?string $lastname;

    #[ORM\Column(length: 20, nullable: true)]
     #[Groups(['user:read'])]
    private ?string $firstname;

    #[ORM\Column(
        length: 180, 
        unique: true, 
        nullable: false, 
        options: ["collation" => "utf8mb4_bin"]
    )]
    #[Groups(['user:read'])] // Add this group    
    private ?string $username;

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(length: 180, unique: true, nullable: false)]
    private ?string $email;

    #[ORM\Column(type: 'json')]
    #[Groups(['user:read'])] // Add this group    
    private array $roles = [];

    #[ORM\Column(length: 15, nullable: true)]    
    private ?string $mobile;
    
    #[ORM\Column(length: 3, options: ["default" => 1])]
    #[Groups(['user:read'])] // Add this group    
    private ?int $isactivated;
    
    #[ORM\Column(length: 3, options: ["default" => 0])]
    #[Groups(['user:read'])] // Add this group    
    private ?int $isblocked;

    #[ORM\Column(type: 'text',nullable: true)]    
    private ?string $secretkey;

    #[ORM\Column(length: 6, options: ["default" => 0])]
    private ?int $mailtoken;

    #[ORM\Column(type: 'text',nullable: true)]    
    #[Groups(['user:read'])] // Add this group    
    private ?string $qrcodeurl;

    #[ORM\Column(length: 255, options: ["default" => "pix.png"])]
    #[Groups(['user:read'])] // Add this group    
    
    private ?string $userpic = 'pix.png'; 


    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $totpSecret = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        // return $this->userRoles->map(fn($role) => $role->getName())->toArray();
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSecretkey(): ?string
    {
        return $this->secretkey;
    }

    public function setSecretkey(?string $secretkey): self
    {
        $this->secretkey = $secretkey;
        return $this;
    }


    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;
        return $this;
    }

    public function getMailtoken(): ?int
    {        
        return $this->mailtoken;
    }

    public function setMailtoken(?int $mailtoken): self
    {
        $this->mailtoken = $mailtoken;
        return $this;
    }

    public function getQrcodeurl(): ?string
    {
        return $this->qrcodeurl;
    }

    public function setQrcodeurl(?string $qrcodeurl): self
    {
        $this->qrcodeurl = $qrcodeurl;
        return $this;
    }

    public function getIsactivated(): ?int
    {
        return $this->isactivated;
    }

    public function setIsactivated(?int $isactivated): self
    {
        $this->isactivated = $isactivated;
        return $this;
    }

    public function getUserpic(): ?string
    {
        return $this->userpic;
    }

    public function setUserpic(?string $userpic): self
    {
        $this->userpic = $userpic;
        return $this;
    }

    public function getIsblocked(): ?int
    {
        return $this->isblocked;
    }

    public function setIsblocked(?int $isblocked): self
    {
        $this->isblocked = $isblocked;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[Deprecated] 
    public function eraseCredentials(): void
    {
        // $this->plainPassword = null;
        // $this->setUsername = null;
        $this->setPassword = null;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->totpSecret ? true : false;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->username;
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        // You could persist the other configuration options in the user entity to make it individual per user.
        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA1, 20, 8);
    }    

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): void
    {
        $this->totpSecret = $totpSecret;
    }

    public function isTwoFactorAuthEnabled(): bool
    {
        // Return true if the user has a TOTP secret set
        return $this->totpSecret !== null;
    }

}