<?php

namespace App\Equipment;

class Weapon {
  public const string TYPE_SECONDARY = 'secondary';
  public const string TYPE_PRIMARY = 'primary';

  /**
   * Creates a new weapon
   *
   * @param string $name The weapon's name
   * @param string $type The weapon type (TYPE_PRIMARY or TYPE_SECONDARY)
   * @param float $damage The base damage dealt by this weapon
   * @param float $range The maximum range of the weapon
   * @param Quiver|null $quiver The quiver for ranged weapons (null for melee)
   * @param bool $isMelee Whether this is a melee weapon
   */
  public function __construct(
    public string  $name,
    public string  $type = self::TYPE_PRIMARY,
    public float   $damage = 50,
    public float   $range = 1,
    public ?Quiver $quiver = null,
    public bool    $isMelee = true,
  ) {
  }

  /**
   * Returns the weapon's name
   *
   * @return string The weapon name
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Returns the weapon's damage
   *
   * @return float The base damage value
   */
  public function getDamage(): float {
    return $this->damage;
  }

  /**
   * Returns the weapon's range
   *
   * @return float The maximum attack range
   */
  public function getRange(): float {
    return $this->range;
  }

  /**
   * Checks if the weapon has ammunition available
   *
   * @return bool True if weapon has ammo or doesn't need it, false if out of ammo
   */
  public function hasAmmo(): bool {
    return $this->quiver === null || $this->quiver->hasArrows();
  }

  /**
   * Consumes one unit of ammunition
   *
   * @return bool True if ammo was consumed successfully, false otherwise
   */
  public function consumeAmmo(): bool {
    if ($this->quiver === null) return true;
    return $this->quiver->consumeArrow();
  }

  /**
   * Returns the remaining ammunition count
   *
   * @return int|null The remaining ammo, or null if weapon doesn't use ammo
   */
  public function getRemainingAmmo(): ?int {
    return $this->quiver?->getRemainingArrows();
  }

  /**
   * Checks if this is a melee weapon
   *
   * @return bool True if melee weapon, false if ranged
   */
  public function isMelee(): bool {
    return $this->isMelee;
  }

  /**
   * Returns the weapon's quiver
   *
   * @return Quiver|null The quiver if ranged weapon, null otherwise
   */
  public function getQuiver(): ?Quiver {
    return $this->quiver;
  }

  /**
   * Restores ammunition to the weapon
   *
   * @param int|null $flat The flat number of ammo to restore
   * @param float|null $ratio The ratio of max ammo to restore (0.0 to 1.0)
   * @return int The amount of ammunition actually restored
   */
  public function restoreAmmo(?int $flat = null, ?float $ratio = null): int {
    if ($this->quiver === null) {
      return 0;
    }

    return $this->quiver->restore($flat, $ratio);
  }
}