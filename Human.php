<?php

class Human {
  public function __construct(
    public string $name,
    public float $health = 100,
    public ?Weapon $weapon = null,
    public ?Shield $shield = null,
  ) {}

  public function getName(): string {
    return $this->name;
  }

  public function isAlive(): bool {
    return $this->health > 0;
  }

  public function attack(Human $target): float|false {
    $damage = $this->weapon ? $this->weapon->getDamage() : rand(1, 5);

    if ($target->shield && !$target->shield->isBroken()) {
      if (rand(1, 100) <= (20 * $target->shield->tier)) {
        if ($target->shield->protect($damage)) {
          echo "🛡️  Le bouclier bloque le coup ! (Durabilité restante: {$target->shield->durability})\n";
          return false;
        }
      }
    }

    $target->health -= $damage;
    return $damage;
  }
}
