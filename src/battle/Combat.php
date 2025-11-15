<?php

namespace App\Battle;

use App\Entity\Human;
use App\Utils\ConsoleMessage;
use InvalidArgumentException;

class Combat {
  private int $round = 1;
  /** @var Human[] */
  private array $fighters;

  public function __construct(Human|array $fighters) {
    if ($fighters instanceof Human) {
      $args = func_get_args();
      if (isset($args[1]) && $args[1] instanceof Human) {
        $this->fighters = [$fighters, $args[1]];
      } else {
        throw new InvalidArgumentException("Il faut au moins 2 combattants");
      }
    } elseif ($fighters) {
      if (count($fighters) < 2) {
        throw new InvalidArgumentException("Il faut au moins 2 combattants");
      }

      $this->fighters = array_values($fighters);
    } else {
      throw new InvalidArgumentException("Format invalide");
    }
  }

  public function getAliveFighters(): array {
    return array_filter($this->fighters, fn($f) => $f->isAlive());
  }

  public function start(): void {
    ConsoleMessage::line();

    // Display initial health bars
    ConsoleMessage::displayHealthBars($this->fighters);

    while (count($this->getAliveFighters()) > 1) {
      ConsoleMessage::info("Tour {$this->round}", "⚔️");
      ConsoleMessage::separator();

      $aliveFighters = $this->getAliveFighters();

      foreach ($aliveFighters as $attacker) {
        if (!$attacker->isAlive()) continue;

        $target = $this->findClosestTarget($attacker);
        if ($target === null) break;

        $this->executeRound($attacker, $target);

        if (count($this->getAliveFighters()) <= 1) break;
      }

      // Display health bars at the end of each round
      $aliveFighters = $this->getAliveFighters();
      if (count($aliveFighters) > 1) {
        ConsoleMessage::displayHealthBars($aliveFighters);
      }

      ConsoleMessage::line();
      $this->round++;
    }

    $survivors = $this->getAliveFighters();
    if (count($survivors) === 1) {
      $winner = array_values($survivors)[0];
      ConsoleMessage::success("{$winner->getName()} remporte le combat !", "🏆");
    } elseif (count($survivors) === 0) {
      ConsoleMessage::error("Tous les combattants sont tombés ! Match nul.", "⚰️");
    }
  }

  private function findClosestTarget(Human $attacker): ?Human {
    $aliveFighters = $this->getAliveFighters();
    $closestTarget = null;
    $minDistance = PHP_FLOAT_MAX;

    foreach ($aliveFighters as $potential) {
      if ($potential === $attacker) continue;

      $distance = $attacker->distanceTo($potential);
      if ($distance < $minDistance) {
        $minDistance = $distance;
        $closestTarget = $potential;
      }
    }

    return $closestTarget;
  }

