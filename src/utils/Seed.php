<?php

namespace App\Utils;

use Random\RandomException;

/**
 * Class to manage random number generation with seed
 */
class Seed {
  private int $seed;

  /**
   * @param int|null $seed The seed to use. If null, generate a random seed
   * @throws RandomException
   */
  public function __construct(?int $seed = null) {
    $this->seed = $seed ?? (int)(microtime(true) * 1000000) ^ hexdec(bin2hex(random_bytes(4)));
    mt_srand($this->seed);
  }

  /**
   * Generate a random number between min and max
   *
   * @param int $min Minimum value
   * @param int $max Maximum value
   * @return int
   */
  public function r(int $min, int $max): int {
    return mt_rand($min, $max);
  }

  /**
   * Generate a random number "rounded" between min and max
   * The numbers generated are rounded to proper multiples:
   * - < 50 : multiples of 5
   * - >= 50 and < 200 : multiples of 10
   * - >= 200 : multiples of 20
   *
   * @param int $min Minimum value
   * @param int $max Maximum value
   * @return int
   */
  public function rF(int $min, int $max): int {
    $value = mt_rand($min, $max);

    // Determine the rounding step based on the range
    if ($max < 50) {
      $step = 5;
    } elseif ($max < 200) {
      $step = 10;
    } else {
      $step = 20;
    }

    // Round to the nearest multiple
    return (int)(round($value / $step) * $step);
  }

  /**
   * Generate a random decimal number between min and max
   *
   * @param float $min Minimum value
   * @param float $max Maximum value
   * @param int $decimals Number of decimals (default: 2)
   * @return float
   */
  public function rDecimal(float $min, float $max, int $decimals = 2): float {
    $multiplier = pow(10, $decimals);
    $minInt = (int)($min * $multiplier);
    $maxInt = (int)($max * $multiplier);

    return mt_rand($minInt, $maxInt) / $multiplier;
  }

  /**
   * Return the seed used
   *
   * @return int
   */
  public function getSeed(): int {
    return $this->seed;
  }
}