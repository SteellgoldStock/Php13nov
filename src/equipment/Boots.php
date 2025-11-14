<?php

namespace App\Equipment;

class Boots {
  // Predefined boots types
  public const string TYPE_RUNNING = 'running';
  public const string TYPE_HEAVY = 'heavy';
  public const string TYPE_SILENT = 'silent';

  // Custom boots type
  public const string TYPE_CUSTOM = 'custom';

  private float $movementBonus = 0.0;
  private float $resistanceBonus = 0.0;
  private float $dodgeBonus = 0.0;

  public function __construct(
    public string $type = self::TYPE_RUNNING,
    ?float $movementBonus = null,
    ?float $resistanceBonus = null,
    ?float $dodgeBonus = null,
    private bool $isCustom = false
  ) {
    if ($this->isCustom) {
      $this->movementBonus = $movementBonus ?? 0.0;
      $this->resistanceBonus = $resistanceBonus ?? 0.0;
      $this->dodgeBonus = $dodgeBonus ?? 0.0;
      return;
    }

    match($type) {
      self::TYPE_RUNNING => $this->setStats(0.50, 0.0, 0.0),
      self::TYPE_HEAVY => $this->setStats(-0.20, 0.10, 0.0),
      self::TYPE_SILENT => $this->setStats(0.0, 0.0, 0.20),
      default => $this->setStats(
        $movementBonus ?? 0.0,
        $resistanceBonus ?? 0.0,
        $dodgeBonus ?? 0.0
      )
    };
  }

  /**
   * Create custom boots with specific bonuses
   */
  public static function createCustom(
    string $name,
    float $movementBonus = 0.0,
    float $resistanceBonus = 0.0,
    float $dodgeBonus = 0.0
  ): self {
    return new self(
      type: $name,
      movementBonus: $movementBonus,
      resistanceBonus: $resistanceBonus,
      dodgeBonus: $dodgeBonus,
      isCustom: true
    );
  }

  private function setStats(float $movement, float $resistance, float $dodge): void {
    $this->movementBonus = $movement;
    $this->resistanceBonus = $resistance;
    $this->dodgeBonus = $dodge;
  }

  public function getType(): string {
    return $this->type;
  }

  public function getTypeName(): string {
    return match($this->type) {
      self::TYPE_RUNNING => 'Bottes de course',
      self::TYPE_HEAVY => 'Bottes lourdes',
      self::TYPE_SILENT => 'Bottes silencieuses',
      default => ucfirst($this->type)
    };
  }

  /**
   * Returns the movement bonus (can be negative for heavy boots)
   */
  public function getMovementBonus(): float {
    return $this->movementBonus;
  }

  /**
   * Returns the resistance bonus (additional damage reduction)
   */
  public function getResistanceBonus(): float {
    return $this->resistanceBonus;
  }

  /**
   * Returns the dodge bonus (increases dodge chance)
   */
  public function getDodgeBonus(): float {
    return $this->dodgeBonus;
  }

  /**
   * Checks if boots have a movement bonus
   */
  public function hasMovementBonus(): bool {
    return $this->movementBonus != 0.0;
  }

  /**
   * Checks if boots have a resistance bonus
   */
  public function hasResistanceBonus(): bool {
    return $this->resistanceBonus > 0.0;
  }

  /**
   * Checks if boots have a dodge bonus
   */
  public function hasDodgeBonus(): bool {
    return $this->dodgeBonus > 0.0;
  }
}

