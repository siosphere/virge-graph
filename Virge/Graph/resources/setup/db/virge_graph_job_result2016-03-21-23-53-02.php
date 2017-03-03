<?php

use Virge\Database\Component\Schema;

Schema::create(function() {
    Schema::table('virge_graph_job_result');
    Schema::id('id');
    Schema::string('token');
    Schema::string('workflow_id');
    Schema::string('status');
    Schema::text('error');
    Schema::text('job');
    Schema::timestamp('created_on');
    Schema::end();
});