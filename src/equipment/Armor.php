<?php

namespace App\Equipment;

class Armor {
  // Predefined armor types
  public const string TYPE_LIGHT = 'light';
  public const string TYPE_IRON = 'iron';
  public const string TYPE_SCALE = 'scale';

  // Custom armor type
  public const string TYPE_CUSTOM = 'custom';

  public function __construct(
    public string $type = self::TYPE_LIGHT,
    public int $durability = 40,
    public float $damageReduction = 0.10,
    private bool $isCustom = false
  ) {
    if ($this->isCustom) return;

    match($type) {
      self::TYPE_LIGHT => $this->setStats(40, 0.10),
      self::TYPE_IRON => $this->setStats(80, 0.25),
      self::TYPE_SCALE => $this->setStats(120, 0.40),
      default => $this->setStats($durability, $damageReduction)
    };
  }

  public static function createCustom(
    string $name,
    int $durability,
    float $damageReduction
  ): self {
    return new self(
      type: $name,
      durability: $durability,
      damageReduction: $damageReduction,
      isCustom: true
    );
  }

  private function setStats(int $durability, float $reduction): void {
    $this->durability = $durability;
    $this->damageReduction = $reduction;
  }

  public function isBroken(): bool {
    return $this->durability <= 0;
  }

  public function getDurability(): int {
    return $this->durability;
  }

  public function getType(): string {
    return $this->type;
  }

  public function getDamageReduction(): float {
    return $this->damageReduction;
  }

  public function getTypeName(): string {
    return match($this->type) {
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

