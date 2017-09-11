<?php

use Pac\Command;
use Pac\Console;

return function(array $command) {
    $rootDir = getcwd();
    $jsonFile = $rootDir . '/pac.json';

    if (file_exists($jsonFile) && !Console::confirm('"pac.json" is already exists, continue anyway?')) {
        return;
    }

    $dirName = last(explode('/', $rootDir));
    $username = get_current_user();

    $name = Console::prompt("Name [<green>$dirName</green>]: \n> ");
    if (!$name) $name = $dirName;

    $version = Console::prompt("Version [<green>0.0.1</green>]: \n> ");
    if (!$version) $version = '0.0.1';

    $license = Console::prompt("License [<green>MIT</green>]: \n> ");
    if (!$license) $license = 'MIT';

    $author = Console::prompt("Author [<green>$username</green>]: \n> ");
    if (!$author) $author = $username;

    $email = Console::prompt("Email: \n> ", false, 3);
    if (!$email) {
        Console::error('Email is required.');
        return;
    }

    $json = json_encode([
        'name' => $name,
        'version' => $version,
        'license' => $license,
        'author' => [
            'name' => $author,
            'email' => $email
        ]
    ], JSON_PRETTY_PRINT);

    echo $json . PHP_EOL;

    if (Console::confirm('Confirm?', true)) {
        file_put_contents($jsonFile, $json);
    }
};
