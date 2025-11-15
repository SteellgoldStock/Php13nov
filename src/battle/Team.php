<?php

namespace App\Battle;

use App\Entity\Human;

class Team {
  /** @var Human[] */
  private array $fighters;
  private ?string $name;

  /**
   * Create a team of fighters
   * 
   * @param Human ...$fighters Variable number of fighters (at least one required)
   * @param string|null $name Optional team name
   */
  public function __construct(Human ...$fighters) {
    if (empty($fighters)) {
      throw new \InvalidArgumentException("A team must have at least one fighter");
    }
    
    $this->fighters = $fighters;
    $this->name = null;
  }

  /**
   * Create a named team
   * 
   * @param string $name Team name
   * @param Human ...$fighters Variable number of fighters
   * @return self
   */
  public static function named(string $name, Human ...$fighters): self {
    $team = new self(...$fighters);
    $team->name = $name;
    return $team;
  }

  /**
   * Get all fighters in the team
   * 
   * @return Human[]
   */
  public function getFighters(): array {
    return $this->fighters;
  }

  /**
   * Get the team name
   * 
   * @return string|null
   */
  public function getName(): ?string {
    return $this->name;
  }

  /**
   * Check if the team has a name
   * 
   * @return bool
   */
  public function hasName(): bool {
    return $this->name !== null;
  }

  /**
   * Get the number of fighters in the team
   * 
   * @return int
   */
  public function getSize(): int {
    return count($this->fighters);
  }
}

