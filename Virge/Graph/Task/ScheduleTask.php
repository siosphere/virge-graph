<?php
namespace Virge\Graph\Task;

use Virge\Graph\Component\Workflow\Job;
use Virge\Graphite\Component\Task;

/**
 * Schedule the tasks for the given job
 */
class ScheduleTask extends Task {
    
    const TASK_NAME = 'virge.graph.task.schedule';
    
    /**
     * @var Job
     */
    protected $job;
    
    /**
     * @param Job $job
     */
    public function __construct($job)
    {
        $this->job = $job;
    }
    
    /**
     * @return Job
     */
    public function getJob()
    {
        return $this->job;
    }
}