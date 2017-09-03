<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Pac\Command;

$args = Command::parse($argv);

//$command = new Command();
//
//$command->addOption('no', [
//    'alias' => 'n',
//    'type' => 'string'
//]);
//
//// Will get the arguments you want
//$args = $command->parse($argv);

var_dump($args);
exit;
