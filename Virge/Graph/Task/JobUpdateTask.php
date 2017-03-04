<?php
namespace Virge\Graph\Task;

use Virge\Graphite\Component\Task;
use Virge\Graph\Model\TaskResult;

/**
 * 
 */
class JobUpdateTask extends Task {
    
    const TASK_NAME = 'virge.graph.task.job_update';
    
    /**
     * @var string 
     */
    protected $jobId;
    
    /**
     * @var string 
     */
    protected $taskId;
    
    /**
     * @var int
     */
    protected $taskResultId;
    
    /**
     * @param string $jobId
     * @param string $taskId
     * @param TaskResult $taskResult
     */
    public function __construct($jobId, $taskId, int $taskResultId)
    {
        $this->jobId = $jobId;
        $this->taskId = $taskId;
        $this->taskResultId = $taskResultId;
    }
    
    /**
     * @return string
     */
    public function getJobId()
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