<?php

require './Human.php';
require './Weapon.php';
require './Shield.php';

$sword = new Weapon(name: 'Épée en bois', damage: 10);
$axe = new Weapon(name: 'Hache en pierre', damage: 7);

$shield = new Shield(durability: 65, tier: 3);

$steve = new Human(name: 'Steve', health: 150, weapon: $sword, shield: $shield);
$alex = new Human(name: 'Alex', health: 135, weapon: $axe);

echo "=== DÉBUT DU COMBAT ===\n";
echo "{$steve->getName()} (PV: {$steve->health}) VS {$alex->getName()} (PV: {$alex->health})\n\n";

$round = 1;

function combatRound(Human $attacker, Human $defender): bool {
  $damage = $attacker->attack($defender);
  if ($damage === false) return true;

  echo "⚔️  {$attacker->getName()} frappe avec son {$attacker->weapon->getName()} et inflige " . round($damage, 1) . " points de dégâts.\n";
  echo "    {$defender->getName()} : " . max(0, round($defender->health, 1)) . " PV restants\n";

  if (!$defender->isAlive()) {
    echo "\n💀 {$defender->getName()} est vaincu !";
    echo "\n🤴 {$attacker->getName()} est vainqueur !\n";
    return false;
  }

  return true;
}

while ($steve->isAlive() && $alex->isAlive()) {
  echo "--- Tour {$round} ---\n";

  if (!combatRound($steve, $alex)) break;
  if (!combatRound($alex, $steve)) break;

  echo "\n";
  $round++;
}
