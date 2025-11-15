<?php

namespace App\Battle;

use App\Entity\Human;
use App\Environment\Environment;
use App\Utils\ConsoleMessage;
use App\Utils\Seed;
use InvalidArgumentException;

class Combat {
  private int $round = 1;
  /** @var Human[] */
  private array $fighters;
  /** @var array Mapping of fighter object_id => team_id to manage teams (unlimited teams supported) */
  private array $teams = [];
  /** @var array Mapping of team_id => team_name (optional) */
  private array $teamNames = [];
  private Environment $environment;
  private Seed $seed;

  /**
   * Creates a new combat instance with the given fighters
   *
   * @param Seed $seed The random number generator
   * @param Team|Human|array $fighters The fighters (Team objects, solo Human, or mixed array)
   * @param Environment|null $environment The combat terrain (default: neutral Environment)
   * @throws InvalidArgumentException If less than 2 fighters are provided
   */
  public function __construct(Seed $seed, Team|Human|array $fighters, ?Environment $environment = null) {
    $this->seed = $seed;
    $this->environment = $environment ?? new Environment($seed);
    if ($fighters instanceof Human) {
      // Single Human (need at least 2 fighters)
      throw new InvalidArgumentException("Il faut au moins 2 combattants");
    } elseif ($fighters instanceof Team) {
      // Single Team (need at least 2 fighters or teams)
      throw new InvalidArgumentException("Il faut au moins 2 combattants ou équipes");
    } elseif (is_array($fighters)) {
      $this->fighters = [];
      $teamId = 0;
      
      // Parse structure and manage teams (supports unlimited teams of any size)
      foreach ($fighters as $item) {
        if ($item instanceof Team) {
          // This is a Team object
          foreach ($item->getFighters() as $fighter) {
            $this->fighters[] = $fighter;
            $this->teams[spl_object_id($fighter)] = $teamId;
          }
          
          // Store team name if it has one
          if ($item->hasName()) {
            $this->teamNames[$teamId] = $item->getName();
          }
          
          $teamId++;
        } elseif (is_array($item)) {
          // Legacy support
          foreach ($item as $fighter) {
            if ($fighter instanceof Human) {
              $this->fighters[] = $fighter;
              $this->teams[spl_object_id($fighter)] = $teamId;
            }
          }
          $teamId++;
        } elseif ($item instanceof Human) {
          // This is a solo fighter
          $this->fighters[] = $item;
          $this->teams[spl_object_id($item)] = $teamId;
          $teamId++;
        }
      }
      
      if (count($this->fighters) < 2) {
        throw new InvalidArgumentException("Il faut au moins 2 combattants");
      }

      $this->fighters = array_values($this->fighters);
    } else {
      throw new InvalidArgumentException("Format invalide");
    }
  }

  /**
   * Returns an array of all fighters that are still alive
   *
   * @return Human[] Array of alive fighters
   */
  public function getAliveFighters(): array {
    return array_filter($this->fighters, fn($f) => $f->isAlive());
  }

  /**
   * Returns the number of alive teams
   *
   * @return int The number of teams with at least one alive fighter
   */
  private function getAliveTeams(): int {
    $aliveTeams = [];
    foreach ($this->getAliveFighters() as $fighter) {
      $teamId = $this->teams[spl_object_id($fighter)] ?? null;
      if ($teamId !== null) {
        $aliveTeams[$teamId] = true;
      }
    }
    return count($aliveTeams);
  }

  /**
   * Checks if two fighters are on the same team
   *
   * @param Human $fighter1 The first fighter
   * @param Human $fighter2 The second fighter
   * @return bool True if both fighters are on the same team, false otherwise
   */
  private function areAllies(Human $fighter1, Human $fighter2): bool {
    $fighterTeamId = $this->teams[spl_object_id($fighter1)] ?? null;
    $otherFighterTeamId = $this->teams[spl_object_id($fighter2)] ?? null;

    return $fighterTeamId !== null && $otherFighterTeamId !== null && $fighterTeamId === $otherFighterTeamId;
  }

