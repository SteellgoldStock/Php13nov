<?php

namespace App\Consumable;

use App\Entity\Human;

class Potion extends Consumable {
  public const string EFFECT_HEAL = 'heal';
  public const string EFFECT_ATTACK = 'attack';
  public const string EFFECT_EVASION = 'evasion';
  public const string EFFECT_ENDURANCE = 'endurance';
  public const string EFFECT_ANTIDOTE = 'antidote';

  private function __construct(
    string         $name,
    private string $effect,
    private array  $config = [],
    string         $description = ''
  ) {
    parent::__construct($name, $description);
  }

  public static function healing(string $name, int $min = 20, int $max = 60): self {
    return new self(
      $name,
      self::EFFECT_HEAL,
      ['min' => $min, 'max' => $max],
      "Restaure entre {$min} et {$max} PV."
    );
  }

  public static function attackBoost(string $name, float $percent, int $turns): self {
    return new self(
      $name,
      self::EFFECT_ATTACK,
      ['percent' => $percent, 'turns' => $turns],
      sprintf("Augmente les dégâts de %d%% pendant %d tours.", (int)round($percent * 100), $turns)
    );
  }

  public static function evasionBoost(string $name, float $percent, int $turns): self {
    return new self(
      $name,
      self::EFFECT_EVASION,
      ['percent' => $percent, 'turns' => $turns],
      sprintf("Augmente les chances de blocage/esquive de %d%% pendant %d tours.", (int)round($percent * 100), $turns)
    );
  }

  public static function endurance(string $name, float $ratio = 0.5, int $flat = 0): self {
    return new self(
      $name,
      self::EFFECT_ENDURANCE,
      ['ratio' => $ratio, 'flat' => $flat],
      "Restaure une partie des munitions."
    );
  }

  public static function antidote(string $name): self {
    return new self(
      $name,
      self::EFFECT_ANTIDOTE,
      [],
      "Supprime les effets de poison actifs."
    );
  }

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

  private function applyHeal(Human $target): array {
    $min = max(1, $this->config['min'] ?? 10);
    $max = max($min, $this->config['max'] ?? 50);
    $amount = mt_rand($min, $max);
    $healed = $target->heal($amount);

    return [
      ['emoji' => '🧪', 'text' => "{$target->getName()} boit {$this->name} et récupère {$healed} PV."]
    ];
  }

  private function applyAttackBoost(Human $target): array {
    $percent = max(0.0, $this->config['percent'] ?? 0.0);
    $turns = max(1, $this->config['turns'] ?? 1);
    $target->addAttackBonus($percent, $turns);
    $percentLabel = (int)round($percent * 100);

    return [
      ['emoji' => '🔥', 'text' => "{$target->getName()} s'embrase de rage grâce à {$this->name} (+{$percentLabel}% dégâts, {$turns} tours)."]
    ];
  }

  private function applyEvasionBoost(Human $target): array {
    $percent = max(0.0, $this->config['percent'] ?? 0.0);
    $turns = max(1, $this->config['turns'] ?? 1);
    $target->addDodgeBonus($percent, $turns);
    $percentLabel = (int)round($percent * 100);

    return [
      ['emoji' => '🎯', 'text' => "{$target->getName()} gagne en lucidité avec {$this->name} (+{$percentLabel}% blocage/esquive pendant {$turns} tours)."]
    ];
  }

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