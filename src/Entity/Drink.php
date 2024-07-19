<?php

namespace App\Entity;

use App\Repository\DrinkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;

#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to views the drinks'),
        new Get(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to view a drink'),
        new Post(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to create a drink'),
        new Put(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to edit this drink'),
        new Patch(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to edit this drink'),
        new Delete(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to delete this drink'),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    forceEager: false
)]
#[ORM\Entity(repositoryClass: DrinkRepository::class)]
class Drink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('read')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['read', 'write'])]
    private ?float $price = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['read', 'write'])]
    private ?Media $image = null;

    /**
     * @var Collection<int, Command>
     */
    #[ORM\ManyToMany(targetEntity: Command::class, mappedBy: 'drinks')]
    private Collection $commands;

    public function __construct()
    {
        $this->commands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?Media
    {
        return $this->image;
    }

    public function setImage(?Media $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Command>
     */
    public function getCommands(): Collection
    {
        return $this->commands;
    }

    public function addCommand(Command $command): static
    {
        if (!$this->commands->contains($command)) {
            $this->commands->add($command);
            $command->addDrink($this);
        }

        return $this;
    }

    public function removeCommand(Command $command): static
    {
        if ($this->commands->removeElement($command)) {
            $command->removeDrink($this);
        }

        return $this;
    }
}
