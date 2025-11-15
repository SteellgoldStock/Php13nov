# Système de Combat PHP

Un système de combat tour par tour en PHP avec gestion d'armes, de boucliers et de consommables.

## Prérequis

- PHP 8.3 ou supérieur

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

### 1. Système de génération aléatoire (Seed)

Le système `Seed` permet de générer des nombres aléatoires de manière reproductible. En utilisant la même graine (seed), vous obtiendrez toujours les mêmes résultats, ce qui est utile pour déboguer ou rejouer des combats.

```php
use App\Utils\Seed;

// Créer un seed aléatoire
$seed = new Seed();

// Créer un seed spécifique (reproductible)
$seed = new Seed(12345678905);

// Récupérer la valeur du seed utilisé
echo $seed->getSeed(); // Affiche le seed utilisé
```

#### Méthodes disponibles

**Nombre entier aléatoire :**
```php
$value = $seed->r(1, 100); // Nombre entre 1 et 100
```

**Nombre entier arrondi (formaté) :**
```php
$value = $seed->rF(10, 200);
// Arrondi à des multiples selon la plage :
// - < 50 : multiples de 5
// - >= 50 et < 200 : multiples de 10
// - >= 200 : multiples de 20
```

**Nombre décimal aléatoire :**
```php
$value = $seed->rDecimal(0.0, 1.0, 2); // Ex: 0.47 (2 décimales)
$value = $seed->rDecimal(10.5, 20.8, 3); // Ex: 15.234 (3 décimales)
```

#### Utilisation pratique

```php
$seed = new Seed(1234567890); // Seed fixe pour tests reproductibles

// Créer des armes avec des stats aléatoires mais reproductibles
$epee = new Weapon(
    name: 'Épée',
    damage: $seed->rF(30, 60),
    range: $seed->r(20, 75) / 10
);

// Le même seed donnera toujours les mêmes valeurs
echo $seed->getSeed(); // 1234567890
```

---

### 2. Créer un personnage

Les personnages sont représentés par la classe `Human`.

```php
use App\Entity\Human;

$guerrier = new Human(
    name: 'Arthur',           // Nom du personnage
    health: 350,              // Points de vie
    weapon: $epee,            // Arme principale
    secondaryWeapon: $lance,  // Arme secondaire (optionnel)
    shield: $bouclier,        // Bouclier (optionnel)
    armor: $armure,           // Armure (optionnel)
    boots: $bottes,           // Bottes (optionnel)
    inventory: [$potion1, $potion2],  // Inventaire de consommables (optionnel)
    position: 0               // Position sur le champ de bataille
);
```

**Paramètres :**
- `name` : Nom du combattant
- `health` : Points de vie de départ
- `weapon` : Arme principale (peut être `null`)
- `secondaryWeapon` : Arme de secours (optionnel)
- `shield` : Bouclier pour se protéger (optionnel)
- `armor` : Armure pour réduire les dégâts (optionnel)
- `boots` : Bottes pour modifier le déplacement et autres bonus (optionnel)
- `inventory` : Tableau de consommables (optionnel, alternative à `addToInventory()`)
- `position` : Position initiale (distance)

---

### 3. Créer des armes

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

### 4. Créer des boucliers

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

### 5. Créer des armures

Les armures réduisent les dégâts reçus et ont une durabilité qui se dégrade au fil des combats.

```php
use App\Equipment\Armor;

// Armure légère (prédéfinie)
$armure_legere = new Armor(type: Armor::TYPE_LIGHT);
// Durabilité: 40, Réduction: 10%

// Armure de fer (prédéfinie)
$armure_fer = new Armor(type: Armor::TYPE_IRON);
// Durabilité: 80, Réduction: 25%

// Armure d'écaille (prédéfinie)
$armure_ecaille = new Armor(type: Armor::TYPE_SCALE);
// Durabilité: 120, Réduction: 40%
```

#### Armures personnalisées

Vous pouvez créer des armures avec des caractéristiques uniques :

```php
// Armure personnalisée
$armure_dragon = Armor::new(
    name: 'Armure de dragon',
    durability: 200,
    damageReduction: 0.55  // 55% de réduction
);

$armure_mithril = Armor::new(
    name: 'Armure de mithril',
    durability: 150,
    damageReduction: 0.45  // 45% de réduction
);
```

