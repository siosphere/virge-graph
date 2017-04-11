<?php

use Virge\Graph\Command\{
    InitCommand
};
use Virge\Cli;

Cli::add(InitCommand::COMMAND, InitCommand::class)
    ->setHelpText(InitCommand::COMMAND_HELP)
    ->setUsage(InitCommand::COMMAND_USAGE)
;