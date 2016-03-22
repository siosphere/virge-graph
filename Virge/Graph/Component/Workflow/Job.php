<?php

namespace Virge\Graph\Component\Workflow;

use Virge\Graph\Model\TaskResult;

/**
 * 
 */
class Job
{
    protected $jobId;
    
    protected $workflowId;
    
    protected $progress;
    
    protected $tasks = [];
    
    protected $data = null;
    
    public function __construct($workflowId)
    {
        $this->workflowId = $workflowId;
    }
    
    public function setResult($taskId, $result)
    {
        $this->getTask($taskId)->setResult($result);
        
        return $this;
    }
    
    public function addTask(TaskResult $task)
    {
        $this->tasks[$task->getTaskId()] = $task;
        
        return $this;
    }
    
    public function getTask($taskId)
    {
        if(!isset($this->tasks[$taskId])) {
            throw new \InvalidArgumentException(sprintf("%s is not a valid task for this workflow", $taskId));
        }
        
        return $this->tasks[$taskId];
    }
    
    public function getTasks()
    {
        return $this->tasks;
    }
    
    public function setProgress($taskId, $progress)
    {
        $this->getTask($taskId)->setProgress($progress);
    }
    
    public function stepProgress($taskId, $step)
    {
        $this->getTask($taskId)->stepProgress($step);
    }
    
    public function setData($data)
    {
        $this->data = $data;
        
        return $this;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function getWorkflowId()
    {
        return $this->workflowId;
    }
    
    public function getJobId()
    {
        return $this->jobId;
    }

    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
        return $this;
    }


}