<?php
namespace Virge\Graph\Task;

use Virge\Graph\Component\Workflow\Job;
use Virge\Graphite\Component\Task;

/**
 * Schedule the tasks for the given job
 */
class ScheduleTask extends Task 
{
    
    const TASK_NAME = 'virge.graph.task.schedule';
    
    protected $jobId;
    
    /**
     * @param Job $job
     */
    public function __construct(int $jobId)
    {
        $this->jobId = $jobId;
    }
    
    public function getJobId() : int
    {
        return $this->jobId;
    }
}