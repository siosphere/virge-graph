<?php

namespace Virge\Graph\Component;

use Virge\Graph;
use Virge\Graph\Component\Workflow\Job;
use Virge\Graph\Component\Workflow\Task;
use Virge\Graph\Model\TaskResult;

/**
 * 
 */
abstract class Workflow
{
    protected $tasks = [];
    
    protected $simple = false;
    
    public abstract function defineWorkflow();
    
    public function setup()
    {
        Graph::setScope($this);
        $this->defineWorkflow();
        Graph::resetScope();
    }
    
    public function addTask($taskId, $callable)
    {
        if(isset($this->tasks[$taskId])) {
            throw new \InvalidArgumentException(sprintf("Task %s was already defined", $taskId));
        }
        
        return $this->tasks[$taskId] = new Task($callable, $taskId);
    }
    
    public function getTask($taskId)
    {
        if(!isset($this->tasks[$taskId])) {
            throw new \InvalidArgumentException(sprintf("Invalid Task %s", $taskId));
        }
        
        return $this->tasks[$taskId];
    }
    
    public function doTask($taskId, Job $job)
    {
        if(!isset($this->tasks[$taskId])) {
            //should be caught and automatically mark this task as a failure in the db
            throw new \InvalidArgumentException(sprintf("Task %s is invalid", $taskId));
        }
        $taskResults = $job->getTasks();
        $task = $this->tasks[$taskId];
        
        //can this job run yet
        $dependencies = $task->getDependencies();
        
        if($dependencies) {
            $ready = true;
            foreach($dependencies as $dependTaskId)
            {
                $taskResult = $taskResults[$dependTaskId];
                if(!$taskResult->getCompleted()) {
                    $ready = false;
                    break;
                }
            }
            if(!$ready) {
                return;
            }
        }
        
        $taskResult = $taskResults[$taskId];
        
        $result = $this->tasks[$taskId]->run($job, $taskResult);
        if($result === false) {
            //mark task as failure
            $taskResult->setStatus(TaskResult::STATUS_FAIL);
        } else {
            $taskResult->setStatus(TaskResult::STATUS_COMPLETE);
            $taskResult->setCompleted(1);
        }

        $taskResult->save();
        
        Graph::taskStatus($job, $taskResult); //push a task status onto the queue
    }
    
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Simple workflows don't use separate queues for each task
     * @return bool
     */
    public function getSimple()
    {
        return $this->simple;
    }

    /**
     * Set whether or not we are a simple workflow, in which case we will not
     * use separate queues for each task
     * @param bool $simple
     * @return \Virge\Graph\Component\Workflow
     */
    public function setSimple($simple)
    {
        $this->simple = $simple;
        return $this;
    }


}