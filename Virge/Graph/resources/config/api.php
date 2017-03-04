<?php

use Virge\Api;
use Virge\Core\Config;
use Virge\Graph\Controller\GraphApiController;

$apiVersion = Config::get('app', 'graph_api_verifier') ?? 'all';

$scheduleEndpoint = Api::post(GraphApiController::SCHEDULE_TASKS_FOR_JOB);
if(Config::get('app', 'graph_api_verifier')) {
    $scheduleEndpoint->verify(Config::get('app', 'graph_api_verifier'));
}
$scheduleEndpoint->version($apiVersion, GraphApiController::class, 'scheduleTasksForJob');

$updateEndpoint = Api::post(GraphApiController::UPDATE_JOB);
if(Config::get('app', 'graph_api_verifier')) {
    $updateEndpoint->verify(Config::get('app', 'graph_api_verifier'));
}
$updateEndpoint->version($apiVersion, GraphApiController::class, 'updateJob');

$doTaskEndpoint = Api::post(GraphApiController::DO_TASK);
if(Config::get('app', 'graph_api_verifier')) {
    $doTaskEndpoint->verify(Config::get('app', 'graph_api_verifier'));
}
$doTaskEndpoint->version($apiVersion, GraphApiController::class, 'doTask');
