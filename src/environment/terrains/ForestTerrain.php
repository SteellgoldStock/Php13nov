<?php

namespace App\Environment\Terrains;

use App\Environment\Environment;
use App\Utils\Seed;

class ForestTerrain extends Environment {
  public function __construct(?Seed $seed = null) {
    parent::__construct($seed);
    
    $this->name = 'Forêt';
    $this->rockyZone = $seed ? $seed->rF(5, 20) : 12.0;
    $this->mudZone = $seed ? $seed->rF(10, 30) : 20.0;
    $this->waterZone = $seed ? $seed->rF(5, 15) : 10.0;
    $this->vegetation = $seed ? $seed->rF(70, 95) : 85.0;
    
    $this->temperature = $seed ? $seed->rF(15, 25) : 20.0;
    $this->humidity = $seed ? $seed->rF(60, 80) : 70.0;
    $this->windSpeed = $seed ? $seed->rF(5, 15) : 10.0;
    $this->visibility = $seed ? $seed->rF(50, 75) : 62.0;
    
    // Effects: dense vegetation hinders movement and visibility
    $this->movementPenalty = $seed ? $seed->rF(0.12, 0.25) : 0.18;
    $this->rangedPenalty = $seed ? $seed->rF(0.20, 0.35) : 0.28;
    $this->dodgePenalty = $seed ? $seed->rF(0.05, 0.15) : 0.10;
    $this->staminaDrain = $seed ? $seed->rF(0.01, 0.03) : 0.02;
  }
}

