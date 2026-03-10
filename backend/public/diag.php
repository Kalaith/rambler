<?php
header('Content-Type: text/plain');

echo "Rambler Diagnostic Tool\n";
echo "======================\n\n";

echo "Current Directory (__DIR__): " . __DIR__ . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "\n";
echo "\nChecking Autoloader Locations:\n";

$locations = [
    'Central (repo root)' => __DIR__ . '/../../../../vendor/autoload.php',
    'Local' => __DIR__ . '/../vendor/autoload.php',
    'Up 2 (legacy)' => __DIR__ . '/../../../vendor/autoload.php',
    'Up 1 (legacy)' => __DIR__ . '/../../vendor/autoload.php',
    'Up 4 (legacy)' => __DIR__ . '/../../../../../vendor/autoload.php',
];

foreach ($locations as $label => $path) {
    $exists = file_exists($path) ? "EXISTS" : "NOT FOUND";
    $realpath = realpath($path);
    echo "[$exists] $label: $path\n";
    if ($exists === "EXISTS") {
        echo "           Realpath: $realpath\n";
    }
}

echo "\nDirectory Listing (Up 2 Levels - potential shared root):\n";
$up2 = realpath(__DIR__ . '/../../');
if ($up2 && is_dir($up2)) {
    $files = scandir($up2);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $type = is_dir($up2 . '/' . $file) ? '[DIR]' : '[FILE]';
        echo "$type $file\n";
    }
} else {
    echo "Could not access parent directory for listing.\n";
}

echo "\nEnvironment Check:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Interface: " . php_sapi_name() . "\n";
