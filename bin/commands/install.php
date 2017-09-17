<?php

use Pac\Command;
use Pac\Console;

return function (array $command) {
    $rootDir = getcwd();
    $pacHomeDir = Console::home() . '/.pacphp';
    $pacCacheDir = "$pacHomeDir/caches";
    $jsonFile = $rootDir . '/pac.json';
    $packageDir = $rootDir . '/php_packages';
    $autoloadFile = $packageDir . '/__init__.php';
    $autoloadTemplate = $rootDir . '/src/__init__.php';

    if (!file_exists($jsonFile)) {
        Console::error('"pac.json" is not exists.');
        return;
    }

    if (!$pacHomeDir && @mkdir($pacCacheDir, 0755, true)) {
        Console::error('"' . $pacCacheDir . '" can not create.');
        return;
    }

    $package = @json_decode(file_get_contents($jsonFile), JSON_OBJECT_AS_ARRAY);

    if (!is_dir($packageDir) && !@mkdir($packageDir, 755, true)) {
        Console::error("Dir \"$packageDir\" can not create");
        return;
    }

    if (!empty($package['dependencies'])) {
        foreach ($package['dependencies'] as $name => $version) {
            $packageCacheDir = "$pacCacheDir/$name";

            // Download the latest
            list($status) = Console::exec("git clone $version $packageCacheDir", ['stdio' => 'pipe']);
            list($_, $hash) = Console::exec("git rev-parse HEAD", ['stdio' => 'pipe', 'cwd' => $packageCacheDir]);

            // TODO: Check shasum

            // Check version
            $version = trim($hash);

            // Check pac.json

            // Check composer.json

            // Check dependency and download

            // Resolve dependency

            // Merge dependency

            // Build autoloader
        }
    }

    $template = file_get_contents($autoloadTemplate);
    file_put_contents($autoloadFile, str_replace('$namespace = [];', '$namespace = ' . var_export($package['namespace'], true) . ';', $template));

    Console::success('Install ok');
};
