<?php

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
    echo "Combattants : " . count($this->fighters) . "\n";
    
    foreach ($this->fighters as $fighter) {
      echo "  • {$fighter->getName()} (PV: {$fighter->health}, position: {$fighter->getPosition()})\n";
    }
    echo "\n";

    while (count($this->getAliveFighters()) > 1) {
      echo "[Tour {$this->round}] ---------------\n";
      
      $aliveFighters = $this->getAliveFighters();
      
      foreach ($aliveFighters as $attacker) {
        if (!$attacker->isAlive()) continue;
        
        $target = $this->findClosestTarget($attacker);
        if ($target === null) break;
        
        $this->executeRound($attacker, $target);

        if (count($this->getAliveFighters()) <= 1) break;
      }

      echo "\n";
      $this->round++;
    }

    $survivors = $this->getAliveFighters();
    if (count($survivors) === 1) {
      $winner = array_values($survivors)[0];
      echo "🏆 {$winner->getName()} remporte le combat !\n";
    } elseif (count($survivors) === 0) {
      echo "⚰️  Tous les combattants sont tombés ! Match nul.\n";
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
          echo "🚶  {$attacker->getName()} n'a plus de munitions pour son {$weaponName} et se rapproche de {$defender->getName()} (distance: {$before} ➤ {$after})\n";
        } else {
          echo "🚶  {$attacker->getName()} est trop loin pour atteindre {$defender->getName()} (distance: {$before} ➤ {$after})\n";
        }

      } else {
        if ($reason === 'no_ammo' && $weaponName) {
          echo "⚠️  {$attacker->getName()} n'a plus de munitions pour son {$weaponName} mais reste à distance (distance: {$before})\n";
        } else {
          echo "⚠️  {$attacker->getName()} ne peut pas atteindre {$defender->getName()} (distance: {$before})\n";
        }
      }
      echo "    (attaque interrompue)\n";
      echo "    Positions ➤ {$attacker->getName()}: " . round($attacker->getPosition(), 1) . " | {$defender->getName()}: " . round($defender->getPosition(), 1) . "\n";

      return;
    }

    $damage = $attackResult['damage'] ?? 0.0;
    $shieldDurability = $attackResult['shieldDurability'] ?? null;

    if ($type === 'no_ammo') {
      if ($weaponName) {
        echo "⚠️  {$attacker->getName()} n'a plus de munitions pour son {$weaponName}.\n";
      } else {
        echo "⚠️  {$attacker->getName()} n'a pas réussi à attaquer : aucune munition disponible.\n";
      }

      if ($ammoLine) {
        echo $ammoLine;
      }

      echo "    Positions ➤ {$attacker->getName()}: " . round($attacker->getPosition(), 1) . " | {$defender->getName()}: " . round($defender->getPosition(), 1) . "\n";
      return;
    }

    if ($type === 'blocked') {
      echo "🛡️  {$defender->getName()} bloque l'attaque de {$attacker->getName()}.\n";
      if ($shieldDurability !== null) {
        echo "    Durabilité du bouclier : {$shieldDurability}\n";
      }
      echo "    Aucun dégât reçu.\n";
    } elseif ($type === 'damage') {
      $weaponLabel = $weaponName && $weaponName !== 'poings'
        ? "son {$weaponName}"
        : 'ses poings';
      echo "⚔️  {$attacker->getName()} attaque avec {$weaponLabel} et inflige " . round($damage, 1) . " dégâts.\n";
      echo "    {$defender->getName()} : " . max(0, round($defender->health, 1)) . " PV restants\n";
    } else {
      echo "❓  Résultat d'attaque inattendu ({$type}).\n";
    }

    if ($ammoLine) {
      echo $ammoLine;
    }

    echo "    Positions ➤ {$attacker->getName()}: " . round($attacker->getPosition(), 1) . " | {$defender->getName()}: " . round($defender->getPosition(), 1) . "\n";

    if (!$defender->isAlive()) {
      echo "\n💀 {$defender->getName()} est éliminé !\n";
      
      $remaining = count($this->getAliveFighters());
      if ($remaining === 1) {
        echo "🤴 {$attacker->getName()} est le dernier survivant !\n";
      } else {
        echo "    Il reste {$remaining} combattants en vie.\n";
      }
    }

  }

  private function describeAmmo(?int $ammo): ?string {
    if ($ammo === null) {
      return null;
    }

    return "    Munitions restantes : {$ammo}\n";
  }
}

