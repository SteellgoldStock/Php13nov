<?php

require './Quiver.php';
require './Weapon.php';
require './Shield.php';
require './Human.php';
require './Combat.php';

const SEED = 31;
$seed = SEED ?: (int)(microtime(true) * 1000000) ^ hexdec(bin2hex(random_bytes(4)));
mt_srand($seed);

$wooden_sword = new Weapon(name: 'Épée en bois', damage: mt_rand(10, 40), range: mt_rand(15, 60) / 10);
$iron_sword = new Weapon(name: 'Épée en fer', damage: mt_rand(20, 50), range: mt_rand(18, 70) / 10);
$steel_sword = new Weapon(name: 'Épée en acier', damage: mt_rand(30, 60), range: mt_rand(20, 75) / 10);
$dragon_sword = new Weapon(name: 'Épée du dragon', damage: mt_rand(50, 80), range: mt_rand(25, 80) / 10);
$short_sword = new Weapon(name: 'Épée courte', damage: mt_rand(15, 35), range: mt_rand(12, 50) / 10);

$stone_axe = new Weapon(name: 'Hache en pierre', damage: mt_rand(12, 48), range: mt_rand(12, 48) / 10);
$iron_axe = new Weapon(name: 'Hache en fer', damage: mt_rand(25, 55), range: mt_rand(15, 55) / 10);
$double_axe = new Weapon(name: 'Hache double', damage: mt_rand(40, 70), range: mt_rand(20, 60) / 10);
$battle_axe = new Weapon(name: 'Hache de bataille', damage: mt_rand(45, 75), range: mt_rand(22, 65) / 10);

$wooden_bow = new Weapon(
  name: 'Arc en bois',
  damage: mt_rand(8, 32),
  range: mt_rand(50, 200) / 10,
  quiver: new Quiver(arrows: mt_rand(5, 15)),
  isMelee: false
);

$composite_bow = new Weapon(
  name: 'Arc composite',
  damage: mt_rand(15, 40),
  range: mt_rand(80, 250) / 10,
  quiver: new Quiver(arrows: mt_rand(8, 20)),
  isMelee: false
);

$longbow = new Weapon(
  name: 'Arc long',
  damage: mt_rand(20, 50),
  range: mt_rand(100, 300) / 10,
  quiver: new Quiver(arrows: mt_rand(6, 18)),
  isMelee: false
);

$crossbow = new Weapon(
  name: 'Arbalète',
  damage: mt_rand(30, 60),
  range: mt_rand(70, 220) / 10,
  quiver: new Quiver(arrows: mt_rand(3, 10)),
  isMelee: false
);

$wooden_spear = new Weapon(name: 'Lance en bois', damage: mt_rand(18, 42), range: mt_rand(25, 90) / 10);
$iron_spear = new Weapon(name: 'Lance en fer', damage: mt_rand(28, 52), range: mt_rand(30, 100) / 10);
$javelin = new Weapon(name: 'Javeline', damage: mt_rand(22, 48), range: mt_rand(35, 110) / 10);

$dagger = new Weapon(name: 'Dague', damage: mt_rand(8, 25), range: mt_rand(8, 40) / 10);
$poisoned_dagger = new Weapon(name: 'Poignard empoisonné', damage: mt_rand(12, 30), range: mt_rand(10, 45) / 10);
$assassin_dagger = new Weapon(name: 'Dague d\'assassin', damage: mt_rand(20, 40), range: mt_rand(12, 50) / 10);

$war_hammer = new Weapon(name: 'Marteau de guerre', damage: mt_rand(35, 65), range: mt_rand(18, 65) / 10);
$mace = new Weapon(name: 'Masse d\'armes', damage: mt_rand(30, 58), range: mt_rand(16, 60) / 10);
$mallet = new Weapon(name: 'Maillet', damage: mt_rand(25, 50), range: mt_rand(14, 55) / 10);

