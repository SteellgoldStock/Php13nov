<?php

namespace App\Equipment;

class Quiver {
  private ?int $capacity;

  public function __construct(
    public ?int $arrows = null,
    ?int        $capacity = null
  ) {
    $this->capacity = $capacity ?? $arrows;
  }

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

  public function restore(?int $flat = null, ?float $ratio = null): int {
    if ($this->arrows === null) {
      return 0;
    }

    $original = $this->arrows;
    $target = $this->arrows;

    if ($ratio !== null && $this->capacity !== null) {
      $ratio = max(0.0, min(1.0, $ratio));
      $target = max($target, (int)ceil($this->capacity * $ratio));
    }

    if ($flat !== null) {
      $target += max(0, $flat);
    }

    if ($this->capacity !== null) {
      $target = min($target, $this->capacity);
    }

    $this->arrows = (int)$target;

    return $this->arrows - $original;
  }

  public function getCapacity(): ?int {
    return $this->capacity;
  }
}