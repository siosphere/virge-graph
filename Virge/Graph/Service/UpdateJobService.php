<?php
namespace Virge\Graph\Service;

use Virge\Graph;
use Virge\Graph\Component\Workflow;
use Virge\Graph\Component\Workflow\Job;
use Virge\Graph\Model\JobResult;
use Virge\Graph\Model\TaskResult;

use Virge\Cli;
use Virge\ORM\Component\Collection\Filter;
use Virge\Virge;

class UpdateJobService
{
    public function updateJob(int $jobId, $taskId, int $taskResultId)
    {
        //load workflow from job
        
        $jobResult = new JobResult();
        if(!$jobResult->load($jobId)) {
            return false; //TODO: log
        }
        
        $workflow = Graph::workflow($jobResult->getWorkflowId());
        if(!$workflow) {
            $jobResult->setStatus(JobResult::STATUS_FAIL);
            $jobResult->setError("Workflow does not exist");
            $jobResult->save();
            return false; //TODO: log
        }

        $taskResult = TaskResult::findOne(function() use($taskResultId) {
            Filter::eq('id', $taskResultId);
        });

        if(!$taskResult) {
            $jobResult->setStatus(JobResult::STATUS_FAIL);
            $jobResult->setError("Invalid Task Result");
            $jobResult->save();
            return false;
        }
        
        $job = $jobResult->getJob();
        $job->addTask($taskResult);
        $this->doTaskCallbacks($workflow, $job, $taskResult);
        $taskResult->save();
        $job->addTask($taskResult);
        $job->setJobId($jobResult->getId());
        $this->scheduleRemainingTasks($workflow, $job, $jobResult, $taskResult);
        $jobResult->setJob($job);
        
        return $jobResult->save();
    }

    protected function scheduleRemainingTasks(Workflow $workflow, Job $job, JobResult $jobResult, TaskResult $lastCompletedTaskResult)
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

        if(empty($remaining)) {
            return $this->finishJob($jobResult);
        }

        foreach($remaining as $task) {
            $taskResult = $taskResults[$task->getTaskId()];
            Graph::queueTask($job, $task->getTaskId(), $taskResult->getId());
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

    protected function finishJob(JobResult $jobResult)
    {
        $jobResult->setStatus(JobResult::STATUS_COMPLETE);
        $jobResult->save();
    }
}