<?php
namespace Virge\Graph\Worker;

use Virge\Graph;
use Virge\Graph\Component\Workflow;
use Virge\Graph\Component\Workflow\Job;
use Virge\Graph\Model\JobResult;
use Virge\Graph\Model\TaskResult;
use Virge\Graph\Task\ScheduleTask;

use Virge\Cli;
use Virge\Virge;

/**
 * 
 * @author Michael Kramer
 */
class ScheduleTasksWorker
{
    public function run(ScheduleTask $task)
    {
        $job = $task->getJob();
        $tasks = $job->getTasks();

        foreach($tasks as $workflowTask)
        {
            if(empty($workflowTask->getDependencies())) {
                //queue it up
                Graph::queueTask($job, $workflowTask->getTaskId());
            }
        }
    }
}