  private function executeRound(Human $attacker, Human $defender): void {
    // AI decides FIRST - can anticipate poison damage and other turn effects
    $consumableResult = ConsumableStrategy::evaluateAndUseConsumable($attacker, $defender);
    if ($consumableResult && isset($consumableResult['messages'])) {
      foreach ($consumableResult['messages'] as $message) {
        if (is_array($message) && isset($message['emoji']) && isset($message['text'])) {
          // Detect message type for color
          $color = null;
          if (str_contains($message['text'], 'soin') || str_contains($message['text'], 'guérit')) {
            $color = 'bright_green';
          } elseif (str_contains($message['text'], 'poison')) {
            $color = 'bright_red';
          } elseif (str_contains($message['text'], 'rage') || str_contains($message['text'], 'attaque')) {
            $color = 'bright_yellow';
          }
          ConsoleMessage::out($message['text'], $message['emoji'], $color);
        } else {
          ConsoleMessage::out($message);
        }
      }
    }

    $turnLogs = $attacker->beginTurn();

    foreach ($turnLogs as $logLine) {
      if (is_array($logLine) && isset($logLine['emoji']) && isset($logLine['text'])) {
        $color = str_contains($logLine['text'], 'poison') ? 'bright_red' : null;
        ConsoleMessage::out($logLine['text'], $logLine['emoji'], $color);
      } else {
        ConsoleMessage::out($logLine);
      }
    }

    if (!$attacker->isAlive()) {
      ConsoleMessage::error("{$attacker->getName()} succombe avant de pouvoir agir.", "☠️");
      return;
    }

    $attackResult = $attacker->attack($defender);
    $type = $attackResult['type'] ?? 'unknown';
    $weaponName = $attackResult['weaponName'] ?? 'poings';
    $ammoLine = $this->describeAmmo($attackResult['ammoRemaining'] ?? null);

    if ($type === 'out_of_range') {
      $reason = $attackResult['reason'] ?? 'distance';
      $shouldMove = $attackResult['shouldMove'] ?? true;
      $before = round($attackResult['distance'] ?? $attacker->distanceTo($defender), 1);

      if ($shouldMove) {
        $attacker->moveTowards($defender);
        $after = round($attacker->distanceTo($defender), 1);

        if ($reason === 'no_ammo' && $weaponName) {
          ConsoleMessage::warning("{$attacker->getName()} n'a plus de munitions pour son {$weaponName} et se rapproche de {$defender->getName()} (distance: {$before} ➤ {$after})", "🚶");
        } else {
          ConsoleMessage::info("{$attacker->getName()} est trop loin pour atteindre {$defender->getName()} (distance: {$before} ➤ {$after})", "🚶");
        }

      } else {
        if ($reason === 'no_ammo' && $weaponName) {
          ConsoleMessage::warning("{$attacker->getName()} n'a plus de munitions pour son {$weaponName} mais reste à distance (distance: {$before})", "⚠️");
        } else {
          ConsoleMessage::warning("{$attacker->getName()} ne peut pas atteindre {$defender->getName()} (distance: {$before})", "⚠️");
        }
      }
      ConsoleMessage::out("(attaque interrompue)", null, 'gray');
      ConsoleMessage::out("Positions ➤ {$attacker->getName()}: " . round($attacker->getPosition(), 1) . " | {$defender->getName()}: " . round($defender->getPosition(), 1), null, 'gray');

      return;
    }

    $damage = $attackResult['damage'] ?? 0.0;
    $shieldDurability = $attackResult['shieldDurability'] ?? null;

    if ($type === 'no_ammo') {
      if ($weaponName) {
        ConsoleMessage::warning("{$attacker->getName()} n'a plus de munitions pour son {$weaponName}.", "⚠️");
      } else {
        ConsoleMessage::warning("{$attacker->getName()} n'a pas réussi à attaquer : aucune munition disponible.", "⚠️");
      }

      if ($ammoLine) {
        ConsoleMessage::out($ammoLine, null, 'yellow');
      }

      ConsoleMessage::out("Positions ➤ {$attacker->getName()}: " . round($attacker->getPosition(), 1) . " | {$defender->getName()}: " . round($defender->getPosition(), 1), null, 'gray');
      return;
    }

    if ($type === 'blocked') {
      ConsoleMessage::info("{$defender->getName()} bloque l'attaque de {$attacker->getName()}.", "🛡️");
      if ($shieldDurability !== null && $shieldDurability > 0) {
        ConsoleMessage::out("Durabilité du bouclier : {$shieldDurability}", null, 'cyan');
      }
      ConsoleMessage::out("Aucun dégât reçu.", null, 'gray');
    } elseif ($type === 'dodged') {
      ConsoleMessage::info("{$defender->getName()} esquive l'attaque de {$attacker->getName()}.", "💨");
      ConsoleMessage::out("Aucun dégât reçu.", null, 'gray');
    } elseif ($type === 'damage') {
      $weaponLabel = $weaponName && $weaponName !== 'poings'
        ? "son {$weaponName}"
        : 'ses poings';
      ConsoleMessage::damage("{$attacker->getName()} attaque avec {$weaponLabel} et inflige " . round($damage, 1) . " dégâts.", "⚔️");
      ConsoleMessage::out("{$defender->getName()} : " . max(0, round($defender->health, 1)) . " PV restants", null, 'yellow');
    } else {
      ConsoleMessage::warning("Résultat d'attaque inattendu ({$type}).", "❓");
    }

    if ($ammoLine) {
      ConsoleMessage::out($ammoLine, null, 'yellow');
    }

    ConsoleMessage::out("Positions ➤ {$attacker->getName()}: " . round($attacker->getPosition(), 1) . " | {$defender->getName()}: " . round($defender->getPosition(), 1), null, 'gray');

    if (!$defender->isAlive()) {
      ConsoleMessage::line();
      ConsoleMessage::error("{$defender->getName()} est éliminé !", "💀");

      $remaining = count($this->getAliveFighters());
      if ($remaining === 1) {
        ConsoleMessage::success("{$attacker->getName()} est le dernier survivant !", "🤴");
      } else {
        ConsoleMessage::info("Il reste {$remaining} combattants en vie.");
      }
    }

  }

  private function describeAmmo(?int $ammo): ?string {
    if ($ammo === null) {
      return null;
    }

    return "Munitions restantes : {$ammo}";
  }
}