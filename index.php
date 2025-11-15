<?php

require __DIR__ . '/autoload.php';

use App\Battle\Combat;
use App\Battle\Team;
use App\Consumable\Food;
use App\Consumable\Potion;
use App\Entity\Human;
use App\Environment\Terrains\ForestTerrain;
use App\Equipment\Armor;
use App\Equipment\Boots;
use App\Equipment\Quiver;
use App\Equipment\Shield;
use App\Equipment\Weapon;
use App\Utils\Seed;
use Random\RandomException;

const SEED = 1313;

try {
  $seed = new Seed(SEED ?: null);
} catch (RandomException $e) {
  echo $e->getMessage();
  exit(1);
}

$wooden_sword = new Weapon(name: 'Épée en bois', damage: $seed->rF(10, 40), range: $seed->r(15, 60) / 10);
$iron_sword = new Weapon(name: 'Épée en fer', damage: $seed->rF(20, 50), range: $seed->r(18, 70) / 10);
$steel_sword = new Weapon(name: 'Épée en acier', damage: $seed->rF(30, 60), range: $seed->r(20, 75) / 10);
$dragon_sword = new Weapon(name: 'Épée du dragon', damage: $seed->rF(50, 80), range: $seed->r(25, 80) / 10);
$short_sword = new Weapon(name: 'Épée courte', damage: $seed->rF(15, 35), range: $seed->r(12, 50) / 10);

$stone_axe = new Weapon(name: 'Hache en pierre', damage: $seed->rF(12, 48), range: $seed->r(12, 48) / 10);
$iron_axe = new Weapon(name: 'Hache en fer', damage: $seed->rF(25, 55), range: $seed->r(15, 55) / 10);
$double_axe = new Weapon(name: 'Hache double', damage: $seed->rF(40, 70), range: $seed->r(20, 60) / 10);
$battle_axe = new Weapon(name: 'Hache de bataille', damage: $seed->rF(45, 75), range: $seed->r(22, 65) / 10);

$wooden_bow = new Weapon(
  name: 'Arc en bois',
  damage: $seed->rF(8, 32),
  range: $seed->r(50, 200) / 10,
  quiver: new Quiver(arrows: $seed->r(5, 15)),
  isMelee: false
);

$composite_bow = new Weapon(
  name: 'Arc composite',
  damage: $seed->rF(15, 40),
  range: $seed->r(80, 250) / 10,
  quiver: new Quiver(arrows: $seed->r(8, 20)),
  isMelee: false
);

$longbow = new Weapon(
  name: 'Arc long',
  damage: $seed->rF(20, 50),
  range: $seed->r(100, 300) / 10,
  quiver: new Quiver(arrows: $seed->r(6, 18)),
  isMelee: false
);

$crossbow = new Weapon(
  name: 'Arbalète',
  damage: $seed->rF(30, 60),
  range: $seed->r(70, 220) / 10,
  quiver: new Quiver(arrows: $seed->r(3, 10)),
  isMelee: false
);

$wooden_spear = new Weapon(name: 'Lance en bois', damage: $seed->rF(18, 42), range: $seed->r(25, 90) / 10);
$iron_spear = new Weapon(name: 'Lance en fer', damage: $seed->rF(28, 52), range: $seed->r(30, 100) / 10);
$javelin = new Weapon(name: 'Javeline', damage: $seed->rF(22, 48), range: $seed->r(35, 110) / 10);

$dagger = new Weapon(name: 'Dague', damage: $seed->rF(8, 25), range: $seed->r(8, 40) / 10);
$poisoned_dagger = new Weapon(name: 'Poignard empoisonné', damage: $seed->rF(12, 30), range: $seed->r(10, 45) / 10);
$assassin_dagger = new Weapon(name: 'Dague d\'assassin', damage: $seed->rF(20, 40), range: $seed->r(12, 50) / 10);

