<?php

namespace App\Battle;

use App\Consumable\Consumable;
use App\Consumable\Food;
use App\Consumable\Potion;
use App\Entity\Human;

/**
 * Smart strategy for managing consumables in combat
 */
class ConsumableStrategy {
  // Decision thresholds
  private const float CRITICAL_HEALTH_THRESHOLD = 30.0;
  private const float LOW_HEALTH_THRESHOLD = 50.0;
  private const int LOW_AMMO_THRESHOLD = 3;

  /**
   * Analyzes the fighter's situation and decides if they should use a consumable
   *
   * @param Human $fighter The fighter
   * @param Human|null $target The current target (optional for more context)
   * @return array|null Result of consumable usage, or null if nothing is used
   */
  public static function evaluateAndUseConsumable(Human $fighter, ?Human $target = null): ?array {
    $inventory = $fighter->getInventory();

    if (empty($inventory)) {
      return null;
    }

    // Priority 1: Critical emergencies (critical health)
    if ($fighter->getHealth() <= self::CRITICAL_HEALTH_THRESHOLD) {
      $consumableIndex = self::findBestHealingConsumable($inventory, urgency: 'critical');
      if ($consumableIndex !== null) {
        return self::useConsumableAtIndex($fighter, $consumableIndex);
      }
    }

    // Priority 2: Antidote if poisoned
    if ($fighter->getPoisonInfo() !== null) {
      $consumableIndex = self::findAntidote($inventory);
      if ($consumableIndex !== null) {
        return self::useConsumableAtIndex($fighter, $consumableIndex);
      }
    }

    // Priority 3: Ammunition restoration if critical
    $totalAmmo = $fighter->getTotalAmmo();
    if ($totalAmmo <= self::LOW_AMMO_THRESHOLD) {
      $consumableIndex = self::findAmmoRestoration($inventory);
      if ($consumableIndex !== null) {
        return self::useConsumableAtIndex($fighter, $consumableIndex);
      }
    }

    // Priority 4: Moderate healing if low health
    if ($fighter->getHealth() <= self::LOW_HEALTH_THRESHOLD) {
      $consumableIndex = self::findBestHealingConsumable($inventory, urgency: 'moderate');
      if ($consumableIndex !== null) {
        return self::useConsumableAtIndex($fighter, $consumableIndex);
      }
    }

    // Priority 5: Tactical buffs if not already active and in good health
    if ($fighter->getHealth() > self::LOW_HEALTH_THRESHOLD) {
      // Attack buff if not active
      if (!$fighter->hasAttackBuff()) {
        $consumableIndex = self::findAttackBoost($inventory);
        if ($consumableIndex !== null) {
          return self::useConsumableAtIndex($fighter, $consumableIndex);
        }
      }

      // Dodge buff if not active
      if (!$fighter->hasDodgeBuff()) {
        $consumableIndex = self::findEvasionBoost($inventory);
        if ($consumableIndex !== null) {
          return self::useConsumableAtIndex($fighter, $consumableIndex);
        }
      }
    }

    return null;
  }

  /**
   * Finds the best healing consumable
   */
  private static function findBestHealingConsumable(array $inventory, string $urgency = 'moderate'): ?int {
    $bestIndex = null;
    $bestPriority = -1;

    foreach ($inventory as $index => $consumable) {
      $priority = 0;

      if ($consumable instanceof Potion) {
        // Check if it's a healing potion
        $reflection = new \ReflectionClass($consumable);
        $effectProperty = $reflection->getProperty('effect');
        $effect = $effectProperty->getValue($consumable);

        if ($effect === Potion::EFFECT_HEAL) {
          $priority = $urgency === 'critical' ? 100 : 50;
        }
      } elseif ($consumable instanceof Food) {
        // Food also heals, but less priority in emergencies
        $priority = $urgency === 'critical' ? 40 : 60;
      }

      if ($priority > $bestPriority) {
        $bestPriority = $priority;
        $bestIndex = $index;
      }
    }

    return $bestIndex;
  }

  /**
   * Finds an antidote in the inventory
   */
  private static function findAntidote(array $inventory): ?int {
    foreach ($inventory as $index => $consumable) {
      if ($consumable instanceof Potion) {
        $reflection = new \ReflectionClass($consumable);
        $effectProperty = $reflection->getProperty('effect');
        $effect = $effectProperty->getValue($consumable);

        if ($effect === Potion::EFFECT_ANTIDOTE) {
          return $index;
        }
      }
    }

    return null;
  }

  /**
   * Finds an ammunition restoration consumable
   */
  private static function findAmmoRestoration(array $inventory): ?int {
    foreach ($inventory as $index => $consumable) {
      if ($consumable instanceof Potion) {
        $reflection = new \ReflectionClass($consumable);
        $effectProperty = $reflection->getProperty('effect');
        $effect = $effectProperty->getValue($consumable);

        if ($effect === Potion::EFFECT_ENDURANCE) {
          return $index;
        }
      }
    }

    return null;
  }

  /**
   * Finds an attack boost
   */
  private static function findAttackBoost(array $inventory): ?int {
    foreach ($inventory as $index => $consumable) {
      if ($consumable instanceof Potion) {
        $reflection = new \ReflectionClass($consumable);
        $effectProperty = $reflection->getProperty('effect');
        $effect = $effectProperty->getValue($consumable);

        if ($effect === Potion::EFFECT_ATTACK) {
          return $index;
        }
      } elseif ($consumable instanceof Food) {
        // Some foods give attack bonuses
        $reflection = new \ReflectionClass($consumable);
        $bonusProperty = $reflection->getProperty('attackBonusPercent');
        $bonus = $bonusProperty->getValue($consumable);

        if ($bonus > 0) {
          return $index;
        }
      }
    }

    return null;
  }

  /**
   * Finds an evasion boost
   */
  private static function findEvasionBoost(array $inventory): ?int {
    foreach ($inventory as $index => $consumable) {
      if ($consumable instanceof Potion) {
        $reflection = new \ReflectionClass($consumable);
        $effectProperty = $reflection->getProperty('effect');
        $effect = $effectProperty->getValue($consumable);

        if ($effect === Potion::EFFECT_EVASION) {
          return $index;
        }
      } elseif ($consumable instanceof Food) {
        $reflection = new \ReflectionClass($consumable);
        $bonusProperty = $reflection->getProperty('dodgeBonusPercent');
        $bonus = $bonusProperty->getValue($consumable);

        if ($bonus > 0) {
          return $index;
        }
      }
    }

    return null;
  }

  /**
   * Uses a consumable at the given index and returns the messages
   */
  private static function useConsumableAtIndex(Human $fighter, int $index): array {
    $messages = $fighter->useConsumable($index);

    if ($messages === null) {
      return [];
    }

    return [
      'used' => true,
      'messages' => $messages
    ];
  }
}