<?php

namespace App\Environment\Terrains;

use App\Environment\Environment;
use App\Utils\Seed;

class ArenaTerrain extends Environment {
  public function __construct(?Seed $seed = null) {
    parent::__construct($seed);
    
    $this->name = 'Arène';
    $this->rockyZone = $seed ? $seed->rF(0, 5) : 2.0;
    $this->mudZone = $seed ? $seed->rF(0, 10) : 5.0;
    $this->waterZone = $seed ? $seed->rF(0, 0) : 0.0;
    $this->vegetation = $seed ? $seed->rF(0, 5) : 2.0;
    
    $this->temperature = $seed ? $seed->rF(20, 30) : 25.0;
    $this->humidity = $seed ? $seed->rF(30, 50) : 40.0;
    $this->windSpeed = $seed ? $seed->rF(0, 10) : 5.0;
    $this->visibility = $seed ? $seed->rF(95, 100) : 98.0;
    
    // Effects: optimal terrain for combat
    $this->movementPenalty = $seed ? $seed->rF(0.0, 0.05) : 0.02;
    $this->rangedPenalty = $seed ? $seed->rF(0.0, 0.05) : 0.02;
    $this->dodgePenalty = $seed ? $seed->rF(0.0, 0.03) : 0.01;
    $this->staminaDrain = $seed ? $seed->rF(0.0, 0.01) : 0.005;
  }
}

