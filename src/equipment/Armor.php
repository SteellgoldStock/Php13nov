<?php

namespace App\Equipment;

class Armor {
  // Predefined armor types
  public const string TYPE_LIGHT = 'light';
  public const string TYPE_IRON = 'iron';
  public const string TYPE_SCALE = 'scale';

  // Custom armor type
  public const string TYPE_CUSTOM = 'custom';

  /**
   * Creates a new armor with predefined or custom stats
   *
   * @param string $type The armor type (use TYPE_* constants or custom name)
   * @param int $durability The armor's durability points
   * @param float $damageReduction The damage reduction percentage (0.0 to 1.0)
   * @param bool $isCustom Whether this is a custom armor type
   */
  public function __construct(
    public string $type = self::TYPE_LIGHT,
    public int    $durability = 40,
    public float  $damageReduction = 0.10,
    private bool  $isCustom = false
  ) {
    if ($this->isCustom) return;

    match ($type) {
      self::TYPE_LIGHT => $this->setStats(40, 0.10),
      self::TYPE_IRON => $this->setStats(80, 0.25),
      self::TYPE_SCALE => $this->setStats(120, 0.40),
      default => $this->setStats($durability, $damageReduction)
    };
  }

  /**
   * Creates a custom armor with specific stats
   *
   * @param string $name The custom armor name
   * @param int $durability The armor's durability points
   * @param float $damageReduction The damage reduction percentage (0.0 to 1.0)
   * @return self A new custom Armor instance
   */
  public static function new(
    string $name,
    int    $durability,
    float  $damageReduction
  ): self {
    return new self(
      type: $name,
      durability: $durability,
      damageReduction: $damageReduction,
      isCustom: true
    );
  }

  /**
   * Sets the armor's stats
   *
   * @param int $durability The durability value
   * @param float $reduction The damage reduction value
   * @return void
   */
  private function setStats(int $durability, float $reduction): void {
    $this->durability = $durability;
    $this->damageReduction = $reduction;
  }

  /**
   * Checks if the armor is broken
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
   * Returns the armor type
   *
   * @return string The armor type identifier
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Returns the damage reduction percentage
   *
   * @return float The damage reduction (0.0 to 1.0)
   */
  public function getDamageReduction(): float {
    return $this->damageReduction;
  }

  /**
   * Returns a human-readable name for the armor type
   *
   * @return string The localized armor type name
   */
  public function getTypeName(): string {
    return match ($this->type) {
      self::TYPE_LIGHT => 'Armure légère',
      self::TYPE_IRON => 'Armure de fer',
      self::TYPE_SCALE => 'Armure d\'écaille',
      default => ucfirst($this->type)
    };
  }

  /**
   * Absorbs part of incoming damage and reduces durability
   * @param int|float $damage Incoming damage
   * @return float Remaining damage after absorption
   */
  public function absorbDamage(int|float $damage): float {
    if ($this->isBroken()) {
      return $damage;
    }

    // Calculate damage reduction
    $absorbed = $damage * $this->damageReduction;
    $remainingDamage = $damage - $absorbed;

    // Reduce durability based on absorbed damage
    $this->durability -= (int)ceil($absorbed);

    if ($this->durability <= 0) {
      $this->durability = 0;
      echo "🛡️  {$this->getTypeName()} est détruite !\n";
    }

    return $remainingDamage;
  }
}