<?php
namespace Virge\Graph;

use Virge\Api\Component\ApiWrapper;
use Virge\Graph\Controller\GraphApiController;

class GraphApi extends ApiWrapper
{
    public static function scheduleTasks(int $jobId)
    {
        return self::_post(GraphApiController::SCHEDULE_TASKS_FOR_JOB, [
            'jobId' => $jobId,
        ]);
    }

    public static function updateJob(int $jobId, $taskId, int $taskResultId)
    {
        return self::_post(GraphApiController::UPDATE_JOB, [
            'jobId' => $jobId,
            'task_id' => $taskId,
            'task_result_id' => $taskResultId
        ]);
    }

    public static function doTask($jobId, $taskId, $taskResultId)
    {
        return self::_post(GraphApiController::DO_TASK, [
            'jobId' => $jobId,
            'task_id' => $taskId,
            'task_result_id' => $taskResultId,
        ]);
    }
}