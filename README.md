# Système de Combat PHP

Un système de combat tour par tour en PHP avec gestion d'armes, de boucliers et de consommables.

## Prérequis

- PHP 8.1 ou supérieur

**Aucune dépendance externe requise !** Le projet utilise un autoloader PSR-4 personnalisé.

## Installation

Aucune installation nécessaire. Clonez simplement le projet :

```bash
git clone https://github.com/SteellgoldStock/CombatPHP
cd php113
```

## Lancer la démo

```bash
php index.php
```

## Note technique

Le projet utilise un autoloader personnalisé (`autoload.php`) qui implémente le standard PSR-4. Composer n'est pas nécessaire car il n'y a aucune dépendance externe.

### Option : Utiliser Composer (facultatif)

Si vous préférez utiliser Composer pour l'autoloading, vous pouvez :

```bash
composer install
```

Puis modifiez `index.php` pour utiliser :

```php
require __DIR__ . '/vendor/autoload.php';  // Au lieu de autoload.php
```

---

## Documentation

### 1. Créer un personnage

Les personnages sont représentés par la classe `Human`.

```php
use App\Entity\Human;

$guerrier = new Human(
    name: 'Arthur',           // Nom du personnage
    health: 350,              // Points de vie
    weapon: $epee,            // Arme principale
    secondaryWeapon: $lance,  // Arme secondaire (optionnel)
    shield: $bouclier,        // Bouclier (optionnel)
    position: 0               // Position sur le champ de bataille
);
```

**Paramètres :**
- `name` : Nom du combattant
- `health` : Points de vie de départ
- `weapon` : Arme principale (peut être `null`)
- `secondaryWeapon` : Arme de secours (optionnel)
- `shield` : Bouclier pour se protéger (optionnel)
- `position` : Position initiale (distance)

---

### 2. Créer des armes

Les armes sont créées avec la classe `Weapon`.

#### Armes de mêlée

```php
use App\Equipment\Weapon;

$epee = new Weapon(
    name: 'Épée en acier',
    damage: 45,      // Dégâts infligés
    range: 3.5       // Portée de l'arme
);

$hache = new Weapon(
    name: 'Hache de bataille',
    damage: 60,
    range: 2.8
);
```

#### Armes à distance

Pour les armes à distance (arcs, arbalètes), il faut un carquois (`Quiver`).

```php
use App\Equipment\Weapon;
use App\Equipment\Quiver;

$arc = new Weapon(
    name: 'Arc long',
    damage: 35,
    range: 20.0,                        // Longue portée
    quiver: new Quiver(arrows: 12),     // Carquois avec 12 flèches
    isMelee: false                      // Arme à distance
);
```