$war_hammer = new Weapon(name: 'Marteau de guerre', damage: $seed->rF(35, 65), range: $seed->r(18, 65) / 10);
$mace = new Weapon(name: 'Masse d\'armes', damage: $seed->rF(30, 58), range: $seed->r(16, 60) / 10);
$mallet = new Weapon(name: 'Maillet', damage: $seed->rF(25, 50), range: $seed->r(14, 55) / 10);

$katana = new Weapon(name: 'Katana', damage: $seed->rF(35, 68), range: $seed->r(22, 75) / 10);
$war_scythe = new Weapon(name: 'Faux de guerre', damage: $seed->rF(40, 72), range: $seed->r(26, 85) / 10);
$barbed_whip = new Weapon(name: 'Fouet barbelé', damage: $seed->rF(15, 38), range: $seed->r(30, 95) / 10);
$trident = new Weapon(name: 'Trident', damage: $seed->rF(28, 55), range: $seed->r(24, 80) / 10);
$club = new Weapon(name: 'Gourdin', damage: $seed->rF(10, 28), range: $seed->r(12, 45) / 10);

$excalibur = new Weapon(name: 'Excalibur', damage: $seed->rF(60, 100), range: $seed->r(30, 90) / 10);
$lightning_blade = new Weapon(name: 'Lame de foudre', damage: $seed->rF(55, 95), range: $seed->r(28, 85) / 10);
$elven_bow = new Weapon(
  name: 'Arc elfique',
  damage: $seed->rF(40, 70),
  range: $seed->r(120, 350) / 10,
  quiver: new Quiver(arrows: $seed->r(10, 25)),
  isMelee: false
);

$shield1 = new Shield(durability: $seed->rF(65, 260), tier: $seed->r(3, 12));
$shield2 = new Shield(durability: $seed->rF(80, 300), tier: $seed->r(4, 10));
$shield3 = new Shield(durability: $seed->rF(50, 200), tier: $seed->r(2, 8));
$shield4 = new Shield(durability: $seed->rF(100, 350), tier: $seed->r(5, 12));

// Predefined armors
$light_armor = new Armor(type: Armor::TYPE_LIGHT);
$iron_armor = new Armor(type: Armor::TYPE_IRON);
$scale_armor = new Armor(type: Armor::TYPE_SCALE);

// Custom armors
$dragon_armor = Armor::createCustom('dragonscale', durability: $seed->rF(180, 220), damageReduction: $seed->r(50, 60) / 100);
$mithril_armor = Armor::createCustom('mithril', durability: $seed->rF(130, 170), damageReduction: $seed->r(40, 50) / 100);
$leather_armor = Armor::createCustom('leather', durability: $seed->rF(40, 60), damageReduction: $seed->r(10, 20) / 100);
$knight_armor = Armor::createCustom('knight', durability: $seed->rF(160, 200), damageReduction: $seed->r(45, 55) / 100);
$shadow_armor = Armor::createCustom('shadow', durability: $seed->rF(50, 70), damageReduction: $seed->r(15, 25) / 100);
$elven_armor = Armor::createCustom('elven', durability: $seed->rF(80, 100), damageReduction: $seed->r(25, 35) / 100);

// Predefined boots
$running_boots = new Boots(type: Boots::TYPE_RUNNING);
$heavy_boots = new Boots(type: Boots::TYPE_HEAVY);
$silent_boots = new Boots(type: Boots::TYPE_SILENT);

// Custom boots
$wind_boots = Boots::createCustom('wind', movementBonus: $seed->r(70, 90) / 100, dodgeBonus: $seed->r(8, 12) / 100);
$tank_boots = Boots::createCustom('tank', movementBonus: -$seed->r(20, 30) / 100, resistanceBonus: $seed->r(15, 21) / 100);
$assassin_boots = Boots::createCustom('assassin', movementBonus: $seed->r(25, 35) / 100, dodgeBonus: $seed->r(20, 30) / 100);
$balanced_boots = Boots::createCustom('balanced', movementBonus: $seed->r(10, 20) / 100, resistanceBonus: $seed->r(5, 11) / 100, dodgeBonus: $seed->r(8, 12) / 100);
$elven_boots = Boots::createCustom('elven', movementBonus: $seed->r(35, 45) / 100, resistanceBonus: $seed->r(3, 7) / 100, dodgeBonus: $seed->r(12, 18) / 100);

