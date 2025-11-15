<?php

namespace App\Environment;

use App\Utils\Seed;

class Environment {
  protected string $name = 'Environnement neutre';
  
  // Environmental properties (percentage 0-100)
  protected float $rockyZone = 0.0;        // % rocky zone
  protected float $mudZone = 0.0;          // % muddy zone
  protected float $waterZone = 0.0;        // % water zone
  protected float $vegetation = 50.0;       // % vegetation
  
  // Weather conditions
  protected float $temperature = 20.0;      // Temperature in °C
  protected float $humidity = 50.0;         // % humidity
  protected float $windSpeed = 0.0;         // Wind speed (km/h)
  protected float $visibility = 100.0;      // % visibility
  
  // Combat effects
  protected float $movementPenalty = 0.0;   // Movement penalty (0-1)
  protected float $rangedPenalty = 0.0;     // Ranged attack penalty (0-1)
  protected float $dodgePenalty = 0.0;      // Dodge penalty (0-1)
  protected float $staminaDrain = 0.0;      // Stamina drain per turn (0-1)
  
  public function __construct(?Seed $seed = null) {
    // Derived classes can use the seed to randomize their values
  }

  public function getName(): string {
    return $this->name;
  }

  // Zone getters
  public function getRockyZone(): float {
    return $this->rockyZone;
  }

  public function getMudZone(): float {
    return $this->mudZone;
  }

  public function getWaterZone(): float {
    return $this->waterZone;
  }

  public function getVegetation(): float {
    return $this->vegetation;
  }

  // Weather getters
  public function getTemperature(): float {
    return $this->temperature;
  }

  public function getHumidity(): float {
    return $this->humidity;
  }

  public function getWindSpeed(): float {
    return $this->windSpeed;
  }

  public function getVisibility(): float {
    return $this->visibility;
  }

  // Combat effects getters
  public function getMovementPenalty(): float {
    return $this->movementPenalty;
  }

  public function getRangedPenalty(): float {
    return $this->rangedPenalty;
  }

  public function getDodgePenalty(): float {
    return $this->dodgePenalty;
  }

  public function getStaminaDrain(): float {
    return $this->staminaDrain;
  }

  /**
   * Returns a complete description of the environment
   */
  public function getDescription(): string {
    $lines = [];
    $lines[] = "🌍 Terrain: {$this->name}";
    
    // Zones
    if ($this->rockyZone > 0) {
      $lines[] = "   └ Zone rocheuse: " . round($this->rockyZone, 1) . "%";
    }
    if ($this->mudZone > 0) {
      $lines[] = "   └ Zone boueuse: " . round($this->mudZone, 1) . "%";
    }
    if ($this->waterZone > 0) {
      $lines[] = "   └ Zone aquatique: " . round($this->waterZone, 1) . "%";
    }
    if ($this->vegetation != 50.0) {
      $lines[] = "   └ Végétation: " . round($this->vegetation, 1) . "%";
    }
    
    // Weather
    $lines[] = "   └ Température: " . round($this->temperature, 1) . "°C";
    if ($this->humidity != 50.0) {
      $lines[] = "   └ Humidité: " . round($this->humidity, 1) . "%";
    }
    if ($this->windSpeed > 0) {
      $lines[] = "   └ Vent: " . round($this->windSpeed, 1) . " km/h";
    }
    if ($this->visibility != 100.0) {
      $lines[] = "   └ Visibilité: " . round($this->visibility, 1) . "%";
    }
    
    // Combat effects
    if ($this->movementPenalty > 0) {
      $lines[] = "   └ Malus de déplacement: " . round($this->movementPenalty * 100, 1) . "%";
    }
    if ($this->rangedPenalty > 0) {
      $lines[] = "   └ Malus à distance: " . round($this->rangedPenalty * 100, 1) . "%";
    }
    if ($this->dodgePenalty > 0) {
      $lines[] = "   └ Malus d'esquive: " . round($this->dodgePenalty * 100, 1) . "%";
    }
    if ($this->staminaDrain > 0) {
      $lines[] = "   └ Drain d'endurance: " . round($this->staminaDrain * 100, 1) . "%";
    }
    
    return implode("\n", $lines);
  }

  /**
   * Applies terrain effects on movement
   */
  public function applyMovementEffect(float $baseMovement): float {
    return $baseMovement * (1 - $this->movementPenalty);
  }

  /**
   * Applies terrain effects on ranged damage
   */
  public function applyRangedEffect(float $baseDamage): float {
    return $baseDamage * (1 - $this->rangedPenalty);
  }

  /**
   * Applies terrain effects on dodge chance
   */
  public function applyDodgeEffect(float $baseDodgeChance): float {
    return $baseDodgeChance * (1 - $this->dodgePenalty);
  }
}

