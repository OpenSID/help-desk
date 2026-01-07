<?php
/**
 * Auto-apply patches after composer install
 * This script fixes the Filament Forms access level issue across all platforms
 */

// Use absolute path based on where this script is located
$baseDir = dirname(dirname(__FILE__)); // Project root
$filePath = $baseDir . '/vendor/filament/forms/src/Concerns/InteractsWithForms.php';

if (!file_exists($filePath)) {
    echo "⚠ Filament forms file not found at: $filePath\n";
    exit(0);
}

$content = file_get_contents($filePath);

// Check if already patched
if (strpos($content, 'public function getFormStatePath') !== false) {
    echo "✓ Filament forms already patched\n";
    exit(0);
}

// Apply patch: change protected to public for getFormStatePath
$original = 'protected function getFormStatePath(): ?string';
$patched = 'public function getFormStatePath(): ?string';

if (strpos($content, $original) === false) {
    echo "⚠ Could not find method to patch\n";
    exit(1);
}

$newContent = str_replace($original, $patched, $content);
file_put_contents($filePath, $newContent);

echo "✓ Filament Forms patch applied successfully\n";
exit(0);
