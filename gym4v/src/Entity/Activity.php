<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activity:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ActivityType::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['activity:read'])]
    private ?ActivityType $activityType = null;

    #[ORM\ManyToMany(targetEntity: Monitor::class)]
    #[ORM\JoinTable(name: "activities_monitors")]
    #[Groups(['activity:read'])]
    private Collection $monitors;

    #[ORM\Column(type: "datetime")]
    #[Groups(['activity:read'])]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(type: "datetime")]
    #[Groups(['activity:read'])]
    private ?\DateTimeInterface $dateEnd = null;

    public function __construct()
    {
        $this->monitors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivityType(): ?ActivityType
    {
        return $this->activityType;
    }

    public function setActivityType(ActivityType $activityType): self
    {
        $this->activityType = $activityType;

        return $this;
    }

    public function getMonitors(): Collection
    {
        return $this->monitors;
    }

    public function addMonitor(Monitor $monitor): self
    {
        if (!$this->monitors->contains($monitor)) {
            $this->monitors->add($monitor);
        }

        return $this;
    }

    public function removeMonitor(Monitor $monitor): self
    {
        $this->monitors->removeElement($monitor);

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }
}
