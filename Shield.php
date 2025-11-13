<?php

class Shield {
  public function __construct(
    public int $durability = 100,
    public int $tier = 0
  ) {

  }

  public function isBroken(): bool {
    return $this->durability <= 0;
  }

  public function protect(int|float $damage): bool {
    if ($this->isBroken()) {
      return false;
    }

    $this->durability -= $damage;

    if ($this->durability <= 0) {
      $this->durability = 0;
      echo "🛡️  Le bouclier est brisé !\n";
    }

    return true;
  }
}
