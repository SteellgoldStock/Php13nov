<?php

class Human {
  private const float BASE_RANGE = 1.0;
  private const float DEFAULT_STEP = 1.0;

  public function __construct(
    public string $name,
    public float $health = 100,
    public ?Weapon $weapon = null,
    public ?Weapon $secondaryWeapon = null,
    public ?Shield $shield = null,
    public float $position = 0,
  ) {}

  public function getName(): string {
    return $this->name;
  }

  public function getHealth(): float {
    return $this->health;
  }

  public function isAlive(): bool {
    return $this->health > 0;
  }

  public function getPosition(): float {
    return $this->position;
  }

  public function setPosition(float $position): void {
    $this->position = $position;
  }

  public function moveTowards(Human $target, float $step = self::DEFAULT_STEP): void {
    $distance = $this->distanceTo($target);

    if ($distance === 0.0) {
      return;
    }

    $direction = $this->position < $target->position ? 1 : -1;
    $movement = min($step, $distance);
    $this->position += $direction * $movement;
  }

  public function distanceTo(Human $target): float {
    return abs($this->position - $target->position);
  }

  private function availableWeapons(): array {
    $weapons = [];

    if ($this->weapon) $weapons[] = $this->weapon;
    if ($this->secondaryWeapon) $weapons[] = $this->secondaryWeapon;

    return $weapons;
  }

  private function selectWeaponForTarget(Human $target, bool $requireAmmo = true): ?Weapon {
    $distance = $this->distanceTo($target);

    foreach ($this->availableWeapons() as $weaponCandidate) {
      if ($distance <= $weaponCandidate->getRange()) {
        if (!$requireAmmo || $this->weaponHasAmmo($weaponCandidate)) {
          return $weaponCandidate;
        }
      }
    }

    return null;
  }

  private function weaponHasAmmo(Weapon $weapon): bool {
    return $weapon->hasAmmo();
  }

  private function consumeAmmo(Weapon $weapon): bool {
    return $weapon->consumeAmmo();
  }

  private function determineWeaponKind(?Weapon $weapon): string {
    if ($weapon === null) {
      return 'unarmed';
    }

    if ($weapon === $this->weapon) {
      return 'primary';
    }

    if ($weapon === $this->secondaryWeapon) {
      return 'secondary';
    }

    return 'unknown';
  }

  public function attack(Human $target): array {
    $distance = $this->distanceTo($target);
    $weapon = $this->selectWeaponForTarget($target, requireAmmo: true);
    $weaponWithoutAmmo = $this->selectWeaponForTarget($target, requireAmmo: false);

    if (!$weapon && $distance > self::BASE_RANGE) {
      if ($weaponWithoutAmmo && !$weaponWithoutAmmo->isMelee()) {
        if ($distance <= $weaponWithoutAmmo->getRange()) {
          return [
            'type' => 'no_ammo',
            'weaponName' => $weaponWithoutAmmo->getName(),
            'weaponKind' => $this->determineWeaponKind($weaponWithoutAmmo),
            'damage' => 0.0,
            'ammoRemaining' => $weaponWithoutAmmo->getRemainingAmmo(),
          ];
        }

        return [
          'type' => 'out_of_range',
          'reason' => 'no_ammo',
          'distance' => $distance,
          'weaponName' => $weaponWithoutAmmo->getName(),
          'weaponKind' => $this->determineWeaponKind($weaponWithoutAmmo),
          'shouldMove' => false
        ];
      }

      $reason = $weaponWithoutAmmo ? 'no_ammo' : 'distance';

      return [
        'type' => 'out_of_range',
        'reason' => $reason,
        'distance' => $distance,
        'weaponName' => $weaponWithoutAmmo?->getName(),
        'weaponKind' => $weaponWithoutAmmo ? ($weaponWithoutAmmo === $this->weapon ? 'primary' : 'secondary') : null,
        'shouldMove' => true
      ];
    }

    if (!$weapon && $weaponWithoutAmmo && $distance <= $weaponWithoutAmmo->getRange()) {
      return [
        'type' => 'no_ammo',
        'weaponName' => $weaponWithoutAmmo->getName(),
        'weaponKind' => $this->determineWeaponKind($weaponWithoutAmmo),
        'damage' => 0.0,
        'ammoRemaining' => $weaponWithoutAmmo->getRemainingAmmo()
      ];
    }

    $weaponName = $weapon?->getName() ?? 'poings';
    $weaponKind = $this->determineWeaponKind($weapon);
    $damage = $weapon ? $weapon->getDamage() : mt_rand(1, 5);

    if ($weapon && !$this->consumeAmmo($weapon)) {
      return [
        'type' => 'no_ammo',
        'weaponName' => $weaponName,
        'weaponKind' => $weaponKind,
        'damage' => 0.0,
        'ammoRemaining' => $weapon->getRemainingAmmo()
      ];
    }

    $shieldDurability = null;
    $blocked = false;

    if ($target->shield && !$target->shield->isBroken()) {
      $blockChance = max(0, min(100, 20 * $target->shield->tier));

      if (mt_rand(1, 100) <= $blockChance) {
        if ($target->shield->protect($damage)) {
          $blocked = true;
          $shieldDurability = $target->shield->durability;
        }
      }
    }

    if ($blocked) {
      return [
        'type' => 'blocked',
        'weaponName' => $weaponName,
        'weaponKind' => $weaponKind,
        'damage' => 0.0,
        'shieldDurability' => $shieldDurability,
        'ammoRemaining' => $weapon?->getRemainingAmmo()
      ];
    }

    $target->health -= $damage;

    return [
      'type' => 'damage',
      'weaponName' => $weaponName,
      'weaponKind' => $weaponKind,
      'damage' => $damage,
      'ammoRemaining' => $weapon?->getRemainingAmmo()
    ];
  }
}
