<?php
/**
 * Created by PhpStorm.
 * User: solo
 * Date: 2015/3/10
 * Time: 16:57
 */

$worker = new GearmanWorker();
$worker->addServer();

$worker->addFunction('getVersion', function(GearmanJob $job){

    $item = $job->workload();
    return $item.'from task 2';
});
while ($worker->work());