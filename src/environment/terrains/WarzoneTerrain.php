<?php

namespace App\Environment\Terrains;

use App\Environment\Environment;
use App\Utils\Seed;

class WarzoneTerrain extends Environment {
  public function __construct(?Seed $seed = null) {
    parent::__construct($seed);
    
    $this->name = 'Zone de guerre';
    $this->rockyZone = $seed ? $seed->rF(30, 50) : 40.0;
    $this->mudZone = $seed ? $seed->rF(40, 60) : 50.0;
    $this->waterZone = $seed ? $seed->rF(5, 15) : 10.0;
    $this->vegetation = $seed ? $seed->rF(5, 20) : 12.0;
    
    $this->temperature = $seed ? $seed->rF(10, 25) : 18.0;
    $this->humidity = $seed ? $seed->rF(40, 70) : 55.0;
    $this->windSpeed = $seed ? $seed->rF(10, 30) : 20.0;
    $this->visibility = $seed ? $seed->rF(40, 70) : 55.0;
    
    // Effects: craters and debris make everything difficult
    $this->movementPenalty = $seed ? $seed->rF(0.25, 0.40) : 0.32;
    $this->rangedPenalty = $seed ? $seed->rF(0.10, 0.20) : 0.15;
    $this->dodgePenalty = $seed ? $seed->rF(0.15, 0.30) : 0.22;
    $this->staminaDrain = $seed ? $seed->rF(0.03, 0.06) : 0.04;
  }
}

