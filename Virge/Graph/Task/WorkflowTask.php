<?php
namespace Virge\Graph\Task;

use Virge\Graph\Component\Workflow\Job;
use Virge\Graphite\Component\Task;

/**
 * Do a workflow task
 */
class WorkflowTask extends Task {
    
    const TASK_NAME = 'virge.graph.task.workflow';
    
    /**
     * @var string 
     */
    protected $workflowId;
    
    /**
     * @var string 
     */
    protected $taskId;
    
    /**
     * @var Job
     */
    protected $job;
    
    /**
     * @param string $workflowId
     * @param string $taskId
     */
    public function __construct($workflowId, $taskId, $job)
    {
        $this->job = $job;
        $this->taskId = $taskId;
        $this->workflowId = $workflowId;
    }
    
    /**
     * @return Job
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @return string
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * @return string
     */
    public function getWorkflowId()
    {
        return $this->workflowId;
    }


}