$arthur = new Human(
  name: 'Arthur',
  health: $seed->rF(250, 450),
  weapon: $steel_sword,
  secondaryWeapon: $iron_spear,
  shield: $shield1,
  armor: $knight_armor,
  boots: $balanced_boots,
  position: 0
);

$legolas = new Human(
  name: 'Legolas',
  health: $seed->rF(200, 400),
  weapon: $elven_bow,
  secondaryWeapon: $assassin_dagger,
  armor: $elven_armor,
  boots: $elven_boots,
  position: 15
);

$thor = new Human(
  name: 'Thor',
  health: $seed->rF(300, 500),
  weapon: $war_hammer,
  secondaryWeapon: $lightning_blade,
  shield: $shield2,
  armor: $scale_armor,
  boots: $heavy_boots,
  position: 3
);

$robin = new Human(
  name: 'Robin',
  health: $seed->rF(220, 380),
  weapon: $longbow,
  secondaryWeapon: $short_sword,
  armor: $leather_armor,
  boots: $running_boots,
  position: 20
);

$conan = new Human(
  name: 'Conan',
  health: $seed->rF(280, 480),
  weapon: $double_axe,
  secondaryWeapon: $battle_axe,
  shield: $shield3,
  armor: $iron_armor,
  boots: $tank_boots,
  position: 5
);

$samurai = new Human(
  name: 'Hattori',
  health: $seed->rF(240, 420),
  weapon: $katana,
  secondaryWeapon: $crossbow,
  armor: $light_armor,
  boots: $silent_boots,
  position: 8
);

$zeus = new Human(
  name: 'Zeus',
  health: $seed->rF(280, 480),
  weapon: $excalibur,
  secondaryWeapon: $javelin,
  shield: $shield4,
  armor: $mithril_armor,
  boots: $balanced_boots,
  position: 12
);

$valkyrie = new Human(
  name: 'Valkyrie',
  health: $seed->rF(230, 410),
  weapon: $iron_spear,
  secondaryWeapon: $composite_bow,
  armor: $scale_armor,
  boots: $wind_boots,
  position: 18
);

$barbarian = new Human(
  name: 'Grognak',
  health: $seed->rF(300, 500),
  weapon: $war_scythe,
  secondaryWeapon: $dragon_sword,
  armor: $dragon_armor,
  boots: $heavy_boots,
  position: 6
);

$ninja = new Human(
  name: 'Kaito',
  health: $seed->rF(200, 380),
  weapon: $poisoned_dagger,
  secondaryWeapon: $barbed_whip,
  armor: $shadow_armor,
  boots: $assassin_boots,
  position: 22
);

$healingPotion = Potion::healing('Potion de soin');
$ragePotion = Potion::attackBoost('Potion de rage', percent: 0.3, turns: 3);
$concentrationPotion = Potion::evasionBoost('Potion de concentration', percent: 0.2, turns: 3);
$endurancePotion = Potion::endurance("Potion d'endurance", ratio: 0.6, flat: 3);
$antidote = Potion::antidote('Antidote');

$apple = Food::plain('Pomme', healAmount: 10);
$jerky = Food::withAttackBonus('Viande séchée', healAmount: 20, bonusPercent: 0.1, turns: 1);
$staleBread = Food::withMovementBonus('Pain dur', healAmount: 5, movementBonus: 0.5, turns: 2);

if ($legolas->weapon?->getQuiver()) {
  $legolas->weapon->getQuiver()->consumeArrow();
  $legolas->weapon->getQuiver()->consumeArrow();
}

