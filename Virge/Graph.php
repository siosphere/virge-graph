<?php

namespace Virge;

use Virge\Graph\Component\Workflow;
use Virge\Graph\Component\Workflow\Job;
use Virge\Graph\Model\JobResult;
use Virge\Graph\Model\TaskResult;
use Virge\Graph\Task\JobUpdateTask;
use Virge\Graph\Task\WorkflowTask;
use Virge\Graph\Task\ScheduleTask;
use Virge\Graphite\Component\Task;
use Virge\Graphite\Service\QueueService;

/**
 * 
 */
class Graph
{
    const GRAPH_QUEUE = 'virge:graph';
    
    protected static $workflowScope = null;
    
    protected static $workflows = [];
    
    public static function push(Job $job)
    {
        if(!isset(self::$workflows[$job->getWorkflowId()])) {
            throw new \InvalidArgumentException(sprintf("Workflow %s does not exist", $job->getWorkflowId()));
        }
        
        $workflow = self::$workflows[$job->getWorkflowId()];
        $jobResult = new JobResult();
        $jobResult->setToken(Enigma::hash($job->getWorkflowId() . mt_rand()));
        $jobResult->setWorkflowId($job->getWorkflowId());
        $jobResult->setStatus(JobResult::STATUS_SETUP);
        $jobResult->save();
        $job->setJobId($jobResult->getId());
        
        self::setupJob($job, $workflow); //setup initial job tasks
        
        $jobResult->setJob($job);
        $jobResult->setStatus(JobResult::STATUS_QUEUED);
        $jobResult->save();
        
        $job->setJobId($jobResult->getId());
        
        
        self::queueJob($jobResult->getId());
    }
    
    public static function queueTask(Job $job, $taskId, $taskResultId, $queue = null)
    {
        self::queue(new WorkflowTask($job->getJobId(), $taskId, $taskResultId), $queue ?? self::getQueueKey($job->getWorkflowId(), $taskId));
    }
    
    protected static function queueJob(int $jobId)
    {
        self::queue(new ScheduleTask($jobId));
    }
    
    protected static function setupJob(Job $job, Workflow $workflow)
    {
        foreach($workflow->getTasks() as $taskId => $task)
        {
            $taskResult = new TaskResult();
            $taskResult->setTaskId($taskId);
            $taskResult->setStatus(TaskResult::STATUS_QUEUED);
            $taskResult->setJobId($job->getJobId());
            $taskResult->save();
            $job->addTask($taskResult);
        }
    }
    
    public static function workflow($workflowId, $workflowClassname = null)
    {
        if($workflowClassname === null) {
            return self::$workflows[$workflowId];
        }
        $workflow = new $workflowClassname;
        if(!($workflow instanceof Workflow)) {
            throw new \InvalidArgumentException(sprintf("%s workflow must extend %s", $workflowId, Workflow::class));
        }
        
        $workflow->setup();
        
        self::$workflows[$workflowId] = $workflow;
    }
    
    public static function taskStatus(Job $job, TaskResult $result)
    {
        self::queue(new JobUpdateTask($job->getJobId(), $result->getTaskId(), $result->getId()));
    }
    
    protected static function queue(Task $task, $queue = self::GRAPH_QUEUE)
    {
        self::getQueueService()->push($queue, $task);
    }
    
    public static function task($taskId, $callable)
    {
        $workflow = self::$workflowScope;
        
        return $workflow->addTask($taskId, $callable);
    }
    
    public static function simple()
    {
        $workflow = self::$workflowScope;
        
        return $workflow->setSimple(true);
    }
    
    public static function setScope(Workflow $workflow)
    {
        self::$workflowScope = $workflow;
    }
    
    public static function resetScope()
    {
        self::$workflowScope = null;
    }
    
    protected static function getQueueKey($workflowId, $taskId = false)
    {
        $workflow = self::$workflows[$workflowId];
        
        if($workflow->getSimple()){
            return self::GRAPH_QUEUE;
        }
        
        return self::GRAPH_QUEUE . ':' . implode(':', array_filter([$workflowId, $taskId]));
    }
    
    /**
     * @return QueueService
     */
    protected static function getQueueService() {
        return Virge::service(QueueService::SERVICE_ID);
    }
}