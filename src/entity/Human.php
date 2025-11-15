<?php

namespace App\Entity;

use App\Consumable\Consumable;
use App\Equipment\Armor;
use App\Equipment\Boots;
use App\Equipment\Shield;
use App\Equipment\Weapon;

class Human {
  private const float BASE_RANGE = 1.0;
  private const float DEFAULT_STEP = 1.0;
  private const float BASE_DODGE_CHANCE = 5.0;

  private array $attackBuff = ['percent' => 0.0, 'turns' => 0];
  private array $dodgeBuff = ['percent' => 0.0, 'turns' => 0];
  private array $movementBuff = ['percent' => 0.0, 'turns' => 0];
  private ?array $poison = null;

  /** @var Consumable[] */
  private array $inventory = [];

  public float $maxHealth;

  public function __construct(
    public string  $name,
    public float   $health = 100,
    public ?Weapon $weapon = null,
    public ?Weapon $secondaryWeapon = null,
    public ?Shield $shield = null,
    public ?Armor  $armor = null,
    public ?Boots  $boots = null,
    public float   $position = 0,
  ) {
    $this->maxHealth = $health;
  }

  public function beginTurn(): array {
    $messages = [];

    if ($this->poison && $this->poison['turns'] > 0) {
      $damage = $this->poison['damage'];
      $this->health -= $damage;
      $this->poison['turns']--;

      $messages[] = ['emoji' => '☠️', 'text' => "{$this->getName()} subit {$damage} dégâts de poison ({$this->poison['turns']} tours restants)."];

      if ($this->poison['turns'] <= 0) {
        $messages[] = ['emoji' => '💧', 'text' => "Le poison cesse d'affecter {$this->getName()}."];
        $this->poison = null;
      }
    }

    return $messages;
  }

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
    $bonusMultiplier = 1.0;

    // Temporary movement buff
    if ($this->movementBuff['turns'] > 0) {
      $bonusMultiplier += $this->movementBuff['percent'];
      $this->movementBuff['turns']--;

      if ($this->movementBuff['turns'] <= 0) {
        $this->movementBuff = ['percent' => 0.0, 'turns' => 0];
      }
    }

    // Boots bonus/malus (permanent while equipped)
    if ($this->boots && $this->boots->hasMovementBonus()) {
      $bonusMultiplier += $this->boots->getMovementBonus();
    }

