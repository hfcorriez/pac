<?php

use Pac\Command;
use Pac\Console;

return function(array $command) {
    if (!isset($command['commands'][1])) {
        Command::help([
            'help' => 'Help',
        ], [
            'usage' => $command['program'] . ' ' . $command['commands'][0] . ' [package]'
        ]);

        return false;
    }

    $package = $command['commands'][1];

    Console::error('search '.  $package);
};
