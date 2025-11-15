<?php

namespace App\Environment\Terrains;

use App\Environment\Environment;
use App\Utils\Seed;

class MountainTerrain extends Environment {
  public function __construct(?Seed $seed = null) {
    parent::__construct($seed);
    
    $this->name = 'Terrain montagneux';
    $this->rockyZone = $seed ? $seed->rF(60, 90) : 75.0;
    $this->mudZone = $seed ? $seed->rF(0, 10) : 5.0;
    $this->waterZone = $seed ? $seed->rF(0, 5) : 2.0;
    $this->vegetation = $seed ? $seed->rF(10, 30) : 20.0;
    
    $this->temperature = $seed ? $seed->rF(-5, 10) : 5.0;
    $this->humidity = $seed ? $seed->rF(30, 60) : 45.0;
    $this->windSpeed = $seed ? $seed->rF(20, 50) : 35.0;
    $this->visibility = $seed ? $seed->rF(70, 100) : 85.0;
    
    // Effects: difficult to move, wind affects ranged attacks
    $this->movementPenalty = $seed ? $seed->rF(0.15, 0.35) : 0.25;
    $this->rangedPenalty = $seed ? $seed->rF(0.10, 0.25) : 0.18;
    $this->dodgePenalty = $seed ? $seed->rF(0.05, 0.15) : 0.10;
    $this->staminaDrain = $seed ? $seed->rF(0.02, 0.05) : 0.03;
  }
}

