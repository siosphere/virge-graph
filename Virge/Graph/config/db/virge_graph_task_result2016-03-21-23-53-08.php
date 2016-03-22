<?php

use Virge\Database\Component\Schema;

Schema::create(function() {
    Schema::table('virge_graph_task_result');
    Schema::id('id');
    Schema::int('job_id')->setIndex('INDEX');
    Schema::string('task_id');
    Schema::bool('completed');
    Schema::bool('fail');
    Schema::int('progress');
    Schema::string('status');
    Schema::text('error');
    Schema::timestamp('created_on');
    
    Schema::reference('job_id', 'virge_graph_job_result/id');
    Schema::end();
});