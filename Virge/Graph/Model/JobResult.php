<?php
namespace Virge\Graph\Model;

use Virge\Graph\Component\Workflow\Job;
use Virge\ORM\Component\Model;

/**
 * 
 */
class JobResult extends Model
{
    protected $_table = 'virge_graph_job_result';
    
    const STATUS_SETUP = 'setup';
    const STATUS_COMPLETE = 'complete';
    const STATUS_PROCESSING = 'processing';
    const STATUS_FAIL = 'fail';
    const STATUS_QUEUED = 'queued';
    
    public function __construct($data = array())
    {
        parent::__construct($data);
        
        $this->setCreatedOn(new \DateTime());
    }
    
    /**
     * @return Job
     */
    public function getJob()
    {
        if($this->job) {
            return unserialize($this->job);
        }
        
        return null;
    }
    
    public function setJob(Job $job) {
        $this->job = serialize($job);
        
        return $this;
    }
}