<?php

use Virge\Cli;
use Virge\Graph\GraphApi;
use Virge\Graphite\Worker;
use Virge\Graph\Task\JobUpdateTask;

use Virge\Graph\Worker\ScheduleTasksWorker;
use Virge\Graph\Task\ScheduleTask;

use Virge\Graph\Worker\WorkflowTaskWorker;
use Virge\Graph\Task\WorkflowTask;

Worker::consume(JobUpdateTask::TASK_NAME, function(JobUpdateTask $task) {
    Cli::output('Updating Job: ' . $task->getJobId());
    $result = GraphApi::updateJob($task->getJobId(), $task->getTaskId(), $task->getTaskResultId()) ? 'OK' : 'FAIL';
    Cli::output('Status: ' . $result);
});

Worker::consume(ScheduleTask::TASK_NAME, function(ScheduleTask $task) {
    Cli::output('Scheduling Job: ' . $task->getJobId());
    $result = GraphApi::scheduleTasks($task->getJobId()) ? 'OK' : 'FAIL';
    Cli::output('Status: ' . $result);
});

Worker::consume(WorkflowTask::TASK_NAME, function(WorkflowTask $task) {
    Cli::output('Do Task:' . $task->getTaskId());
    $result = GraphApi::doTask($task->getJobId(), $task->getTaskId(), $task->getTaskResultId()) ? 'OK' : 'FAIL';
    Cli::output('Status: ' . $result);
});

