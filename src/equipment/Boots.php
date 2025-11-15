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

  /**
   * Creates new boots with predefined or custom bonuses
   *
   * @param string $type The boots type (use TYPE_* constants or custom name)
   * @param float|null $movementBonus The movement speed modifier (can be negative)
   * @param float|null $resistanceBonus The damage resistance bonus
   * @param float|null $dodgeBonus The dodge chance bonus
   * @param bool $isCustom Whether this is a custom boots type
   */
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
   * Creates custom boots with specific bonuses
   *
   * @param string $name The custom boots name
   * @param float $movementBonus The movement speed modifier (can be negative)
   * @param float $resistanceBonus The damage resistance bonus
   * @param float $dodgeBonus The dodge chance bonus
   * @return self A new custom Boots instance
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

  /**
   * Sets the boots' stats
   *
   * @param float $movement The movement bonus value
   * @param float $resistance The resistance bonus value
   * @param float $dodge The dodge bonus value
   * @return void
   */
  private function setStats(float $movement, float $resistance, float $dodge): void {
    $this->movementBonus = $movement;
    $this->resistanceBonus = $resistance;
    $this->dodgeBonus = $dodge;
  }

  /**
   * Returns the boots type
   *
   * @return string The boots type identifier
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Returns a human-readable name for the boots type
   *
   * @return string The localized boots type name
   */
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
   *
   * @return float The movement speed modifier
   */
  public function getMovementBonus(): float {
    return $this->movementBonus;
  }

  /**
   * Returns the resistance bonus (additional damage reduction)
   *
   * @return float The resistance bonus percentage
   */
  public function getResistanceBonus(): float {
    return $this->resistanceBonus;
  }

  /**
   * Returns the dodge bonus (increases dodge chance)
   *
   * @return float The dodge bonus percentage
   */
  public function getDodgeBonus(): float {
    return $this->dodgeBonus;
  }

  /**
   * Checks if boots have a movement bonus (positive or negative)
   *
   * @return bool True if movement bonus is non-zero, false otherwise
   */
  public function hasMovementBonus(): bool {
    return $this->movementBonus != 0.0;
  }

  /**
   * Checks if boots have a resistance bonus
   *
   * @return bool True if resistance bonus is greater than 0, false otherwise
   */
  public function hasResistanceBonus(): bool {
    return $this->resistanceBonus > 0.0;
  }

  /**
   * Checks if boots have a dodge bonus
   *
   * @return bool True if dodge bonus is greater than 0, false otherwise
   */
  public function hasDodgeBonus(): bool {
    return $this->dodgeBonus > 0.0;
  }
}