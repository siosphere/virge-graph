<?php

$reactor = new Reactor();
$reactor->registerCapsules();

use Virge\Graph;
use Virge\Graph\Component\Workflow\Job;
use Virge\Graph\Model\TaskResult;

class SimpleWorkflow extends Virge\Graph\Component\Workflow
{
    public function defineWorkflow()
    {
        Graph::task(StepOne::TASK_ID, StepOne::class);
        Graph::task('a', function(Job $job, TaskResult $result) {
            return true;
        });
        
        Graph::task('b', function(Job $job, TaskResult $result) {
            $result->setResult('world');
            //fail
            return true;
        })->dependsOn(['a']);
        
        Graph::task('world', function(Job $job, TaskResult $result) {
            $result->setResult('world');
            //fail
            return false;
        })
            ->dependsOn([StepOne::TASK_ID, 'a', 'b'])
            ->delay(10) //delay 10ms
            ->onComplete(function(Job $job) {
                $hello = $job->getTask(StepOne::TASK_ID);
                $world = $job->getTask('world');
                
                //write the result
                file_put_contents('./results.log', $hello->getResult() . ' ' . $world->getResult() . "\n", FILE_APPEND);
            })
            ->onFail(function(Job $job) {
                file_put_contents('./results.log', 'failure bot' . "\n", FILE_APPEND);
            })
        ;
    }
}

class StepOne
{
    const TASK_ID = 'hello';
    
    public function run(Job $job, TaskResult $result)
    {
        $result->setResult('hello');
        $result->stepProgress(1);
    }
}

Graph::workflow('simple', SimpleWorkflow::class);


//php -f vadmin.php virge:graphite virge:graph
//php -f vadmin.php virge:graphite virge:graph:workflowId:taskId