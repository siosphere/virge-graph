<?php
namespace Virge\Graph\Controller;

use Virge\Api\Controller\InternalApiController;
use Virge\Api\Exception\ApiException;
use Virge\Graph;
use Virge\Graph\Model\JobResult;
use Virge\Graph\Service\UpdateJobService;
use Virge\Router\Component\Request;

use Virge\Virge;

class GraphApiController extends InternalApiController
{
    const SCHEDULE_TASKS_FOR_JOB = 'virge/graph/job/{jobId}/scheduleTasks';
    const UPDATE_JOB = 'virge/graph/job/{jobId}/update';
    const DO_TASK    = 'virge/graph/job/{jobId}/task';

    public function scheduleTasksForJob(Request $request)
    {
        $jobId = $request->getUrlParam('jobId');
        $jobResult = new JobResult();
        if(!$jobResult->load($jobId)) {
            throw new ApiException("Invalid job");
        }

        $workflow = Graph::workflow($jobResult->getWorkflowId());

        $job = $jobResult->getJob();
        if(!$job) {
            throw new ApiException("Invalid job");
        }

        $tasks = $job->getTasks();

        $queuedTasks = 0;
        foreach($tasks as $taskResult)
        {
            if(empty($taskResult->getDependencies())) {
                $workflowTask = $workflow->getTask($taskResult->getTaskId());
                //queue it up
                Graph::queueTask($job, $taskResult->getTaskId(), $taskResult->getId(), $workflowTask->getQueue());
                $queuedTasks++;
            }
        }

        return [
            'success'       => true,
            'queued_tasks'  => $queuedTasks,
        ];
    }

    public function updateJob(Request $request)
    {
        $jobId = $request->getUrlParam('jobId');
        $taskId = $request->get('task_id');
        $taskResultId = $request->get('task_result_id');

        return [
            'success' => $this->getUpdateJobService()->updateJob($jobId, $taskId, $taskResultId),
        ];
    }

    public function doTask(Request $request)
    {
        $taskId = $request->get('task_id');
        $jobId = $request->getUrlParam('jobId');

        $jobResult = new JobResult();
        if(!$jobResult->load($jobId)) {
            throw new ApiException("Invalid job");
        }

        $workflow = Graph::workflow($jobResult->getWorkflowId());
        if(!$workflow) {
            throw new \ApiException(sprintf("%s is not a valid workflow", $jobResult->getWorkflowId()));
        }

        $success = $workflow->doTask($taskId, $jobResult->getJob());

        return [
            'success' => $success,
        ];
    }

    protected function getUpdateJobService() : UpdateJobService
    {
        return Virge::service(UpdateJobService::class);
    }
}