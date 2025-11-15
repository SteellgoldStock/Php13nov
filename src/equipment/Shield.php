<?php

namespace App\Equipment;

class Shield {
  /**
   * Creates a new shield
   *
   * @param int $durability The shield's durability points
   * @param int $tier The shield tier (affects block chance: 20% per tier)
   */
  public function __construct(
    public int $durability = 100,
    public int $tier = 0
  ) {

  }

  /**
   * Checks if the shield is broken
   *
   * @return bool True if durability is 0 or less, false otherwise
   */
  public function isBroken(): bool {
    return $this->durability <= 0;
  }

  /**
   * Returns the current durability
   *
   * @return int The durability points remaining
   */
  public function getDurability(): int {
    return $this->durability;
  }

  /**
   * Returns the shield tier
   *
   * @return int The shield tier level
   */
  public function getTier(): int {
    return $this->tier;
  }

  /**
   * Attempts to block incoming damage with the shield
   *
   * @param int|float $damage The amount of damage to block
   * @return bool True if damage was blocked, false if shield is broken
   */
  public function protect(int|float $damage): bool {
    if ($this->isBroken()) {
      return false;
    }

    $this->durability = (int)round($this->durability - $damage);

    if ($this->durability <= 0) {
      $this->durability = 0;
      echo "🛡️  Le bouclier est brisé !\n";
    }

    return true;
  }
}