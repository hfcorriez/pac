<?php

use Pac\Command;
use Pac\Console;

return function (array $command) {
    $rootDir = getcwd();
    $jsonFile = $rootDir . '/pac.json';
    $packageDir = $rootDir . '/php_packages';
    $autoloadFile = $packageDir . '/__init__.php';
    $autoloadTemplate = $rootDir . '/src/__init__.php';

    if (!file_exists($jsonFile)) {
        Console::error('"pac.json" is not exists.');
        return;
    }

    $json = @json_decode(file_get_contents($jsonFile), JSON_OBJECT_AS_ARRAY);

    if (!is_dir($packageDir) && !@mkdir($packageDir, 755, true)) {
        Console::error("Dir \"$packageDir\" can not create");
        return;
    }

    $template = file_get_contents($autoloadTemplate);
    file_put_contents($autoloadFile, str_replace('$namespace = [];', '$namespace = ' . var_export($json['namespace'], true) . ';', $template));

    Console::success('Install ok');
};
