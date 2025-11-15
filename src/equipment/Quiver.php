<?php

namespace App\Equipment;

class Quiver {
  private ?int $capacity;

  /**
   * Creates a new quiver for storing arrows
   *
   * @param int|null $arrows The initial number of arrows (null for infinite)
   * @param int|null $capacity The maximum capacity (defaults to initial arrows)
   */
  public function __construct(
    public ?int $arrows = null,
    ?int        $capacity = null
  ) {
    $this->capacity = $capacity ?? $arrows;
  }

  /**
   * Returns the current number of arrows
   *
   * @return int|null The arrow count, or null for infinite arrows
   */
  public function getArrows(): ?int {
    return $this->arrows;
  }

  /**
   * Checks if the quiver has arrows available
   *
   * @return bool True if arrows are available (or infinite), false if empty
   */
  public function hasArrows(): bool {
    return $this->arrows === null || $this->arrows > 0;
  }

  /**
   * Consumes one arrow from the quiver
   *
   * @return bool True if an arrow was consumed, false if quiver is empty
   */
  public function consumeArrow(): bool {
    if ($this->arrows === null) return true;
    if ($this->arrows <= 0) return false;

    $this->arrows--;
    return true;
  }

  /**
   * Returns the remaining arrow count
   *
   * @return int|null The remaining arrows, or null for infinite arrows
   */
  public function getRemainingArrows(): ?int {
    return $this->arrows;
  }

  /**
   * Restores arrows to the quiver
   *
   * @param int|null $flat The flat number of arrows to add
   * @param float|null $ratio The ratio of capacity to restore (0.0 to 1.0)
   * @return int The number of arrows actually restored
   */
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

  /**
   * Returns the maximum capacity of the quiver
   *
   * @return int|null The maximum capacity, or null if no limit
   */
  public function getCapacity(): ?int {
    return $this->capacity;
  }
}