    $movement = min($step * $bonusMultiplier, $distance);
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
    $damage *= $this->getAttackMultiplier();

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
      $this->consumeAttackBuffTurn();
      return [
        'type' => 'blocked',
        'weaponName' => $weaponName,
        'weaponKind' => $weaponKind,
        'damage' => 0.0,
        'shieldDurability' => $shieldDurability,
        'ammoRemaining' => $weapon?->getRemainingAmmo()
      ];
    }

    if ($target->attemptDodge()) {
      $this->consumeAttackBuffTurn();
      return [
        'type' => 'dodged',
        'weaponName' => $weaponName,
        'weaponKind' => $weaponKind,
        'damage' => 0.0,
        'ammoRemaining' => $weapon?->getRemainingAmmo()
      ];
    }

    // Armor absorption
    $armorDurability = null;
    $armorReduction = 0.0;
    if ($target->armor && !$target->armor->isBroken()) {
      $originalDamage = $damage;
      $damage = $target->armor->absorbDamage($damage);
      $armorDurability = $target->armor->getDurability();
      $armorReduction = $originalDamage - $damage;
    }

    // Boots resistance bonus
    $bootsReduction = 0.0;
    if ($target->boots && $target->boots->hasResistanceBonus()) {
      $bootsReduction = $damage * $target->boots->getResistanceBonus();
      $damage -= $bootsReduction;
    }

    $target->health -= $damage;
    $this->consumeAttackBuffTurn();

    return [
      'type' => 'damage',
      'weaponName' => $weaponName,
      'weaponKind' => $weaponKind,
      'damage' => $damage,
      'armorDurability' => $armorDurability,
      'armorReduction' => $armorReduction,
      'bootsReduction' => $bootsReduction,
      'ammoRemaining' => $weapon?->getRemainingAmmo()
    ];
  }

  public function heal(float $amount): float {
    $amount = max(0, $amount);
    $before = $this->health;
    $this->health += $amount;
    return $this->health - $before;
  }

  public function addAttackBonus(float $percent, int $turns): void {
    if ($percent <= 0 || $turns <= 0) {
      return;
    }

    $this->attackBuff['percent'] += $percent;
    $this->attackBuff['turns'] = max($this->attackBuff['turns'], $turns);
  }

  public function addDodgeBonus(float $percent, int $turns): void {
    if ($percent <= 0 || $turns <= 0) {
      return;
    }

    $this->dodgeBuff['percent'] += $percent;
    $this->dodgeBuff['turns'] = max($this->dodgeBuff['turns'], $turns);
  }

  public function addMovementBonus(float $percent, int $turns): void {
    if ($percent <= 0 || $turns <= 0) {
      return;
    }

    $this->movementBuff['percent'] += $percent;
    $this->movementBuff['turns'] = max($this->movementBuff['turns'], $turns);
  }

  public function restoreAmmo(float $ratio = 0.5, int $flat = 0): int {
    $restored = 0;

    foreach ($this->availableWeapons() as $weapon) {
      $restored += $weapon->restoreAmmo(
        flat: $flat > 0 ? $flat : null,
        ratio: $ratio > 0 ? $ratio : null
      );
    }

    return $restored;
  }

  public function applyPoison(float $damagePerTurn, int $turns): void {
    if ($damagePerTurn <= 0 || $turns <= 0) {
      return;
    }

    $this->poison = [
      'damage' => $damagePerTurn,
      'turns' => $turns
    ];
  }

  public function cleansePoison(): bool {
    if ($this->poison === null) {
      return false;
    }

    $this->poison = null;
    return true;
  }

  private function getAttackMultiplier(): float {
    if ($this->attackBuff['turns'] > 0) {
      return 1 + $this->attackBuff['percent'];
    }

    return 1.0;
  }

  private function consumeAttackBuffTurn(): void {
    if ($this->attackBuff['turns'] <= 0) {
      return;
    }

    $this->attackBuff['turns']--;

    if ($this->attackBuff['turns'] <= 0) {
      $this->attackBuff = ['percent' => 0.0, 'turns' => 0];
    }
  }

  private function attemptDodge(): bool {
    $chance = self::BASE_DODGE_CHANCE;

    // Temporary dodge buff
    if ($this->dodgeBuff['turns'] > 0) {
      $chance += $this->dodgeBuff['percent'] * 100;
    }

    // Permanent boots dodge bonus
    if ($this->boots && $this->boots->hasDodgeBonus()) {
      $chance += $this->boots->getDodgeBonus() * 100;
    }

    $chance = max(0, min(95, $chance));
    $roll = mt_rand(1, 100);
    $success = $roll <= $chance;

    if ($this->dodgeBuff['turns'] > 0) {
      $this->dodgeBuff['turns']--;

      if ($this->dodgeBuff['turns'] <= 0) {
        $this->dodgeBuff = ['percent' => 0.0, 'turns' => 0];
      }
    }

    return $success;
  }

  /**
   * Adds a consumable to the inventory
   */
  public function addToInventory(Consumable $consumable): void {
    $this->inventory[] = $consumable;
  }

  /**
   * Returns the complete inventory
   * @return Consumable[]
   */
  public function getInventory(): array {
    return $this->inventory;
  }

  /**
   * Uses a consumable from the inventory by its index
   * @return array|null Usage messages, or null if index is invalid
   */
  public function useConsumable(int $index): ?array {
    if (!isset($this->inventory[$index])) {
      return null;
    }

    $consumable = $this->inventory[$index];
    $messages = $consumable->consume($this);

    // Remove the consumable from inventory after use
    array_splice($this->inventory, $index, 1);

    return $messages;
  }

  /**
   * Returns the current poison information
   */
  public function getPoisonInfo(): ?array {
    return $this->poison;
  }

  /**
   * Checks if the fighter has an active attack buff
   */
  public function hasAttackBuff(): bool {
    return $this->attackBuff['turns'] > 0;
  }

  /**
   * Checks if the fighter has an active dodge buff
   */
  public function hasDodgeBuff(): bool {
    return $this->dodgeBuff['turns'] > 0;
  }

  /**
   * Returns the total available ammunition count
   */
  public function getTotalAmmo(): int {
    $total = 0;
    foreach ($this->availableWeapons() as $weapon) {
      $total += $weapon->getRemainingAmmo();
    }
    return $total;
  }
}