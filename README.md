# Virge::Graph
Virge::Graph is a simple workflow framework to provide scalable queue backed job processing.

Virge::Graph requires RabbitMQ (http://www.rabbitmq.com/) , and a MySQL Database (https://www.mysql.com/).

Virge::Graph uses the Virge::Graphite queue library to handle all of the workers,
and scaling. https://github.com/siosphere/virge-graphite

## Getting Started
You will need to setup a RabbitMQ Server, and a MySQL Server. Both can be setup
easily using http://www.docker.com/

You will need to include the virge/graph package in your project:
```
composer require virge/graph:dev-master
```
You will also need to create a Virge::Reactor and setup some configuration files.

If starting a project from scratch, you can use the virge/project
```
composer create-project virge/project
```
Which will setup the Reactor for you.

To add to existing projects, it is recommended to make a sub-folder to house 
the virge configuration files and reactor.

```
myproject/
    virge/
        config/
            database.php
            queue.php
        reactor.php
        vadmin.php
```
### Reactor
The reactor will automatically load all of the configuration files, and register
the services needed into Virge.

```
<?php 
//reactor.php
$BASE_DIR = dirname(__FILE__) . '/';

$loader = include $BASE_DIR . '../vendor/autoload.php';
$loader->add('Virge', $BASE_DIR . '../');


class Reactor extends Virge\Core\BaseReactor
{
    public function registerCapsules($capsules = [])
    {
        parent::registerCapsules([
            new \Virge\Graph\GraphCapsule(),
            new \Virge\Graphite\GraphiteCapsule(),
            new \Virge\Cli\Capsule(),
            new \Virge\ORM\Capsule(),
            new \Virge\Database\Capsule(),
        ]);
    }
}

//require our workflows
require_once 'workflows.php'
```
You will also need to setup / create two configuration files in config/,
You can copy these files from the examples directory

database.php
```
<?php

return [
    'service'       =>      'virge/database',
    'connections'   =>      array(
        'default'       =>      array(
            'hostname'      =>  'localhost',
            'username'      =>  '',
            'password'      =>  '',
            'database'      =>  'virge_graph',
        ),
    ),
];
```

queue.php
```
<?php

return [
    'host'      =>      'localhost',
    'port'      =>      5672,
    'user'      =>      'guest',
    'pass'      =>      'guest',
];
```

You will also need an entry point to run the reactor and process your queues,
there is a lightweight admin tool included called "vadmin" that you can copy
from the examples directory, or create a new file

vadmin.php
```
<?php
use Virge\Core\Config;

error_reporting(E_ALL &~ E_NOTICE &~ E_STRICT);

/**
 * 
 * @author Michael Kramer
 */

chdir(dirname(__FILE__));

require_once 'reactor.php';

// Create new Notifier instance.
$config = Config::get('app');

$args = array();
if(isset($argv[2])){
    for($i = 2; $i <= $argc; $i++) {
        $args[] = isset($argv[$i]) ? $argv[$i] : NULL;
    }
}

$command = isset($argv[1]) ? $argv[1] : null;

$reactor = new Reactor();
$reactor->run('prod', 'cli', 'execute', array($command, $args));
```

We will also need to create and setup our workflows file, this only needs to
contain the definitions for our workflows, the workflow class can be autoloaded
by composer see https://getcomposer.org/doc/

workflows.php
```
<?php

use MyApp\MyWorkflow;
use Virge\Graph;

Graph::workflow('simple', MyWorkflow::class);
```

The reactor and workflows must be included in your app anywhere you'd like to
push onto a workflow. They are automatically included from the resources dir if
using a virge/project.

## Simple Mode
Virge::Graph has a simple workflow mode, that allows 1 queue to process your workflow. 
This doesn't allow you to individually scale out individual tasks, but does make it
simpler to setup.

## Defining a Workflow
A workflow is a class that defines a series of tasks, and their dependencies, as
well as their life-cycle callbacks.

```
class SimpleWorkflow extends Virge\Graph\Component\Workflow
{
    public function defineWorkflow()
    {
        Graph::simple();
        Graph::task('hello', function(Job $job, TaskResult $result) {
            $result->setResult('hello');
        });
        
        Graph::task('world', function(Job $job, TaskResult $result) {
            $result->setResult('world');
        })
            ->dependsOn(['hello'])
            ->onComplete(function(Job $job) {
                $hello = $job->getTask('hello');
                $world = $job->getTask('world');
                
                //write the result
                file_put_contents('./results.log', $hello->getResult() . ' ' . $world->getResult() . "\n", FILE_APPEND);
            })
        ;
    }
}
```
A task can be a Closure, or it can be a class that has a "run" method.
```
class HelloTask
{
    const TASK_ID = 'hello';
    
    public function run(Job $job, TaskResult $result)
    {
        $result->setResult('hello');
    }
}
```
And when defining, simply pass in the className
```
Graph::task(HelloTask::TASK_ID, HelloTask::class);
```

## Pushing a job onto a workflow
A Job is a Virge\Graph\Component\Workflow\Job object. You can setup initial data
by using the "setData" function.
You can setup complex data in setData, but be aware it is serialized and
pushed onto multiple queues, so it is unwise to put large amounts of data,
and better to pass simple data, and load in the data on each worker.

```
use Virge\Graph;

$job = new Job('simple');
$job->setData('test123');

Graph::push($job);
```

## Working the Queue
You must setup at least 1 worker to work the default virge:graph queue, this
queue handles scheduling of tasks, life-cycle events, updating tasks
progress, and syncing their results with the database.

```
php -f vadmin.php virge:graphite:worker virge:graph
```

On Advanced workflows, each task will be queued to it's own queue, to allow
horizontal scalability.

```
# replace workflowId and taskId below
php -f vadmin.php virge:graphite:worker virge:graph:workflowId:taskId
```