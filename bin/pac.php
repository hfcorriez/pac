<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Pac\Command;

$args = Command::config(['auto' => true])
    ->addOption('no', [
        'alias' => 'n',
        'type' => 'number',
    ])
    ->parseCommand($argv);

//$command = new Command();
//
//$command->addOption('no', [
//    'alias' => 'n',
//    'type' => 'string'
//]);
//
//// Will get the arguments you want
//$args = $command->parse($argv);
var_dump($args['invalids']);
var_dump($args);
exit;
