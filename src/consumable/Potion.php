<?php

namespace App\Consumable;

use App\Entity\Human;

class Potion extends Consumable {
  public const string EFFECT_HEAL = 'heal';
  public const string EFFECT_ATTACK = 'attack';
  public const string EFFECT_EVASION = 'evasion';
  public const string EFFECT_ENDURANCE = 'endurance';
  public const string EFFECT_ANTIDOTE = 'antidote';

  /**
   * Creates a new potion with the specified effect
   *
   * @param string $name The name of the potion
   * @param string $effect The type of effect (use EFFECT_* constants)
   * @param array $config Configuration array specific to the effect type
   * @param string $description The description of the potion
   */
  private function __construct(
    string         $name,
    private string $effect,
    private array  $config = [],
    string         $description = ''
  ) {
    parent::__construct($name, $description);
  }

  /**
   * Creates a healing potion that restores health
   *
   * @param string $name The name of the potion
   * @param int $min The minimum health restored
   * @param int $max The maximum health restored
   * @return self A new healing Potion instance
   */
  public static function healing(string $name, int $min = 20, int $max = 60): self {
    return new self(
      $name,
      self::EFFECT_HEAL,
      ['min' => $min, 'max' => $max],
      "Restaure entre {$min} et {$max} PV."
    );
  }

  /**
   * Creates an attack boost potion that increases damage output
   *
   * @param string $name The name of the potion
   * @param float $percent The damage increase percentage (0.0 to 1.0)
   * @param int $turns The number of turns the effect lasts
   * @return self A new attack boost Potion instance
   */
  public static function attackBoost(string $name, float $percent, int $turns): self {
    return new self(
      $name,
      self::EFFECT_ATTACK,
      ['percent' => $percent, 'turns' => $turns],
      sprintf("Augmente les dégâts de %d%% pendant %d tours.", (int)round($percent * 100), $turns)
    );
  }

  /**
   * Creates an evasion boost potion that increases dodge/block chances
   *
   * @param string $name The name of the potion
   * @param float $percent The evasion increase percentage (0.0 to 1.0)
   * @param int $turns The number of turns the effect lasts
   * @return self A new evasion boost Potion instance
   */
  public static function evasionBoost(string $name, float $percent, int $turns): self {
    return new self(
      $name,
      self::EFFECT_EVASION,
      ['percent' => $percent, 'turns' => $turns],
      sprintf("Augmente les chances de blocage/esquive de %d%% pendant %d tours.", (int)round($percent * 100), $turns)
    );
  }

  /**
   * Creates an endurance potion that restores ammunition
   *
   * @param string $name The name of the potion
   * @param float $ratio The ratio of maximum ammo to restore (0.0 to 1.0)
   * @param int $flat The flat amount of ammo to add
   * @return self A new endurance Potion instance
   */
  public static function endurance(string $name, float $ratio = 0.5, int $flat = 0): self {
    return new self(
      $name,
      self::EFFECT_ENDURANCE,
      ['ratio' => $ratio, 'flat' => $flat],
      "Restaure une partie des munitions."
    );
  }

  /**
   * Creates an antidote potion that removes poison effects
   *
   * @param string $name The name of the potion
   * @return self A new antidote Potion instance
   */
  public static function antidote(string $name): self {
    return new self(
      $name,
      self::EFFECT_ANTIDOTE,
      [],
      "Supprime les effets de poison actifs."
    );
  }

  /**
   * Consumes the potion and applies its effect to the target
   *
   * @param Human $target The fighter consuming the potion
   * @return array Array of message arrays describing the effects
   */
  public function consume(Human $target): array {
    return match ($this->effect) {
      self::EFFECT_HEAL => $this->applyHeal($target),
      self::EFFECT_ATTACK => $this->applyAttackBoost($target),
      self::EFFECT_EVASION => $this->applyEvasionBoost($target),
      self::EFFECT_ENDURANCE => $this->applyEndurance($target),
      self::EFFECT_ANTIDOTE => $this->applyAntidote($target),
      default => [['emoji' => '❔', 'text' => "Effet inconnu pour {$this->name}."]]
    };
  }

  /**
   * Applies healing effect to the target
   *
   * @param Human $target The fighter receiving the healing
   * @return array Array of message arrays describing the healing
   */
  private function applyHeal(Human $target): array {
    $min = max(1, $this->config['min'] ?? 10);
    $max = max($min, $this->config['max'] ?? 50);
    $amount = mt_rand($min, $max);
    $healed = $target->heal($amount);

    return [
      ['emoji' => '🧪', 'text' => "{$target->getName()} boit {$this->name} et récupère {$healed} PV."]
    ];
  }

  /**
   * Applies attack boost effect to the target
   *
   * @param Human $target The fighter receiving the attack boost
   * @return array Array of message arrays describing the boost
   */
  private function applyAttackBoost(Human $target): array {
    $percent = max(0.0, $this->config['percent'] ?? 0.0);
    $turns = max(1, $this->config['turns'] ?? 1);
    $target->addAttackBonus($percent, $turns);
    $percentLabel = (int)round($percent * 100);

    return [
      ['emoji' => '🔥', 'text' => "{$target->getName()} s'embrase de rage grâce à {$this->name} (+{$percentLabel}% dégâts, {$turns} tours)."]
    ];
  }

  /**
   * Applies evasion boost effect to the target
   *
   * @param Human $target The fighter receiving the evasion boost
   * @return array Array of message arrays describing the boost
   */
  private function applyEvasionBoost(Human $target): array {
    $percent = max(0.0, $this->config['percent'] ?? 0.0);
    $turns = max(1, $this->config['turns'] ?? 1);
    $target->addDodgeBonus($percent, $turns);
    $percentLabel = (int)round($percent * 100);

    return [
      ['emoji' => '🎯', 'text' => "{$target->getName()} gagne en lucidité avec {$this->name} (+{$percentLabel}% blocage/esquive pendant {$turns} tours)."]
    ];
  }

  /**
   * Applies endurance effect to the target, restoring ammunition
   *
   * @param Human $target The fighter receiving the ammunition restoration
   * @return array Array of message arrays describing the restoration
   */
  private function applyEndurance(Human $target): array {
    $ratio = $this->config['ratio'] ?? 0.5;
    $flat = $this->config['flat'] ?? 0;
    $restored = $target->restoreAmmo($ratio, $flat);

    if ($restored <= 0) {
      return [
        ['emoji' => '🪙', 'text' => "{$this->name} n'a aucun effet : aucune munition à restaurer pour {$target->getName()}."]
      ];
    }

    return [
      ['emoji' => '🏹', 'text' => "{$target->getName()} retrouve {$restored} munitions grâce à {$this->name}."]
    ];
  }

  /**
   * Applies antidote effect to the target, removing poison
   *
   * @param Human $target The fighter being cured of poison
   * @return array Array of message arrays describing the cure
   */
  private function applyAntidote(Human $target): array {
    if ($target->cleansePoison()) {
      return [
        ['emoji' => '💊', 'text' => "{$target->getName()} est purgé de tout poison par {$this->name}."]
      ];
    }

    return [
      ['emoji' => '💊', 'text' => "{$this->name} n'a rien à purifier chez {$target->getName()}."]
    ];
  }
}