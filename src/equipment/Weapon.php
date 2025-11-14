<?php

class Weapon {
  public const string TYPE_SECONDARY = 'secondary';
  public const string TYPE_PRIMARY = 'primary';

  public function __construct(
    public string $name,
    public string $type = self::TYPE_PRIMARY,
    public float $damage = 50,
    public float $range = 1,
    public ?Quiver $quiver = null,
    public bool $isMelee = true,
  ) {}

  public function getName(): string {
    return $this->name;
  }

  public function getDamage(): float {
    return $this->damage;
  }

  public function getRange(): float {
    return $this->range;
  }

  public function hasAmmo(): bool {
    return $this->quiver === null || $this->quiver->hasArrows();
  }

  public function consumeAmmo(): bool {
    if ($this->quiver === null) return true;
    return $this->quiver->consumeArrow();
  }

  public function getRemainingAmmo(): ?int {
    return $this->quiver?->getRemainingArrows();
  }

  public function isMelee(): bool {
    return $this->isMelee;
  }

  public function getQuiver(): ?Quiver {
    return $this->quiver;
  }
}