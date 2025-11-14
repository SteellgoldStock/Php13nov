<?php
/**
 * Custom PSR-4 Autoloader
 * 
 * This file implements a PSR-4 compliant autoloader for the App\ namespace,
 * allowing the project to run without Composer.
 * 
 * Examples:
 * - App\Entity\Human       -> src/entity/Human.php
 * - App\Battle\Combat      -> src/battle/Combat.php
 * - App\Equipment\Weapon   -> src/equipment/Weapon.php
 * 
 * @see https://www.php-fig.org/psr/psr-4/
 */

spl_autoload_register(function ($class) {
    // Main namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace
    $base_dir = __DIR__ . '/src/';
    
    // Check if the class uses our namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Class doesn't use our namespace, pass to next autoloader
        return;
    }
    
    // Get the relative class name (without the App\ prefix)
    $relative_class = substr($class, $len);
    
    // Build the file path
    // Replace namespace backslashes with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, load it
    if (file_exists($file)) {
        require $file;
    }
});
