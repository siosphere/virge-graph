<?php
namespace Virge\Graph\Command;

use Virge\Cli;
use Virge\Cli\Component\Command;
use Virge\Core\Config;

class InitCommand extends Command
{
    const COMMAND = 'virge:graph:init';

    public function run()
    {
        Cli::output("Virge::Graph");

        Cli::execute('db:schema:commit', [
            Config::path('Virge\\Graph@resources/setup/db/'),
        ]);

        Cli::output('DONE');
    }
}