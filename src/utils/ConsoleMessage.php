<?php

namespace App\Utils;

class ConsoleMessage {
  public static function out(string $text, ?string $emoji = null): void {
    if ($emoji !== null) {
      echo "{$emoji} {$text}\n";
    } else {
      $startsWithEmoji = preg_match('/^[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}☠️💧🛡️]/u', $text);
      
      if ($startsWithEmoji) {
        echo "{$text}\n";
      } else {
        echo "    {$text}\n";
      }
    }
  }

  public static function separator(): void {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
  }

  public static function line(): void {
    echo "\n";
  }

  public static function header(string $title, ?string $emoji = null): void {
    self::line();
    self::out($title, $emoji);
  }
}

