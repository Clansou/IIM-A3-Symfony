<?php

namespace App\Entity;

use App\Repository\CommandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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
        new GetCollection(security: "is_granted('ROLE_SERVEUR') or is_granted('ROLE_BARMAN') or is_granted('ROLE_PATRON')"),
        new Get(security: "is_granted('ROLE_SERVEUR') or is_granted('ROLE_BARMAN') or is_granted('ROLE_PATRON')"),
        new Post(name: 'create_command', routeName: 'create_command'),
        new Put(security: "is_granted('ROLE_SERVEUR') and object.getStatus() != 'payée'", securityMessage: 'You are not allowed to edit this order'),
        new Patch(security: "is_granted('ROLE_SERVEUR') and object.getStatus() != 'payée'", securityMessage: 'You are not allowed to edit this order'),
        new Patch(name: 'assign_command_to_barman', routeName: 'assign_command_to_barman'),
        new Patch(name: 'command_is_ready', routeName: 'command_is_ready'),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    forceEager: false
)]
#[ORM\Entity(repositoryClass: CommandRepository::class)]
class Command
{
    #[ORM\Id]
    #[Groups('read')]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['read', 'write'])]
    private ?\DateTimeInterface $created_date = null;

    /**
     * @var Collection<int, Drink>
     */
    #[ORM\ManyToMany(targetEntity: Drink::class, inversedBy: 'commands')]
    #[Groups(['read', 'write'])]
    private Collection $drinks;

    #[ORM\Column]
    #[Groups(['read', 'write'])]
    private ?int $table_number = null;

    #[ORM\ManyToOne(inversedBy: 'commands')]
    #[Groups(['read', 'write'])]
    private ?User $server = null;

    #[ORM\ManyToOne(inversedBy: 'commands')]
    #[Groups(['read', 'write'])]
    private ?User $barman = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    private ?string $status = null;

    public function __construct()
    {
        $this->drinks = new ArrayCollection();
        $this->created_date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->created_date;
    }

    public function setCreatedDate(\DateTimeInterface $created_date): static
    {
        $this->created_date = $created_date;

        return $this;
    }

    /**
     * @return Collection<int, Drink>
     */
    public function getDrinks(): Collection
    {
        return $this->drinks;
    }

    public function addDrink(Drink $drink): static
    {
        if (!$this->drinks->contains($drink)) {
            $this->drinks->add($drink);
        }

        return $this;
    }

    public function removeDrink(Drink $drink): static
    {
        $this->drinks->removeElement($drink);

        return $this;
    }

    public function getTableNumber(): ?int
    {
        return $this->table_number;
    }

    public function setTableNumber(int $table_number): static
    {
        $this->table_number = $table_number;

        return $this;
    }

    public function getServer(): ?User
    {
        return $this->server;
    }

    public function setServer(?User $server): static
    {
        $this->server = $server;

        return $this;
    }

    public function getBarman(): ?User
    {
        return $this->barman;
    }

    public function setBarman(?User $barman): static
    {
        $this->barman = $barman;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
