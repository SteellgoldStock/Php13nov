<?php

namespace App\Environment\Terrains;

use App\Environment\Environment;
use App\Utils\Seed;

class SwampTerrain extends Environment {
  public function __construct(?Seed $seed = null) {
    parent::__construct($seed);
    
    $this->name = 'Marécage';
    $this->rockyZone = $seed ? $seed->rF(0, 10) : 5.0;
    $this->mudZone = $seed ? $seed->rF(60, 90) : 75.0;
    $this->waterZone = $seed ? $seed->rF(30, 60) : 45.0;
    $this->vegetation = $seed ? $seed->rF(60, 90) : 75.0;
    
    $this->temperature = $seed ? $seed->rF(18, 28) : 23.0;
    $this->humidity = $seed ? $seed->rF(80, 100) : 90.0;
    $this->windSpeed = $seed ? $seed->rF(0, 15) : 8.0;
    $this->visibility = $seed ? $seed->rF(40, 70) : 55.0;
    
    // Effects: very difficult to move, reduced visibility affects dodge and ranged attacks
    $this->movementPenalty = $seed ? $seed->rF(0.30, 0.50) : 0.40;
    $this->rangedPenalty = $seed ? $seed->rF(0.15, 0.30) : 0.22;
    $this->dodgePenalty = $seed ? $seed->rF(0.20, 0.35) : 0.28;
    $this->staminaDrain = $seed ? $seed->rF(0.04, 0.08) : 0.06;
  }
}

