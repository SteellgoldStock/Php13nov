<?php

class Quiver {
  public function __construct(
    public ?int $arrows = null
  ) {}

  public function getArrows(): ?int {
    return $this->arrows;
  }

  public function hasArrows(): bool {
    return $this->arrows === null || $this->arrows > 0;
  }

  public function consumeArrow(): bool {
    if ($this->arrows === null) return true;
    if ($this->arrows <= 0) return false;

    $this->arrows--;
    return true;
  }

  public function getRemainingArrows(): ?int {
    return $this->arrows;
  }
}