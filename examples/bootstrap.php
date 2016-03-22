<?php

error_reporting(E_ALL & ~E_NOTICE);

$BASE_DIR = dirname(__FILE__) . '/';

$loader = include $BASE_DIR . '../vendor/autoload.php';
$loader->add('Virge', $BASE_DIR . '../');


class Reactor extends Virge\Core\BaseReactor
{
    public function registerCapsules($capsules = [])
    {
        parent::registerCapsules([
            new \Virge\Graph\GraphCapsule(),
            new \Virge\Graphite\GraphiteCapsule(),
            new \Virge\Cli\Capsule(),
            new \Virge\ORM\Capsule(),
            new \Virge\Database\Capsule(),
        ]);
    }
}