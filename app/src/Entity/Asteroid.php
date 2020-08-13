<?php

namespace App\Entity;

use App\Repository\AsteroidRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;

/**
 * @ORM\Entity(repositoryClass=AsteroidRepository::class)
 */
class Asteroid implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $speed;

    /**
     * @ORM\Column(name="is_hazardous", type="boolean")
     */
    private $isHazardous;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getReference(): ?int
    {
        return $this->reference;
    }

    public function setReference(int $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    public function setSpeed(float $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getIsHazardous(): ?bool
    {
        return $this->isHazardous;
    }

    public function setIsHazardous(bool $isHazardous): self
    {
        $this->isHazardous = $isHazardous;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'date'         => $this->date->format('Y-m-d'),
            'reference'    => $this->reference,
            'name'         => $this->name,
            'speed'        => $this->speed,
            'is_hazardous' => $this->isHazardous,
        ];
    }
}
