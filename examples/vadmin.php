<?php
use Virge\Core\Config;

error_reporting(E_ALL &~ E_NOTICE &~ E_STRICT);

/**
 * 
 * @author Michael Kramer
 */

chdir(dirname(__FILE__));

require_once 'bootstrap.php';

//include all the workflows for examples here
include_once './simple/workflow.php';

// Create new Notifier instance.
$config = Config::get('app');

$args = array();
if(isset($argv[2])){
    for($i = 2; $i <= $argc; $i++) {
        $args[] = isset($argv[$i]) ? $argv[$i] : NULL;
    }
}

$command = isset($argv[1]) ? $argv[1] : null;

$reactor = new Reactor();
$reactor->run('prod', 'cli', 'execute', array($command, $args));