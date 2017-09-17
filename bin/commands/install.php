<?php

use Pac\Console;
use Pac\Process;

return function (array $command) {
    $rootDir = getcwd();
    $pacHomeDir = Process::home() . '/.pacphp';
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
        foreach ($package['dependencies'] as $name => $packageVersion) {
            $packageCacheDir = "$pacCacheDir/$name";
            $packageCacheVersionDir = $gitUrl = $gitVersion = null;

            if (preg_match("/^(\~|\^)\d\.\d\.\d$/", $packageVersion) > 0) {
                // Find right version
                $packageType = 'dist';
                $rightVersion = '1.0.0';
                $packageCacheVersionDir = "$packageCacheDir/$rightVersion";
            } else {
                list($gitUrl, $gitVersion) = explode('#' , $packageVersion);
                $packageType = 'git';

                if (!$gitVersion) {
                    $gitVersion = 'master';
                }

                if(preg_match("/^(git|ssh|http)/", $packageVersion) === 0) {
                    $gitUrl = "git://git@github.com:$gitUrl.git";
                }

                $packageCacheVersionDir = "$packageCacheDir/$gitVersion";
            }

            // Download the latest
            if ($packageType === 'git') {
                if (is_dir($packageCacheVersionDir)) {
                    Process::exec("git pull", ['stdio' => 'pipe', 'cwd' => $packageCacheVersionDir]);
                } else {
                    Process::exec("git clone $gitUrl $packageCacheVersionDir", ['stdio' => 'pipe']);
                    list($status) = Process::exec("git checkout $gitVersion", ['stdio' => 'pipe']);
                    if ($status !== 0) {
                        Console::error("$packageVersion is not exists");
                    }
                }
            } else {
                // Download
            }

            // TODO: Check shasum

            // Check version

            // Check pac.json

            // Check composer.json

            // Check dependency and download

            // Resolve dependency

            // Merge dependency

            // Copy to dist

            // Build autoloader
        }
    }

    $template = file_get_contents($autoloadTemplate);
    file_put_contents($autoloadFile, str_replace('$namespace = [];', '$namespace = ' . var_export($package['namespace'], true) . ';', $template));

    Console::success('Install ok');
};
