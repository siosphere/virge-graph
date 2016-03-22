<?php
require_once '../bootstrap.php';
require_once './workflow.php';

use Virge\Graph;
use Virge\Graph\Component\Workflow\Job;
use Virge\Graph\Model\TaskResult;

$job = new Job('simple');
$job->setData('test123');
Graph::push($job);