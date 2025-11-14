<?php

namespace App\Battle;

use App\Entity\Human;
use App\Utils\ConsoleMessage;
use InvalidArgumentException;

class Combat {
  private int $round = 1;
  /** @var Human[] */
  private array $fighters;
  /** @var array Statistiques de combat */
  private array $stats = [];

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
    
    // Initialiser les statistiques
    foreach ($this->fighters as $fighter) {
      $this->stats[$fighter->getName()] = [
        'damageDealt' => 0.0,
        'damageTaken' => 0.0,
        'kills' => 0,
        'attacks' => 0,
        'blocks' => 0,
        'dodges' => 0,
        'consumablesUsed' => 0,
        'maxHealth' => $fighter->maxHealth,
        'finalHealth' => $fighter->getHealth(),
        'isAlive' => true,
      ];
    }

    // Afficher les barres de vie initiales
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

      // Afficher les barres de vie à la fin de chaque tour
      $aliveFighters = $this->getAliveFighters();
      if (count($aliveFighters) > 1) {
        ConsoleMessage::displayHealthBars($aliveFighters);
      }

      ConsoleMessage::line();
      $this->round++;
    }

    // Mettre à jour les statistiques finales
    foreach ($this->fighters as $fighter) {
      if (isset($this->stats[$fighter->getName()])) {
        $this->stats[$fighter->getName()]['finalHealth'] = $fighter->getHealth();
        $this->stats[$fighter->getName()]['isAlive'] = $fighter->isAlive();
      }
    }

    $survivors = $this->getAliveFighters();
    if (count($survivors) === 1) {
      $winner = array_values($survivors)[0];
      ConsoleMessage::success("{$winner->getName()} remporte le combat !", "🏆");
    } elseif (count($survivors) === 0) {
      ConsoleMessage::error("Tous les combattants sont tombés ! Match nul.", "⚰️");
    }

    // Afficher le résumé détaillé
    $this->displayCombatSummary();
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
      if (isset($this->stats[$attacker->getName()])) {
        $this->stats[$attacker->getName()]['consumablesUsed']++;
      }
      foreach ($consumableResult['messages'] as $message) {
        if (is_array($message) && isset($message['emoji']) && isset($message['text'])) {
          // Détecter le type de message pour la couleur
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
      
      // Statistiques
      if (isset($this->stats[$defender->getName()])) {
        $this->stats[$defender->getName()]['blocks']++;
      }
      if (isset($this->stats[$attacker->getName()])) {
        $this->stats[$attacker->getName()]['attacks']++;
      }
    } elseif ($type === 'dodged') {
      ConsoleMessage::info("{$defender->getName()} esquive l'attaque de {$attacker->getName()}.", "💨");
      ConsoleMessage::out("Aucun dégât reçu.", null, 'gray');
      
      // Statistiques
      if (isset($this->stats[$defender->getName()])) {
        $this->stats[$defender->getName()]['dodges']++;
      }
      if (isset($this->stats[$attacker->getName()])) {
        $this->stats[$attacker->getName()]['attacks']++;
      }
    } elseif ($type === 'damage') {
      $weaponLabel = $weaponName && $weaponName !== 'poings'
        ? "son {$weaponName}"
        : 'ses poings';
      ConsoleMessage::damage("{$attacker->getName()} attaque avec {$weaponLabel} et inflige " . round($damage, 1) . " dégâts.", "⚔️");
      ConsoleMessage::out("{$defender->getName()} : " . max(0, round($defender->health, 1)) . " PV restants", null, 'yellow');
      
      // Statistiques
      if (isset($this->stats[$attacker->getName()])) {
        $this->stats[$attacker->getName()]['damageDealt'] += $damage;
        $this->stats[$attacker->getName()]['attacks']++;
      }
      if (isset($this->stats[$defender->getName()])) {
        $this->stats[$defender->getName()]['damageTaken'] += $damage;
      }
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

      // Statistiques
      if (isset($this->stats[$attacker->getName()])) {
        $this->stats[$attacker->getName()]['kills']++;
      }

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

  /**
   * Affiche un résumé détaillé du combat
   */
  private function displayCombatSummary(): void {
    ConsoleMessage::line();
    ConsoleMessage::separator();
    ConsoleMessage::header("📊 Résumé du combat", "📊");
    ConsoleMessage::separator();

    // Statistiques générales
    ConsoleMessage::info("Durée du combat : {$this->round} tours");
    ConsoleMessage::line();

    // Trier les combattants par dégâts infligés
    $sortedStats = $this->stats;
    uasort($sortedStats, fn($a, $b) => $b['damageDealt'] <=> $a['damageDealt']);

    // Afficher les statistiques de chaque combattant
    foreach ($sortedStats as $name => $stat) {
      $status = $stat['isAlive'] ? '✅ Vivant' : '💀 Éliminé';
      $statusColor = $stat['isAlive'] ? 'bright_green' : 'red';
      
      ConsoleMessage::out("{$name} - {$status}", null, $statusColor);
      ConsoleMessage::out("  ❤️  PV : " . round($stat['finalHealth'], 1) . " / " . round($stat['maxHealth'], 1), null, 'cyan');
      ConsoleMessage::out("  ⚔️  Dégâts infligés : " . round($stat['damageDealt'], 1), null, 'bright_red');
      ConsoleMessage::out("  🛡️  Dégâts reçus : " . round($stat['damageTaken'], 1), null, 'yellow');
      ConsoleMessage::out("  💀 Éliminations : " . $stat['kills'], null, 'magenta');
      ConsoleMessage::out("  🎯 Attaques : " . $stat['attacks'], null, 'blue');
      ConsoleMessage::out("  🛡️  Blocages : " . $stat['blocks'], null, 'cyan');
      ConsoleMessage::out("  💨 Esquives : " . $stat['dodges'], null, 'bright_cyan');
      ConsoleMessage::out("  🧪 Consommables utilisés : " . $stat['consumablesUsed'], null, 'bright_magenta');
      
      if ($stat['attacks'] > 0) {
        $avgDamage = round($stat['damageDealt'] / $stat['attacks'], 1);
        ConsoleMessage::out("  📈 Dégâts moyens par attaque : {$avgDamage}", null, 'gray');
      }
      
      ConsoleMessage::line();
    }

    // Meilleur combattant
    $bestFighter = null;
    $bestScore = -1;
    foreach ($sortedStats as $name => $stat) {
      $score = $stat['damageDealt'] + ($stat['kills'] * 100) + ($stat['blocks'] * 10) + ($stat['dodges'] * 5);
      if ($score > $bestScore) {
        $bestScore = $score;
        $bestFighter = $name;
      }
    }

    if ($bestFighter) {
      ConsoleMessage::separator();
      ConsoleMessage::success("🏅 Meilleur combattant : {$bestFighter}", "🏅");
      ConsoleMessage::separator();
    }
  }
}