<?php

class Weapon {
  public function __construct(
  	public string $name,
    public int $damage = 50,
  ) {}

  public function getName(): string {
  	return $this->name;
  }

  public function getDamage(): int {
    return $this->damage;
  }
}