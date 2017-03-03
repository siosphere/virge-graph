<?php

use Virge\Graphite\Worker;
use Virge\Graph\Worker\JobUpdateWorker;
use Virge\Graph\Task\JobUpdateTask;

use Virge\Graph\Worker\ScheduleTasksWorker;
use Virge\Graph\Task\ScheduleTask;


use Virge\Graph\Worker\WorkflowTaskWorker;
use Virge\Graph\Task\WorkflowTask;

Worker::consume(JobUpdateTask::TASK_NAME, new JobUpdateWorker(), 'run');
Worker::consume(ScheduleTask::TASK_NAME, new ScheduleTasksWorker(), 'run');
Worker::consume(WorkflowTask::TASK_NAME, new WorkflowTaskWorker(), 'run');