$ninja->applyPoison(6, 3);

// Add consumables to fighters' inventories
// Healing potions
$arthur->addToInventory(Potion::healing('Potion de soin mineure', min: 15, max: 30));
$arthur->addToInventory(Potion::healing('Potion de soin majeure', min: 40, max: 80));
$legolas->addToInventory(Potion::healing('Potion de soin'));
$thor->addToInventory(Potion::healing('Potion de soin'));
$robin->addToInventory(Potion::healing('Potion de soin mineure', min: 10, max: 25));
$conan->addToInventory(Potion::healing('Potion de soin'));
$samurai->addToInventory(Potion::healing('Potion de soin majeure', min: 50, max: 90));
$zeus->addToInventory(Potion::healing('Potion de soin'));
$valkyrie->addToInventory(Potion::healing('Potion de soin'));
$barbarian->addToInventory(Potion::healing('Potion de soin majeure', min: 40, max: 75));
$ninja->addToInventory(Potion::healing('Potion de soin'));

// Rage/attack potions
$arthur->addToInventory(Potion::attackBoost('Potion de rage', percent: 0.5, turns: 4));
$conan->addToInventory(Potion::attackBoost('Élixir de fureur', percent: 0.4, turns: 3));
$thor->addToInventory(Potion::attackBoost('Potion de puissance', percent: 0.3, turns: 3));
$barbarian->addToInventory(Potion::attackBoost('Rage de berserker', percent: 0.6, turns: 3));

// Dodge/concentration potions
$legolas->addToInventory(Potion::evasionBoost('Potion de concentration', percent: 0.3, turns: 4));
$ninja->addToInventory(Potion::evasionBoost('Élixir d\'ombre', percent: 0.4, turns: 3));
$robin->addToInventory(Potion::evasionBoost('Potion d\'agilité', percent: 0.25, turns: 3));

// Endurance potions (ammunition)
$legolas->addToInventory(Potion::endurance("Potion d'endurance", ratio: 0.7, flat: 5));
$robin->addToInventory(Potion::endurance("Élixir de récupération", flat: 3));
$samurai->addToInventory(Potion::endurance("Potion d'endurance", ratio: 0.6, flat: 4));
$valkyrie->addToInventory(Potion::endurance("Potion d'endurance", flat: 2));

// Antidotes
$ninja->addToInventory(Potion::antidote('Antidote universel'));
$arthur->addToInventory(Potion::antidote('Antidote'));
$zeus->addToInventory(Potion::antidote('Antidote'));

// Food
$robin->addToInventory(Food::plain('Pomme', healAmount: 15));
$legolas->addToInventory(Food::withAttackBonus('Viande séchée', healAmount: 25, bonusPercent: 0.15, turns: 2));
$thor->addToInventory(Food::withAttackBonus('Cuissot rôti', healAmount: 30, bonusPercent: 0.2, turns: 2));
$conan->addToInventory(Food::plain('Pain et fromage', healAmount: 20));
$valkyrie->addToInventory(Food::withMovementBonus('Baies énergisantes', healAmount: 12, movementBonus: 0.4, turns: 3));
$barbarian->addToInventory(Food::plain('Ration de survie', healAmount: 18));
$samurai->addToInventory(Food::plain('Boulette de riz', healAmount: 15));

