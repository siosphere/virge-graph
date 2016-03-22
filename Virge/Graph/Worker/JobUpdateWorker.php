<?php
namespace Virge\Graph\Worker;

use Virge\Graph;
use Virge\Graph\Component\Workflow;
use Virge\Graph\Component\Workflow\Job;
use Virge\Graph\Model\JobResult;
use Virge\Graph\Model\TaskResult;
use Virge\Graph\Task\JobUpdateTask;

use Virge\Cli;
use Virge\Virge;

/**
 * 
 * @author Michael Kramer
 */
class JobUpdateWorker
{
    public function run(JobUpdateTask $task)
    {
        //load workflow from job
        
        $jobResult = new JobResult();
        if(!$jobResult->load($task->getJobId())) {
            return false; //TODO: log
        }
        
        $workflow = Graph::workflow($jobResult->getWorkflowId());
        if(!$workflow) {
            $jobResult->setStatus(JobResult::STATUS_FAIL);
            $jobResult->setError("Workflow does not exist");
            $jobResult->save();
            return false; //TODO: log
        }
        
        $taskResult = $task->getTaskResult();
        
        $job = $jobResult->getJob();
        $job->addTask($taskResult);
        $this->doTaskCallbacks($workflow, $job, $taskResult);
        $taskResult->save();
        $job->addTask($taskResult);
        $job->setJobId($jobResult->getId());
        $this->scheduleRemainingTasks($workflow, $job, $taskResult);
        $jobResult->setJob($job);
        $jobResult->save();
    }
    
    protected function scheduleRemainingTasks(Workflow $workflow, Job $job, TaskResult $lastCompletedTaskResult)
    {
        $taskResults = $job->getTasks();
        
        $remaining = array_filter($workflow->getTasks(), function($task) use($taskResults, $lastCompletedTaskResult) {
            
            $taskResult = $taskResults[$task->getTaskId()];
            
            $dependencies = $task->getDependencies();
            if($taskResult->getStatus() !== TaskResult::STATUS_QUEUED || empty($dependencies) || !in_array($lastCompletedTaskResult->getTaskId(), $dependencies)) {
                return false;
            }

            $ready = true;
            foreach($dependencies as $dependTaskId)
            {
                $taskResult = $taskResults[$dependTaskId];
                if(!$taskResult->getCompleted()) {
                    $ready = false;
                    break;
                }
            }
            
            return $ready;
        });
        
        foreach($remaining as $task) {
            Graph::queueTask($job, $task->getTaskId());
        }
        
    }
    
    protected function doTaskCallbacks(Workflow $workflow, Job $job, TaskResult $taskResult)
    {
        $task = $workflow->getTask($taskResult->getTaskId());
        switch($taskResult->getStatus()) {
            case TaskResult::STATUS_COMPLETE:
                $taskResult->setCompleted(true);
                $task->complete($job);
                break;
            case TaskResult::STATUS_FAIL:
                $taskResult->setFail(true);
                $task->fail($job);
                break;
        }
    }
}