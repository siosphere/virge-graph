<?php
namespace Virge\Graph\Task;

use Virge\Graph\Component\Workflow\Job;
use Virge\Graphite\Component\Task;

/**
 * Do a workflow task
 */
class WorkflowTask extends Task 
{
    
    const TASK_NAME = 'virge.graph.task.workflow';

    /**
     * @var int
     */
    protected $jobId;

    /**
     * @var string 
     */
    protected $taskId;
    
    public function __construct(int $jobId, $taskId, int $taskResultId)
    {
        $this->jobId = $jobId;
        $this->taskId = $taskId;
        $this->taskResultId = $taskResultId;
    }
    
    public function getJobId() : int
    {
        return $this->jobId;
    }

    /**
     * @return string
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    public function getTaskResultId() : int
    {
        return $this->taskResultId;
    }
}