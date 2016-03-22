<?php
namespace Virge\Graph\Component\Workflow;

/**
 * 
 */
class Task
{
    protected $taskId;
    protected $callable;
    
    protected $dependencies = [];
    
    protected $delay = null;
    
    protected $onComplete = null;
    
    protected $onProgress = null;
    
    protected $onFail = null;
    
    public function __construct($callable, $taskId)
    {
        $this->callable = $callable;
        $this->taskId = $taskId;
    }
    
    public function run(Job $job) 
    {
        if(is_callable($this->callable)) {
            return call_user_func_array($this->callable, [$job, $job->getTask($this->taskId)]);
        }

        if(!class_exists($this->callable)) {
            throw new \InvalidArgumentException(sprintf("Invalid class or callable for Task %s", $this->taskId));
        }
        
        $temp = new $this->callable;
        return $temp->run($job, $job->getTask($this->taskId));
    }
    
    public function dependsOn($tasks = [])
    {
        $this->dependencies = $tasks;
        return $this;
    }
    
    public function delay($delay)
    {
        $this->delay = $delay;
        
        return $this;
    }
    
    public function onComplete($callable)
    {
        if(!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf("Invalid callable passed to onComplete"));
        }
        
        $this->onComplete = $callable;
        
        return $this;
    }
    
    public function onProgress($callable)
    {
        if(!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf("Invalid callable passed to onProgress"));
        }
        
        $this->onProgress = $callable;
        
        return $this;
    }
    
    public function onFail($callable)
    {
        if(!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf("Invalid callable passed to onFail"));
        }
        
        $this->onFail = $callable;
        
        return $this;
    }
    
    public function complete(Job $job)
    {
        if($this->onComplete)
        {
            call_user_func_array($this->onComplete, [$job]);
        }
    }
    
    public function progress(Job $job)
    {
        if($this->onProgress)
        {
            call_user_func_array($this->onProgress, [$job]);
        }
    }
    
    public function fail(Job $job)
    {
        if($this->onFail)
        {
            call_user_func_array($this->onFail, [$job]);
        }
    }
    
    public function getDependencies()
    {
        return $this->dependencies;
    }
    
    public function getTaskId()
    {
        return $this->taskId;
    }
}