$katana = new Weapon(name: 'Katana', damage: mt_rand(35, 68), range: mt_rand(22, 75) / 10);
$war_scythe = new Weapon(name: 'Faux de guerre', damage: mt_rand(40, 72), range: mt_rand(26, 85) / 10);
$barbed_whip = new Weapon(name: 'Fouet barbelé', damage: mt_rand(15, 38), range: mt_rand(30, 95) / 10);
$trident = new Weapon(name: 'Trident', damage: mt_rand(28, 55), range: mt_rand(24, 80) / 10);
$club = new Weapon(name: 'Gourdin', damage: mt_rand(10, 28), range: mt_rand(12, 45) / 10);

$excalibur = new Weapon(name: 'Excalibur', damage: mt_rand(60, 100), range: mt_rand(30, 90) / 10);
$lightning_blade = new Weapon(name: 'Lame de foudre', damage: mt_rand(55, 95), range: mt_rand(28, 85) / 10);
$elven_bow = new Weapon(
  name: 'Arc elfique',
  damage: mt_rand(40, 70),
  range: mt_rand(120, 350) / 10,
  quiver: new Quiver(arrows: mt_rand(10, 25)),
  isMelee: false
);

$shield1 = new Shield(durability: mt_rand(65, 260), tier: mt_rand(3, 12));
$shield2 = new Shield(durability: mt_rand(80, 300), tier: mt_rand(4, 10));
$shield3 = new Shield(durability: mt_rand(50, 200), tier: mt_rand(2, 8));
$shield4 = new Shield(durability: mt_rand(100, 350), tier: mt_rand(5, 12));

$arthur = new Human(
  name: 'Arthur',
  health: mt_rand(200, 600),
  weapon: $excalibur,
  secondaryWeapon: $iron_spear,
  shield: $shield1,
  position: 0
);

$legolas = new Human(
  name: 'Legolas',
  health: mt_rand(150, 500),
  weapon: $elven_bow,
  secondaryWeapon: $assassin_dagger,
  position: 15
);

$thor = new Human(
  name: 'Thor',
  health: mt_rand(250, 650),
  weapon: $war_hammer,
  secondaryWeapon: $lightning_blade,
  shield: $shield2,
  position: 3
);

$robin = new Human(
  name: 'Robin',
  health: mt_rand(140, 480),
  weapon: $longbow,
  secondaryWeapon: $short_sword,
  position: 20
);

$conan = new Human(
  name: 'Conan',
  health: mt_rand(220, 620),
  weapon: $double_axe,
  secondaryWeapon: $battle_axe,
  shield: $shield3,
  position: 5
);

$samurai = new Human(
  name: 'Hattori',
  health: mt_rand(180, 550),
  weapon: $katana,
  secondaryWeapon: $crossbow,
  position: 8
);

$zeus = new Human(
  name: 'Zeus',
  health: mt_rand(200, 580),
  weapon: $trident,
  secondaryWeapon: $javelin,
  shield: $shield4,
  position: 12
);

$valkyrie = new Human(
  name: 'Valkyrie',
  health: mt_rand(170, 530),
  weapon: $wooden_spear,
  secondaryWeapon: $composite_bow,
  position: 18
);

$barbarian = new Human(
  name: 'Grognak',
  health: mt_rand(240, 640),
  weapon: $war_scythe,
  secondaryWeapon: $mace,
  position: 6
);

$ninja = new Human(
  name: 'Kaito',
  health: mt_rand(130, 450),
  weapon: $dagger,
  secondaryWeapon: $barbed_whip,
  position: 22
);

function printGameState(array $weapons, array $shields, array $humans, int $seed): void {
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

  foreach ($weapons as $weapon) {
    $emoji = $weapon->isMelee() ? ($weapon->getName() === 'Hache en pierre' ? "🪓" : "⚔️") : "🏹";
    echo "{$emoji}  {$weapon->getName()}\n";
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
  
  echo "\n❤️  Combattants\n";
  foreach ($humans as $human) {
    echo "    └ {$human->getName()}: [{$human->getHealth()} PV]\n";
  }

  echo "\n🎲  Graine aléatoire: [{$seed}]\n";
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
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

$all_fighters = [
  $arthur, $legolas, $thor, $robin, $conan,
  $samurai, $zeus, $valkyrie, $barbarian, $ninja
];

printGameState($all_weapons, $all_shields, $all_fighters, $seed);

$combat = new Combat($all_fighters, $seed);
$combat->start();