<?php

use Virge\Graph\Command\{
    InitCommand
};
use Virge\Cli;

Cli::add(InitCommand::COMMAND, InitCommand::class);