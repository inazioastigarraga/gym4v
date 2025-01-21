<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class ActivityType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activity:read', 'activity_type:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['activity:read', 'activity_type:read'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['activity:read', 'activity_type:read'])]
    private ?int $numberMonitors = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNumberMonitors(): ?int
    {
        return $this->numberMonitors;
    }

    public function setNumberMonitors(int $numberMonitors): self
    {
        $this->numberMonitors = $numberMonitors;

        return $this;
    }
}
