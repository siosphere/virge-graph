<?php
namespace Virge\Graph\Worker;

use Virge\Graph;
use Virge\Graph\Component\Workflow;
use Virge\Graph\Component\Workflow\Job;
use Virge\Graph\Model\JobResult;
use Virge\Graph\Model\TaskResult;
use Virge\Graph\Task\WorkflowTask;

use Virge\Cli;
use Virge\Virge;

/**
 * 
 * @author Michael Kramer
 */
class WorkflowTaskWorker
{
    public function run(WorkflowTask $task)
    {
        try {
            $workflow = Graph::workflow($task->getWorkflowId());
            if(!$workflow) {
                throw new \InvalidArgumentException(sprintf("%s is not a valid workflow", $task->getWorkflowId()));
            }
            $workflow->doTask($task->getTaskId(), $task->getJob());
        } catch(\Exception $ex) {
            Cli::output($ex->getMessage());
        }
    }
}