**Paramètres :**
- `name` : Nom de l'arme
- `damage` : Dégâts de base
- `range` : Portée (distance max d'attaque)
- `quiver` : Carquois pour les armes à distance (optionnel)
- `isMelee` : `true` pour mêlée, `false` pour distance (défaut : `true`)

---

### 3. Créer des boucliers

Les boucliers permettent de bloquer les attaques.

```php
use App\Equipment\Shield;

$bouclier = new Shield(
    durability: 150,  // Points de durabilité
    tier: 8           // Niveau (affecte les chances de blocage)
);
```

**Paramètres :**
- `durability` : Points de durabilité (se dégrade à chaque blocage)
- `tier` : Niveau du bouclier (plus le tier est élevé, plus les chances de blocage sont élevées)

**Formule de blocage :** `chance = 20 × tier` (max 100%)

---

### 4. Créer des consommables

#### Potions

Les potions offrent divers effets.

```php
use App\Consumable\Potion;

// Potion de soin
$soin = Potion::healing('Potion de soin', min: 20, max: 60);

// Potion d'attaque
$rage = Potion::attackBoost(
    name: 'Potion de rage',
    percent: 0.3,  // +30% de dégâts
    turns: 3       // Pendant 3 tours
);

// Potion d'esquive
$concentration = Potion::evasionBoost(
    name: 'Potion de concentration',
    percent: 0.2,  // +20% d'esquive
    turns: 3       // Pendant 3 tours
);

// Potion de restauration de munitions
$endurance = Potion::endurance(
    name: "Potion d'endurance",
    ratio: 0.6,    // Restaure 60% des munitions max
    flat: 3        // + 3 munitions fixes
);

// Antidote
$antidote = Potion::antidote('Antidote');  // Supprime les poisons
```

#### Nourriture

La nourriture offre de petites guérisons et des buffs temporaires.

```php
use App\Consumable\Food;

// Nourriture simple
$pomme = Food::plain('Pomme', healAmount: 15);

// Nourriture avec bonus d'attaque
$viande = Food::withAttackBonus(
    name: 'Viande séchée',
    healAmount: 25,
    bonusPercent: 0.15,  // +15% dégâts
    turns: 2             // Pendant 2 tours
);

// Nourriture avec bonus de vitesse
$pain = Food::withMovementBonus(
    name: 'Pain dur',
    healAmount: 10,
    movementBonus: 0.4,  // +40% vitesse
    turns: 2             // Pendant 2 tours
);
```

---

### 5. Inventaire et utilisation

#### Ajouter des consommables à l'inventaire

```php
$guerrier->addToInventory($soin);
$guerrier->addToInventory($rage);
$guerrier->addToInventory($pomme);
```

#### Utiliser un consommable

```php
// Utiliser le premier consommable de l'inventaire
$messages = $guerrier->useConsumable(0);

// Les messages décrivent les effets appliqués
foreach ($messages as $msg) {
    echo $msg['emoji'] . ' ' . $msg['text'] . "\n";
}
```

---

### 6. Système de combat

Le système de combat est géré par la classe `Combat`.

```php
use App\Battle\Combat;

$combattants = [$arthur, $legolas, $thor, $robin];

$combat = new Combat($combattants);
$combat->start();
```

**Fonctionnement :**
- Les combattants attaquent à tour de rôle
- Ils peuvent se déplacer, attaquer ou utiliser des consommables
- Le combat se termine quand il ne reste qu'un seul combattant vivant

---

### 7. Mécaniques avancées

#### Poison

Appliquer un poison à un personnage :

```php
$ninja->applyPoison(
    damagePerTurn: 6,  // Dégâts par tour
    turns: 3           // Durée en tours
);
```

#### Distance et mouvement

Les combattants se déplacent automatiquement vers leurs cibles si elles sont hors de portée.

```php
// Vérifier la distance
$distance = $guerrier->distanceTo($archer);

// Se déplacer manuellement
$guerrier->moveTowards($archer, step: 1.5);
```

#### Buffs temporaires

Les buffs s'appliquent automatiquement et se décrémentent à chaque tour.

```php
// Vérifier les buffs actifs
if ($guerrier->hasAttackBuff()) {
    echo "Le guerrier a un boost d'attaque !\n";
}

if ($guerrier->hasDodgeBuff()) {
    echo "Le guerrier a un boost d'esquive !\n";
}
```

---

## Structure du projet

```
src/
├── battle/
│   ├── Combat.php                 # Gestion du combat
│   └── ConsumableStrategy.php     # IA pour l'utilisation de consommables
├── consumable/
│   ├── Consumable.php             # Classe abstraite
│   ├── Potion.php                 # Potions (soin, rage, etc.)
│   └── Food.php                   # Nourriture
├── entity/
│   └── Human.php                  # Personnage combattant
├── equipment/
│   ├── Weapon.php                 # Armes
│   ├── Shield.php                 # Boucliers
│   └── Quiver.php                 # Carquois (munitions)
└── utils/
    └── ConsoleMessage.php         # Utilitaires d'affichage
```

---

## Exemples rapides

### Créer un archer complet

```php
$arc_elfique = new Weapon(
    name: 'Arc elfique',
    damage: 50,
    range: 25.0,
    quiver: new Quiver(arrows: 20),
    isMelee: false
);

$dague = new Weapon(name: 'Dague', damage: 20, range: 2.0);

$archer = new Human(
    name: 'Legolas',
    health: 300,
    weapon: $arc_elfique,
    secondaryWeapon: $dague,
    position: 15
);

// Ajouter des consommables
$archer->addToInventory(Potion::healing('Potion de soin'));
$archer->addToInventory(Potion::endurance("Potion d'endurance", ratio: 0.7, flat: 5));
$archer->addToInventory(Food::plain('Pomme', healAmount: 15));
```

### Créer un guerrier complet

```php
$epee = new Weapon(name: 'Épée du dragon', damage: 70, range: 4.0);
$lance = new Weapon(name: 'Lance', damage: 45, range: 6.0);
$bouclier = new Shield(durability: 200, tier: 10);

$guerrier = new Human(
    name: 'Arthur',
    health: 400,
    weapon: $epee,
    secondaryWeapon: $lance,
    shield: $bouclier,
    position: 0
);

$guerrier->addToInventory(Potion::healing('Potion de soin majeure', min: 50, max: 90));
$guerrier->addToInventory(Potion::attackBoost('Potion de rage', percent: 0.5, turns: 4));
$guerrier->addToInventory(Potion::antidote('Antidote'));
```