**Paramètres :**
- `type` ou `name` : Type d'armure (constantes TYPE_* ou nom personnalisé)
- `durability` : Points de durabilité (se réduit quand l'armure absorbe des dégâts)
- `damageReduction` : Pourcentage de réduction des dégâts (0.0 à 1.0)

**Types prédéfinis :**
| Type | Durabilité | Réduction |
|------|------------|-----------|
| `TYPE_LIGHT` | 40 | 10% |
| `TYPE_IRON` | 80 | 25% |
| `TYPE_SCALE` | 120 | 40% |

**Mécanisme :**
- L'armure réduit les dégâts reçus selon son pourcentage
- La durabilité diminue proportionnellement aux dégâts absorbés
- Une armure cassée (durabilité ≤ 0) ne protège plus

---

### 6. Créer des bottes

Les bottes offrent différents bonus qui affectent le déplacement, la résistance et l'esquive.

```php
use App\Equipment\Boots;

// Bottes de course (prédéfinies)
$bottes_course = new Boots(type: Boots::TYPE_RUNNING);
// +50% vitesse de déplacement

// Bottes lourdes (prédéfinies)
$bottes_lourdes = new Boots(type: Boots::TYPE_HEAVY);
// -20% vitesse, +10% résistance

// Bottes silencieuses (prédéfinies)
$bottes_silent = new Boots(type: Boots::TYPE_SILENT);
// +20% esquive
```

#### Bottes personnalisées

Créez des bottes avec des bonus multiples :

```php
// Bottes du vent
$bottes_vent = Boots::new(
    name: 'Bottes du vent',
    movementBonus: 0.80,    // +80% vitesse
    dodgeBonus: 0.10        // +10% esquive
);

// Bottes de tank
$bottes_tank = Boots::new(
    name: 'Bottes de tank',
    movementBonus: -0.25,   // -25% vitesse (malus)
    resistanceBonus: 0.18   // +18% résistance
);

// Bottes équilibrées (tous les bonus)
$bottes_balanced = Boots::new(
    name: 'Bottes équilibrées',
    movementBonus: 0.15,    // +15% vitesse
    resistanceBonus: 0.08,  // +8% résistance
    dodgeBonus: 0.10        // +10% esquive
);
```

**Paramètres :**
- `type` ou `name` : Type de bottes (constantes TYPE_* ou nom personnalisé)
- `movementBonus` : Modificateur de vitesse (peut être négatif)
- `resistanceBonus` : Réduction de dégâts supplémentaire (cumulable avec l'armure)
- `dodgeBonus` : Bonus de chance d'esquive

**Types prédéfinis :**
| Type | Déplacement | Résistance | Esquive |
|------|-------------|------------|---------|
| `TYPE_RUNNING` | +50% | 0% | 0% |
| `TYPE_HEAVY` | -20% | +10% | 0% |
| `TYPE_SILENT` | 0% | 0% | +20% |

**Effets :**
- **Bonus de mouvement** : Augmente/diminue la vitesse de déplacement sur le champ de bataille
- **Bonus de résistance** : S'ajoute à la réduction d'armure (ex: armure 30% + bottes 10% = 40% total)
- **Bonus d'esquive** : Augmente les chances d'éviter complètement une attaque

---

### 7. Créer des consommables

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

### 8. Inventaire et utilisation

#### Ajouter des consommables à l'inventaire

**Méthode 1 : Directement dans le constructeur**

```php
$guerrier = new Human(
    name: 'Arthur',
    health: 400,
    weapon: $epee,
    inventory: [
        Potion::healing('Potion de soin'),
        Potion::attackBoost('Potion de rage', percent: 0.5, turns: 4),
        Food::plain('Pomme', healAmount: 15)
    ]
);
```

**Méthode 2 : Avec addToInventory()**

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

### 9. Équipes

Le système d'équipes permet de regrouper plusieurs combattants ensemble. Utile pour organiser des combats par équipe.

```php
use App\Battle\Team;

// Créer une équipe simple
$equipe1 = new Team($guerrier1, $archer1, $mage1);

// Créer une équipe nommée
$equipe_rouge = Team::named('Équipe Rouge', $guerrier1, $archer1);
$equipe_bleue = Team::named('Équipe Bleue', $guerrier2, $mage1, $tank1);

// Équipe sans nom (pour combattants indépendants)
$mercenaires = new Team($ninja, $assassin);
```

**Utilisation avec Combat :**

```php
// Combat avec des équipes et des combattants solo
$combat = new Combat(
    seed: $seed,
    fighters: [
        $equipe_rouge,      // Équipe nommée
        $equipe_bleue,      // Équipe nommée
        $mercenaires,       // Équipe sans nom
        $solo_warrior       // Combattant solo
    ]
);
```

**Méthodes utiles :**

```php
// Récupérer tous les combattants d'une équipe
$combattants = $equipe->getFighters();

// Obtenir le nom de l'équipe
$nom = $equipe->getName(); // string|null

// Vérifier si l'équipe a un nom
if ($equipe->hasName()) {
    echo "C'est l'équipe : " . $equipe->getName();
}

// Obtenir le nombre de combattants
$taille = $equipe->getSize();
```

**Avantages :**
- Organise les combattants de manière logique
- Permet des combats par équipe
- Facilite la gestion de groupes de combattants
- Peut mélanger équipes et combattants solo dans un même combat

---

### 10. Environnement et terrains

Les environnements affectent le déroulement des combats en appliquant des modificateurs sur le mouvement, les attaques à distance, l'esquive et l'endurance.

#### Terrains disponibles

```php
use App\Environment\Terrains\ForestTerrain;
use App\Environment\Terrains\DesertTerrain;
use App\Environment\Terrains\MountainTerrain;
use App\Environment\Terrains\SwampTerrain;
use App\Environment\Terrains\ArenaTerrain;
use App\Environment\Terrains\WarzoneTerrain;

// Créer un terrain (valeurs aléatoires)
$foret = new ForestTerrain();

// Créer un terrain avec seed (reproductible)
$desert = new DesertTerrain($seed);
```

#### Caractéristiques des terrains

**Forêt (ForestTerrain)**
- **Zones** : Végétation dense (70-95%), zones boueuses, rochers
- **Météo** : Température modérée (15-25°C), humidité élevée (60-80%)
- **Effets** :
  - Malus de déplacement : 12-25%
  - Malus attaques à distance : 20-35% (végétation bloque la vue)
  - Malus esquive : 5-15%
  - Drain d'endurance : 1-3%

**Désert (DesertTerrain)**
- **Zones** : Zones rocheuses (20-40%), très peu d'eau et de végétation
- **Météo** : Chaleur extrême (35-50°C), humidité faible (5-20%), vents de sable (15-40 km/h)
- **Effets** :
  - Malus de déplacement : 10-20%
  - Malus attaques à distance : 12-22% (vent et sable)
  - Malus esquive : 8-18%
  - Drain d'endurance : 5-10% (chaleur épuisante)

**Montagne (MountainTerrain)**
- **Zones** : Très rocheux (60-90%), peu de végétation
- **Météo** : Froid (0-15°C), vent fort (20-50 km/h), visibilité réduite
- **Effets** :
  - Malus de déplacement : 20-35% (terrain accidenté)
  - Malus attaques à distance : 15-30% (vent)
  - Malus esquive : 10-20%
  - Drain d'endurance : 3-6% (altitude)

**Marais (SwampTerrain)**
- **Zones** : Zones boueuses (40-70%), eau stagnante (20-50%)
- **Météo** : Humidité extrême (80-95%), brouillard
- **Effets** :
  - Malus de déplacement : 25-40% (le pire terrain)
  - Malus attaques à distance : 18-33% (brouillard)
  - Malus esquive : 15-25%
  - Drain d'endurance : 4-8%

**Arène (ArenaTerrain)**
- **Zones** : Terrain dégagé et plat
- **Météo** : Conditions neutres
- **Effets** : Aucun malus (terrain idéal pour le combat)

**Zone de guerre (WarzoneTerrain)**
- **Zones** : Cratères, ruines, débris
- **Météo** : Variable, souvent enfumée
- **Effets** :
  - Malus de déplacement : 15-25%
  - Malus attaques à distance : 10-20%
  - Malus esquive : 12-18%
  - Drain d'endurance : 2-5%

#### Utilisation

```php
// Créer un combat avec environnement
$terrain = new ForestTerrain($seed);
$combat = new Combat($seed, $fighters, $terrain);

// Afficher les détails du terrain
echo $terrain->getDescription();

// Exemple de sortie :
// 🌍 Terrain: Forêt
//    └ Zone rocheuse: 12.0%
//    └ Zone boueuse: 20.0%
//    └ Végétation: 85.0%
//    └ Température: 20.0°C
//    └ Malus de déplacement: 18.0%
//    └ Malus à distance: 28.0%
```

#### Méthodes de l'environnement

```php
// Propriétés environnementales
$terrain->getRockyZone();       // % de zones rocheuses
$terrain->getMudZone();         // % de zones boueuses
$terrain->getWaterZone();       // % de zones aquatiques
$terrain->getVegetation();      // % de végétation

// Conditions météo
$terrain->getTemperature();     // Température en °C
$terrain->getHumidity();        // % d'humidité
$terrain->getWindSpeed();       // Vitesse du vent (km/h)
$terrain->getVisibility();      // % de visibilité

// Effets de combat
$terrain->getMovementPenalty(); // Malus de déplacement (0-1)
$terrain->getRangedPenalty();   // Malus attaques à distance (0-1)
$terrain->getDodgePenalty();    // Malus d'esquive (0-1)
$terrain->getStaminaDrain();    // Drain d'endurance par tour (0-1)
```

**Impact stratégique :**
- Les **archers** sont désavantagés en forêt et montagne
- Les **guerriers de mêlée** souffrent moins des malus à distance
- Tous les combattants sont affectés par le drain d'endurance
- L'**arène** est le terrain le plus équitable

---

### 11. Système de combat

Le système de combat est géré par la classe `Combat`.

#### Combat basique

```php
use App\Battle\Combat;

// Combattants individuels
$combattants = [$arthur, $legolas, $thor, $robin];

$combat = new Combat($combattants);
$combat->start();
```

#### Combat avec Seed (reproductible)

```php
use App\Utils\Seed;

$seed = new Seed(1234567890); // Combat reproductible
$combat = new Combat($seed, $combattants);
$combat->start();
```

#### Combat avec Environnement

```php
use App\Environment\Terrains\ForestTerrain;

$seed = new Seed(1234567890);
$terrain = new ForestTerrain($seed);

$combat = new Combat($seed, $combattants, $terrain);
$combat->start();
```

#### Combat avec Équipes

```php
use App\Battle\Team;

$equipe_rouge = Team::named('Équipe Rouge', $arthur, $legolas);
$equipe_bleue = Team::named('Équipe Bleue', $thor, $robin);

$combat = new Combat($seed, [$equipe_rouge, $equipe_bleue], $terrain);
$combat->start();
```

**Signature complète :**

```php
$combat = new Combat(
    seed: $seed,           // Seed|array - Seed ou tableau de combattants (rétrocompat)
    fighters: $fighters,   // array|null - Combattants/équipes (optionnel si seed est un tableau)
    environment: $terrain  // Environment|null - Environnement (optionnel)
);
```

**Fonctionnement :**
- Les combattants attaquent à tour de rôle
- Ils peuvent se déplacer, attaquer ou utiliser des consommables automatiquement
- Le système d'IA utilise les consommables de manière stratégique
- Les effets d'environnement s'appliquent à tous les combattants
- Le combat se termine quand il ne reste qu'un seul combattant (ou une équipe) vivant

---

### 12. Mécaniques avancées

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
│   ├── ConsumableStrategy.php     # IA pour l'utilisation de consommables
│   └── Team.php                   # Système d'équipes
├── consumable/
│   ├── Consumable.php             # Classe abstraite
│   ├── Potion.php                 # Potions (soin, rage, endurance, etc.)
│   └── Food.php                   # Nourriture
├── entity/
│   └── Human.php                  # Personnage combattant
├── environment/
│   ├── Environment.php            # Classe de base pour les environnements
│   └── terrains/
│       ├── ArenaTerrain.php       # Arène (neutre)
│       ├── DesertTerrain.php      # Désert
│       ├── ForestTerrain.php      # Forêt
│       ├── MountainTerrain.php    # Montagne
│       ├── SwampTerrain.php       # Marais
│       └── WarzoneTerrain.php     # Zone de guerre
├── equipment/
│   ├── Weapon.php                 # Armes (mêlée et à distance)
│   ├── Shield.php                 # Boucliers
│   ├── Armor.php                  # Armures
│   ├── Boots.php                  # Bottes
│   └── Quiver.php                 # Carquois (munitions)
└── utils/
    ├── ConsoleMessage.php         # Utilitaires d'affichage console
    └── Seed.php                   # Génération aléatoire reproductible
```

---

## Exemples rapides

### Créer un archer complet

```php
use App\Entity\Human;
use App\Equipment\Weapon;
use App\Equipment\Quiver;
use App\Equipment\Armor;
use App\Equipment\Boots;
use App\Consumable\Potion;
use App\Consumable\Food;

// Armes
$arc_elfique = new Weapon(
    name: 'Arc elfique',
    damage: 50,
    range: 25.0,
    quiver: new Quiver(arrows: 20),
    isMelee: false
);

$dague = new Weapon(name: 'Dague', damage: 20, range: 2.0);

// Équipement
$armure_elfe = Armor::new(
    name: 'Armure elfique',
    durability: 90,
    damageReduction: 0.30
);

$bottes_elfe = Boots::new(
    name: 'Bottes elfiques',
    movementBonus: 0.40,
    resistanceBonus: 0.05,
    dodgeBonus: 0.15
);

// Créer l'archer avec inventaire
$archer = new Human(
    name: 'Legolas',
    health: 300,
    weapon: $arc_elfique,
    secondaryWeapon: $dague,
    armor: $armure_elfe,
    boots: $bottes_elfe,
    inventory: [
        Potion::healing('Potion de soin'),
        Potion::endurance("Potion d'endurance", ratio: 0.7, flat: 5),
        Potion::evasionBoost('Potion de concentration', percent: 0.3, turns: 4),
        Food::plain('Pomme', healAmount: 15)
    ],
    position: 15
);
```

### Créer un guerrier complet

```php
use App\Entity\Human;
use App\Equipment\Weapon;
use App\Equipment\Shield;
use App\Equipment\Armor;
use App\Equipment\Boots;
use App\Consumable\Potion;

// Armes
$epee = new Weapon(name: 'Épée du dragon', damage: 70, range: 4.0);
$lance = new Weapon(name: 'Lance', damage: 45, range: 6.0);

// Équipement défensif
$bouclier = new Shield(durability: 200, tier: 10);

$armure_chevalier = Armor::new(
    name: 'Armure de chevalier',
    durability: 180,
    damageReduction: 0.50
);

$bottes_equilibrees = Boots::new(
    name: 'Bottes équilibrées',
    movementBonus: 0.15,
    resistanceBonus: 0.10,
    dodgeBonus: 0.08
);

// Créer le guerrier avec inventaire
$guerrier = new Human(
    name: 'Arthur',
    health: 400,
    weapon: $epee,
    secondaryWeapon: $lance,
    shield: $bouclier,
    armor: $armure_chevalier,
    boots: $bottes_equilibrees,
    inventory: [
        Potion::healing('Potion de soin majeure', min: 50, max: 90),
        Potion::attackBoost('Potion de rage', percent: 0.5, turns: 4),
        Potion::antidote('Antidote')
    ],
    position: 0
);
```

### Combat complet avec tout

```php
use App\Utils\Seed;
use App\Battle\Combat;
use App\Battle\Team;
use App\Environment\Terrains\ForestTerrain;

// Seed pour reproductibilité
$seed = new Seed(1234567890);

// Créer l'environnement
$terrain = new ForestTerrain($seed);

// Créer des équipes
$equipe_heros = Team::named('Héros', $guerrier, $archer);
$equipe_ennemis = Team::named('Ennemis', $orc1, $orc2, $goblin);

// Lancer le combat
$combat = new Combat($seed, [$equipe_heros, $equipe_ennemis], $terrain);
$combat->start();

// Le combat affichera :
// - Les détails du terrain et ses effets
// - Les équipes et leurs membres
// - Chaque action de combat avec les effets d'armure, bottes, etc.
// - L'utilisation intelligente des consommables
// - Le gagnant final
```