<?php

namespace App\Consumable;

use App\Entity\Human;

class Food extends Consumable {
  /**
   * Creates a new food item with various potential bonuses
   *
   * @param string $name The name of the food
   * @param int $healAmount The amount of health restored when consumed
   * @param float $attackBonusPercent The attack bonus percentage (0.0 to 1.0)
   * @param int $attackBonusTurns The number of turns the attack bonus lasts
   * @param float $movementBonusPercent The movement bonus percentage (0.0 to 1.0)
   * @param int $movementBonusTurns The number of turns the movement bonus lasts
   * @param float $dodgeBonusPercent The dodge bonus percentage (0.0 to 1.0)
   * @param int $dodgeBonusTurns The number of turns the dodge bonus lasts
   * @param string $description The description of the food
   */
  public function __construct(
    string        $name,
    private int   $healAmount,
    private float $attackBonusPercent = 0.0,
    private int   $attackBonusTurns = 0,
    private float $movementBonusPercent = 0.0,
    private int   $movementBonusTurns = 0,
    private float $dodgeBonusPercent = 0.0,
    private int   $dodgeBonusTurns = 0,
    string        $description = ''
  ) {
    parent::__construct($name, $description);
  }

  /**
   * Creates a plain food item with only healing properties
   *
   * @param string $name The name of the food
   * @param int $healAmount The amount of health restored
   * @return self A new Food instance
   */
  public static function plain(string $name, int $healAmount): self {
    return new self($name, $healAmount);
  }

  /**
   * Creates a food item that heals and provides an attack bonus
   *
   * @param string $name The name of the food
   * @param int $healAmount The amount of health restored
   * @param float $bonusPercent The attack bonus percentage (0.0 to 1.0)
   * @param int $turns The number of turns the bonus lasts
   * @return self A new Food instance
   */
  public static function withAttackBonus(string $name, int $healAmount, float $bonusPercent, int $turns): self {
    return new self(
      $name,
      $healAmount,
      attackBonusPercent: $bonusPercent,
      attackBonusTurns: $turns
    );
  }

  /**
   * Creates a food item that heals and provides a movement bonus
   *
   * @param string $name The name of the food
   * @param int $healAmount The amount of health restored
   * @param float $movementBonus The movement bonus percentage (0.0 to 1.0)
   * @param int $turns The number of turns the bonus lasts
   * @return self A new Food instance
   */
  public static function withMovementBonus(string $name, int $healAmount, float $movementBonus, int $turns): self {
    return new self(
      $name,
      $healAmount,
      movementBonusPercent: $movementBonus,
      movementBonusTurns: $turns
    );
  }

  /**
   * Consumes the food item and applies its effects to the target
   *
   * @param Human $target The fighter consuming the food
   * @return array Array of message strings describing the effects
   */
  public function consume(Human $target): array {
    $messages = [];
    $healed = $target->heal($this->healAmount);
    $messages[] = "🍽️  {$target->getName()} mange {$this->name} et récupère {$healed} PV.";

    if ($this->attackBonusPercent > 0 && $this->attackBonusTurns > 0) {
      $target->addAttackBonus($this->attackBonusPercent, $this->attackBonusTurns);
      $messages[] = sprintf(
        "🍖 Bonus de dégâts : +%d%% pendant %d tour(s).",
        (int)round($this->attackBonusPercent * 100),
        $this->attackBonusTurns
      );
    }

    if ($this->movementBonusPercent > 0 && $this->movementBonusTurns > 0) {
      $target->addMovementBonus($this->movementBonusPercent, $this->movementBonusTurns);
      $messages[] = sprintf(
        "🥖 Bonus de vitesse : +%d%% pendant %d tour(s).",
        (int)round($this->movementBonusPercent * 100),
        $this->movementBonusTurns
      );
    }

    if ($this->dodgeBonusPercent > 0 && $this->dodgeBonusTurns > 0) {
      $target->addDodgeBonus($this->dodgeBonusPercent, $this->dodgeBonusTurns);
      $messages[] = sprintf(
        "🍏 Bonus d'esquive : +%d%% pendant %d tour(s).",
        (int)round($this->dodgeBonusPercent * 100),
        $this->dodgeBonusTurns
      );
    }

    return $messages;
  }
}