  /**
   * Starts the combat and runs until only one team remains or all are eliminated
   *
   * @return void
   */
  public function start(): void {
    ConsoleMessage::line();
    
    // Display environment info
    echo $this->environment->getDescription() . "\n";
    ConsoleMessage::line();

    // Display team composition (supports unlimited teams)
    $this->displayTeams();
    ConsoleMessage::line();

    // Display initial health bars
    ConsoleMessage::displayHealthBars($this->fighters);

    while ($this->getAliveTeams() > 1) {
      ConsoleMessage::info("Tour {$this->round}", "⚔️");
      ConsoleMessage::separator();

      $aliveFighters = $this->getAliveFighters();

      foreach ($aliveFighters as $attacker) {
        if (!$attacker->isAlive()) continue;

        $target = $this->findClosestTarget($attacker);
        if ($target === null) break;

        $this->executeRound($attacker, $target);

        if ($this->getAliveTeams() <= 1) break;
      }

      // Display health bars at the end of each round
      $aliveFighters = $this->getAliveFighters();
      if ($this->getAliveTeams() > 1) {
        ConsoleMessage::displayHealthBars($aliveFighters);
      }

      ConsoleMessage::line();
      $this->round++;
    }

    $survivors = $this->getAliveFighters();
    $aliveTeams = $this->getAliveTeams();
    
    if ($aliveTeams === 1) {
      // One team won
      if (count($survivors) === 1) {
        $winner = array_values($survivors)[0];
        ConsoleMessage::success("{$winner->getName()} remporte le combat !", "🏆");
      } else {
        // Multiple survivors from the same team
        $teamId = $this->teams[spl_object_id($survivors[0])] ?? null;
        $teamMembers = array_map(fn($f) => $f->getName(), $survivors);
        
        // Display team name if it has one
        $teamLabel = isset($this->teamNames[$teamId]) 
          ? $this->teamNames[$teamId] 
          : "L'équipe " . ($teamId + 1);
        
        ConsoleMessage::success("{$teamLabel} remporte le combat ! Survivants: " . implode(", ", $teamMembers), "🏆");
      }
    } elseif (count($survivors) === 0) {
      ConsoleMessage::error("Tous les combattants sont tombés ! Match nul.", "⚰️");
    }
  }

  /**
   * Displays team composition (supports unlimited teams of any size)
   *
   * @return void
   */
  private function displayTeams(): void {
    // Group fighters by team
    $teamGroups = [];
    foreach ($this->fighters as $fighter) {
      $teamId = $this->teams[spl_object_id($fighter)] ?? 0;
      $teamGroups[$teamId][] = $fighter;
    }

    echo "👥 Composition des équipes:\n";
    foreach ($teamGroups as $teamId => $members) {
      if (count($members) === 1) {
        echo "   └ Solo: {$members[0]->getName()}\n";
      } else {
        $names = array_map(fn($f) => $f->getName(), $members);

        // Display team name if it has one
        if (isset($this->teamNames[$teamId])) {
          echo "   └ {$this->teamNames[$teamId]}: " . implode(", ", $names) . "\n";
        } else {
          echo "   └ Équipe " . ($teamId + 1) . ": " . implode(", ", $names) . "\n";
        }
      }
    }
  }

  /**
   * Finds the closest alive target for the given attacker
   *
   * @param Human $attacker The fighter looking for a target
   * @return Human|null The closest target, or null if no valid targets exist
   */
  private function findClosestTarget(Human $attacker): ?Human {
    $aliveFighters = $this->getAliveFighters();
    $closestTarget = null;
    $minDistance = PHP_FLOAT_MAX;

    foreach ($aliveFighters as $potential) {
      if ($potential === $attacker) continue;
      
      // Don't target allies (supports unlimited teams)
      if ($this->areAllies($attacker, $potential)) continue;

      $distance = $attacker->distanceTo($potential);
      if ($distance < $minDistance) {
        $minDistance = $distance;
        $closestTarget = $potential;
      }
    }

    return $closestTarget;
  }

  /**
   * Executes a single round of combat between an attacker and a defender
   *
   * @param Human $attacker The attacking fighter
   * @param Human $defender The defending fighter
   * @return void
   */
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
      $isCritical = $attackResult['isCritical'] ?? false;
      $weaponLabel = $weaponName && $weaponName !== 'poings'
        ? "son {$weaponName}"
        : 'ses poings';
      
      if ($isCritical) {
        ConsoleMessage::damage("{$attacker->getName()} attaque avec {$weaponLabel} et inflige " . round($damage, 1) . " dégâts. 💥 COUP CRITIQUE !", "⚔️");
      } else {
        ConsoleMessage::damage("{$attacker->getName()} attaque avec {$weaponLabel} et inflige " . round($damage, 1) . " dégâts.", "⚔️");
      }
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

  /**
   * Creates a descriptive string for remaining ammunition
   *
   * @param int|null $ammo The amount of ammunition remaining, or null if not applicable
   * @return string|null A formatted string describing the ammo, or null if no ammo info
   */
  private function describeAmmo(?int $ammo): ?string {
    if ($ammo === null) {
      return null;
    }

    return "Munitions restantes : {$ammo}";
  }
}