function printGameState(array $weapons, array $shields, array $armors, array $boots, array $humans, Seed $seed): void {
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
  echo "🎲  Graine aléatoire: [{$seed->getSeed()}]\n";
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

  foreach ($weapons as $weapon) {
    $emoji = $weapon->isMelee() ? ($weapon->getName() === 'Hache en pierre' ? "🪓" : "⚔️") : "🏹";
    echo "$emoji  {$weapon->getName()}\n";
    echo "    └ Dégâts: [{$weapon->getDamage()}]\n";
    echo "    └ Portée: [{$weapon->getRange()}]\n";

    if (!$weapon->isMelee() && $weapon->getQuiver() !== null) {
      $arrows = $weapon->getQuiver()->getArrows();
      echo "    └ Flèches: [" . ($arrows === 0 ? "∞" : $arrows) . "]\n";
    }
  }

  foreach ($shields as $shield) {
    echo "🛡️  Bouclier\n";
    echo "    └ Durabilité: [{$shield->getDurability()}]\n";
    echo "    └ Tier: [{$shield->getTier()}]\n";
  }

  foreach ($armors as $armor) {
    echo "🛡️  {$armor->getTypeName()}\n";
    echo "    └ Durabilité: [{$armor->getDurability()}]\n";
    echo "    └ Réduction: [" . ($armor->getDamageReduction() * 100) . "%]\n";
  }

  foreach ($boots as $boot) {
    echo "👢  {$boot->getTypeName()}\n";
    if ($boot->hasMovementBonus()) {
      $sign = $boot->getMovementBonus() >= 0 ? '+' : '';
      echo "    └ Déplacement: [$sign" . ($boot->getMovementBonus() * 100) . "%]\n";
    }
    if ($boot->hasResistanceBonus()) {
      echo "    └ Résistance: [+" . ($boot->getResistanceBonus() * 100) . "%]\n";
    }
    if ($boot->hasDodgeBonus()) {
      echo "    └ Esquive: [+" . ($boot->getDodgeBonus() * 100) . "%]\n";
    }
  }
  
  echo "\n❤️  Combattants\n";
  // Flatten the fighters array (support nested arrays and Team objects)
  $allFighters = [];
  foreach ($humans as $item) {
    if ($item instanceof Team) {
      foreach ($item->getFighters() as $fighter) {
        $allFighters[] = $fighter;
      }
    } elseif (is_array($item)) {
      foreach ($item as $fighter) {
        $allFighters[] = $fighter;
      }
    } else {
      $allFighters[] = $item;
    }
  }
  
  foreach ($allFighters as $human) {
    echo "    └ {$human->getName()}: [{$human->getHealth()} PV]\n";
    if ($human->armor) {
      echo "        • Armure: {$human->armor->getTypeName()}\n";
    }
    if ($human->boots) {
      echo "        • Bottes: {$human->boots->getTypeName()}\n";
    }
  }
}

$all_weapons = [
  $wooden_sword, $iron_sword, $steel_sword, $dragon_sword, $short_sword,
  $stone_axe, $iron_axe, $double_axe, $battle_axe,
  $wooden_bow, $composite_bow, $longbow, $crossbow,
  $wooden_spear, $iron_spear, $javelin,
  $dagger, $poisoned_dagger, $assassin_dagger,
  $war_hammer, $mace, $mallet,
  $katana, $war_scythe, $barbed_whip, $trident, $club,
  $excalibur, $lightning_blade, $elven_bow
];

$all_shields = [$shield1, $shield2, $shield3, $shield4];

$all_armors = [
  $light_armor, $iron_armor, $scale_armor,
  $dragon_armor, $mithril_armor, $leather_armor,
  $knight_armor, $shadow_armor, $elven_armor
];

$all_boots = [
  $running_boots, $heavy_boots, $silent_boots,
  $wind_boots, $tank_boots, $assassin_boots,
  $balanced_boots, $elven_boots
];

$teamFrance = Team::named("Équipe France", $arthur, $legolas, $thor, $robin, $conan);
$teamNordic = Team::named("Équipe Nordique", $zeus, $valkyrie);
$teamChaos = new Team($barbarian, $ninja);

$all_fighters = [
  $teamFrance,
  $teamNordic,
  $teamChaos,
  $samurai
];

printGameState($all_weapons, $all_shields, $all_armors, $all_boots, $all_fighters, $seed);

$combat = new Combat($seed, $all_fighters, new ForestTerrain($seed));
$combat->start();