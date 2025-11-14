<?php

namespace App\Consumable;

use App\Entity\Human;

class Food extends Consumable {
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

  public static function plain(string $name, int $healAmount): self {
    return new self($name, $healAmount);
  }

  public static function withAttackBonus(string $name, int $healAmount, float $bonusPercent, int $turns): self {
    return new self(
      $name,
      $healAmount,
      attackBonusPercent: $bonusPercent,
      attackBonusTurns: $turns
    );
  }

  public static function withMovementBonus(string $name, int $healAmount, float $movementBonus, int $turns): self {
    return new self(
      $name,
      $healAmount,
      movementBonusPercent: $movementBonus,
      movementBonusTurns: $turns
    );
  }

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