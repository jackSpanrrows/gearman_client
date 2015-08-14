<?php

$worker = new GearmanWorker();
$worker->addServer();

$worker->addFunction('getAppInfo', function(GearmanJob $job){
    $item = $job->workload();
    $item = explode("##",$item);
    $url = $item[0];
    $reg = array('appName' => array('.app-pod ul li img','alt'), 'href' => array('.app-pod ul li img', 'src') , 'version' => array('.category', 'text'),'download_url' => array('.app-pod .icon ','href'),'appleid'=>array('.app-pod ul li','appleid'));
    $sj = QueryList::Query($url, $reg);
    $tmp = $sj->jsonArr;
    $cid = getmypid();
    echo $cid;
    
});
while ($worker->work());


function getCurrentPageAppList($url,$total,$k,$db,$log_filename,$sourceid)
{

    $reg = array('appName' => array('.app-pod ul li img','alt'), 'href' => array('.app-pod ul li img', 'src') , 'version' => array('.category', 'text'),'download_url' => array('.app-pod .icon ','href'),'appleid'=>array('.app-pod ul li','appleid'));

    $sj = QueryList::Query($url, $reg);

    $columnArr = $sj->jsonArr;
    
    $i = 0;

    $sql = "REPLACE INTO ". TABLE . "(APPLEID,APPNAME,APPICON,DISPLAYVERSION,APPCATEGORY,SOURCEID,INSERTTIME,APPDOWNLOADURL) values";
    
    foreach($columnArr as $key => $value)
    {
        $appName = addslashes($value['appName']);
        
        $version = explode("|",$value['version']);
        
        $display_version = $version[0]; 
        
        $app_icon = addslashes($value['href']);
        
        $app_download_url = "http://app.tongbu.com".$value['download_url'];
        
        $sql.= "('{$value['appleid']}','{$appName}','{$app_icon}','{$display_version}','{$k}','{$sourceid}','".date('Y-m-d H:i:s',time())."','{$app_download_url}'),";
        
    	$i++;
    }

    //当前页应用统计
    $onePageApp = count($columnArr);

    //第二页应用个数
    $secpage_app_num = 21;

    //总共还需获取页数
    $totalPage = ceil((100-$onePageApp) / $secpage_app_num) + 1;

    //获取后面几页地址
    $sj->setQuery(array('current_page' => array('.pagination a', 'href')));

    $pageList = $sj->jsonArr;

    $needGetPage = array_slice($pageList, 0, $totalPage);
    
   
    
    foreach ($needGetPage as $val) 
    {
    	$urls = DEFAULT_DOMAIN.$val['current_page'];

    	$sj = QueryList::Query($urls, $reg);

    	$columnArrs = $sj->jsonArr;

    	foreach($columnArrs as $keys => $values)
    	{
    	    $appName = addslashes($values['appName']);
            
            $version = explode("|",$values['version']);
            
            $display_version = $version[0]; 
        
            $app_icon = addslashes($values['href']);

            $app_download_url = "http://app.tongbu.com".$values['download_url'];

            if($i > 99) break;  
            
            $sql.= "('{$values['appleid']}','{$appName}','{$app_icon}','{$display_version}','{$k}','{$sourceid}','".date('Y-m-d H:i:s',time())."','{$app_download_url}'),";
            $i++;
    	}

    }
    echo  $sql;die;
    //insertDb($db,$sql,$log_filename,$k);
}



function insertDb($db,$sql,$log_filename,$k){
    $date = date("Y-m-d H:i:s" , time());
    try{
            $db->beginTransaction();
            $sql = substr($sql,0,-1);
            $num = $db->exec($sql);//执行SQL语句返回总条数
            $db->commit();
            if($num>0){
                $message = "成功插入{$k}{$num}条数据!";
                error_log($date . "\t" . $message . "\r\n" , 3 , $log_filename);
            }

        } catch (PDOException $err) {
            
            $db->rollback();

            die(error_log($date . "\t" . $err->getMessage() . "\r\n" , 3 , $log_filename));
        }
}



