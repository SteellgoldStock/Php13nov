<?php

namespace App\Utils;

class ConsoleMessage {
  // Codes de couleurs ANSI
  private const COLOR_RESET = "\033[0m";
  private const COLOR_RED = "\033[31m";
  private const COLOR_GREEN = "\033[32m";
  private const COLOR_YELLOW = "\033[33m";
  private const COLOR_BLUE = "\033[34m";
  private const COLOR_MAGENTA = "\033[35m";
  private const COLOR_CYAN = "\033[36m";
  private const COLOR_WHITE = "\033[37m";
  private const COLOR_BRIGHT_RED = "\033[91m";
  private const COLOR_BRIGHT_GREEN = "\033[92m";
  private const COLOR_BRIGHT_YELLOW = "\033[93m";
  private const COLOR_BRIGHT_BLUE = "\033[94m";
  private const COLOR_BRIGHT_MAGENTA = "\033[95m";
  private const COLOR_BRIGHT_CYAN = "\033[96m";
  private const COLOR_BRIGHT_WHITE = "\033[97m";
  private const COLOR_GRAY = "\033[90m";

  // Styles
  private const STYLE_BOLD = "\033[1m";
  private const STYLE_DIM = "\033[2m";

  public static function out(string $text, ?string $emoji = null, ?string $color = null): void {
    $colorCode = $color ? self::getColorCode($color) : '';
    $reset = $color ? self::COLOR_RESET : '';

    if ($emoji !== null) {
      echo "{$colorCode}{$emoji} {$text}{$reset}\n";
    } else {
      $startsWithEmoji = preg_match('/^[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}☠️💧🛡️]/u', $text);

      if ($startsWithEmoji) {
        echo "{$colorCode}{$text}{$reset}\n";
      } else {
        echo "    {$colorCode}{$text}{$reset}\n";
      }
    }
  }

  public static function success(string $text, ?string $emoji = null): void {
    self::out($text, $emoji, 'green');
  }

  public static function error(string $text, ?string $emoji = null): void {
    self::out($text, $emoji, 'red');
  }

  public static function warning(string $text, ?string $emoji = null): void {
    self::out($text, $emoji, 'yellow');
  }

  public static function info(string $text, ?string $emoji = null): void {
    self::out($text, $emoji, 'cyan');
  }

  public static function damage(string $text, ?string $emoji = null): void {
    self::out($text, $emoji, 'bright_red');
  }

  public static function heal(string $text, ?string $emoji = null): void {
    self::out($text, $emoji, 'bright_green');
  }

  public static function separator(): void {
    echo self::COLOR_GRAY . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . self::COLOR_RESET . "\n";
  }

  public static function line(): void {
    echo "\n";
  }

  public static function header(string $title, ?string $emoji = null): void {
    self::line();
    self::out($title, $emoji, 'bright_white');
  }

  /**
   * Affiche une barre de vie visuelle
   * @param string $name Nom du combattant
   * @param float $currentHealth Points de vie actuels
   * @param float $maxHealth Points de vie maximum
   * @param int $barLength Longueur de la barre (par défaut 30)
   * @param int $nameWidth Largeur maximale pour le nom (pour l'alignement)
   */
  public static function healthBar(string $name, float $currentHealth, float $maxHealth, int $barLength = 30, int $nameWidth = 0): void {
    $percentage = $maxHealth > 0 ? max(0, min(100, ($currentHealth / $maxHealth) * 100)) : 0;
    $filled = (int)round(($percentage / 100) * $barLength);
    $empty = $barLength - $filled;

    // Déterminer la couleur selon le pourcentage
    if ($percentage >= 70) {
      $barColor = self::COLOR_BRIGHT_GREEN;
    } elseif ($percentage >= 40) {
      $barColor = self::COLOR_BRIGHT_YELLOW;
    } elseif ($percentage >= 20) {
      $barColor = self::COLOR_YELLOW;
    } else {
      $barColor = self::COLOR_BRIGHT_RED;
    }

    $bar = str_repeat('█', $filled) . str_repeat('░', $empty);
    $healthText = sprintf("%.1f / %.1f", max(0, $currentHealth), $maxHealth);
    $percentageText = sprintf("%.0f%%", $percentage);

    // Aligner le nom à gauche avec la largeur maximale
    $paddedName = $nameWidth > 0 ? str_pad($name, $nameWidth, ' ', STR_PAD_RIGHT) : $name;

    echo sprintf(
      "    %s%s%s │%s%s%s│ %s %s%s%s\n",
      self::COLOR_BRIGHT_WHITE,
      $paddedName,
      self::COLOR_RESET,
      $barColor,
      $bar,
      self::COLOR_RESET,
      $healthText,
      self::COLOR_GRAY,
      $percentageText,
      self::COLOR_RESET
    );
  }

  /**
   * Affiche les barres de vie de tous les combattants
   * @param array $fighters Tableau de combattants (Human[])
   */
  public static function displayHealthBars(array $fighters): void {
    self::line();
    echo self::COLOR_BRIGHT_CYAN . "    ❤️  État des combattants" . self::COLOR_RESET . "\n";
    
    // Calculer la largeur maximale des noms pour l'alignement
    $maxNameWidth = 0;
    foreach ($fighters as $fighter) {
      $nameLength = mb_strlen($fighter->getName(), 'UTF-8');
      if ($nameLength > $maxNameWidth) {
        $maxNameWidth = $nameLength;
      }
    }
    
    foreach ($fighters as $fighter) {
      // Calculer les PV max (approximatif si on ne les stocke pas)
      // On va utiliser une estimation basée sur les PV actuels si on n'a pas accès au max
      // Pour l'instant, on va stocker le max au début du combat
      $maxHealth = $fighter->maxHealth ?? $fighter->getHealth();
      self::healthBar($fighter->getName(), $fighter->getHealth(), $maxHealth, 30, $maxNameWidth);
    }
    self::line();
  }

  private static function getColorCode(string $color): string {
    return match ($color) {
      'red' => self::COLOR_RED,
      'green' => self::COLOR_GREEN,
      'yellow' => self::COLOR_YELLOW,
      'blue' => self::COLOR_BLUE,
      'magenta' => self::COLOR_MAGENTA,
      'cyan' => self::COLOR_CYAN,
      'white' => self::COLOR_WHITE,
      'bright_red' => self::COLOR_BRIGHT_RED,
      'bright_green' => self::COLOR_BRIGHT_GREEN,
      'bright_yellow' => self::COLOR_BRIGHT_YELLOW,
      'bright_blue' => self::COLOR_BRIGHT_BLUE,
      'bright_magenta' => self::COLOR_BRIGHT_MAGENTA,
      'bright_cyan' => self::COLOR_BRIGHT_CYAN,
      'bright_white' => self::COLOR_BRIGHT_WHITE,
      'gray' => self::COLOR_GRAY,
      default => '',
    };
  }
}