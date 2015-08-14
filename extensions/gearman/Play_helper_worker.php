<?php
/*
 * @Author AChan
 * @Date   20150321
 * @Describ 91助手多进程处理
 */

require 'QueryList.class.php';
$worker = new GearmanWorker();
$worker->addServer();

$worker->addFunction("getAppInfor", function(GearmanJob $job){
	$item = $job->workload();
	$item = explode("##",$item);
        $url = $item[0];
});
