<?php
namespace Virge\Graph\Model;

use Virge\ORM\Component\Model;

/**
 * 
 */
class TaskResult extends Model
{
    protected $_table = 'virge_graph_task_result';
    
    const STATUS_COMPLETE = 'complete';
    const STATUS_PROCESSING = 'processing';
    const STATUS_FAIL = 'fail';
    const STATUS_QUEUED = 'queued';
    
    public function stepProgress($step)
    {
        $this->progress += $step;
        
        return $this;
    }
}