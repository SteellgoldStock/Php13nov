<?php

namespace App\Environment\Terrains;

use App\Environment\Environment;
use App\Utils\Seed;

class DesertTerrain extends Environment {
  public function __construct(?Seed $seed = null) {
    parent::__construct($seed);
    
    $this->name = 'Désert';
    $this->rockyZone = $seed ? $seed->rF(20, 40) : 30.0;
    $this->mudZone = $seed ? $seed->rF(0, 5) : 2.0;
    $this->waterZone = $seed ? $seed->rF(0, 2) : 0.5;
    $this->vegetation = $seed ? $seed->rF(0, 10) : 5.0;
    
    $this->temperature = $seed ? $seed->rF(35, 50) : 42.0;
    $this->humidity = $seed ? $seed->rF(5, 20) : 12.0;
    $this->windSpeed = $seed ? $seed->rF(15, 40) : 27.0;
    $this->visibility = $seed ? $seed->rF(60, 90) : 75.0;
    
    // Effects: exhausting heat, sandstorms affect visibility
    $this->movementPenalty = $seed ? $seed->rF(0.10, 0.20) : 0.15;
    $this->rangedPenalty = $seed ? $seed->rF(0.12, 0.22) : 0.17;
    $this->dodgePenalty = $seed ? $seed->rF(0.08, 0.18) : 0.13;
    $this->staminaDrain = $seed ? $seed->rF(0.05, 0.10) : 0.07;
  }
}

