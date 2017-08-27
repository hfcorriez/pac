<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Pac\Command;

$arg_parser = new Command();

// Add a short arguments and support enum type
$arg_parser->add('a', array('help' => 'a long time with he happy', 'enum' => array(
    'go' => 'do some thing with go'
)));

// Add long and short arguments
$arg_parser->add('l', array('help' => 'long arguments', 'enum' => array('a', 'b')));

// Will get the arguments you want
$args = $arg_parser->parse();

var_dump($args);
exit;

// Print the help message
echo $arg_parser->help();
