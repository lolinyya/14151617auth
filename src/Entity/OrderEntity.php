<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class OrderEntity
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    private ?People $people = null;

    #[ORM\ManyToMany(targetEntity: Dish::class)]
    private Collection $dishes;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderDocument::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $documents;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->dishes = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getPeople(): ?People
    {
        return $this->people;
    }
    public function setPeople(?People $c): self
    {
        $this->people = $c;
        return $this;
    }

    public function getDishes(): Collection
    {
        return $this->dishes;
    }
    public function addDish(Dish $d): self
    {
        if (!$this->dishes->contains($d)) $this->dishes->add($d);
        return $this;
    }
    public function removeDish(Dish $d): self
    {
        $this->dishes->removeElement($d);
        return $this;
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }
    public function addDocument(OrderDocument $d): self
    {
        if (!$this->documents->contains($d)) {
            $this->documents->add($d);
            $d->setOrder($this);
        }
        return $this;
    }
    public function removeDocument(OrderDocument $d): self
    {
        $this->documents->removeElement($d);
        return $this;
    }

    public function getTotalPrice(): int
    {
        return array_reduce($this->dishes->toArray(), fn($s, $d) => $s + $d->getPrice(), 0);
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }   
}
