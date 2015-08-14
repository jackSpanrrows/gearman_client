<?php
/*
 * @Author AChan
 * Date 二次修改 20150520
 * describe 抓取同步推软件应用
 */
/****************************************************************************************************/
$dir = dirname(__FILE__).'/';

include_once($dir.'../config/config.class.php');

$client = new GearmanClient();

$client->addServer();

$beginTime = time();

$total = TOTAL;

/*********************************************同步推热门最新装机必备游戏应用Begin************************************************/
$urlArr = array("热门游戏" =>"http://app.tongbu.com/iphone-tuijian-1-1/","热门应用" => "http://app.tongbu.com/iphone-tuijian-2-1/", "最新游戏" => "http://app.tongbu.com/iphone-tuijian-3-1/" , "最新应用" => "http://app.tongbu.com/iphone-tuijian-4-1/" , "装机必备"=> "http://app.tongbu.com/iphone-tuijian-5-1/");

/**
 * 同步推热门最新以及装机必备数据抓取方法
 * @param type $client      gearman 客户端对象
 * @param type $urlArr      抓取的URL数组
 * @param type $db          数据库
 * @param type $log_filename   日志文件
 * @param type $webName     来源网站名称
 */
function getIndexAppListSql($client,$urlArr,$db,$log_filename)
{
    $client->setCompleteCallback(function(GearmanTask $task) use (&$data) 
    {
        $data[] = $task->data();
    });   
    foreach($urlArr as $key => $url)
    {
        $client->addTask('getAppInfo', $url.'##'.$key,'##'.TONGBUTUI_SOURCEID);
    }
    $client->runTasks();
    
    foreach ($data as $v)
    {
      insertDb($db,$v,$log_filename,WEB_NAME_1);  
    }
}

getIndexAppListSql($client,$urlArr,$db,$log_filename);// 初步测试20S左右
//echo time() - $beginTime."s";  

/*****************************************同步推热门最新装机必备游戏应用End********************************************************/

/*****************************************iTunes排行榜-中国区Begin***************************************************************/
$categoryArr = array("Free" => "iPhone免费软件排行榜","noFree" => "iPhone收费软件排行榜", "Grow"=> "iPhone畅销软件排行榜");

function getSoftRankingList($client,$categoryArr,$db,$log_filename)
{
    $client->setCompleteCallback(function(GearmanTask $task) use (&$returnData) 
    {
        $returnData[] = $task->data();
    });

    foreach($categoryArr as $isFree => $category)
    {
        $client->addTask('getRankingAppInfo', IPHONE_PAIHANG_ITUNES_CN.'##'.$isFree.'##'.$category.'##'.TONGBUTUI_SOURCEID);
    }

    $client->runTasks(); 
    
    foreach ($returnData as $val)
    {
        insertDb($db,$val,$log_filename,WEB_NAME_1);
    }
    //return $returnData;
}

getSoftRankingList($client,$categoryArr,$db,$log_filename);


/*****************************************iTunes排行榜-中国区End***************************************************************/

/*************************同步推iTunes中国热门榜(精选软件排行榜-100,热门软件排行榜-100)Begin**************************************/
$categoryArr = array("Hotest" => "热门软件排行榜","Goodest" => "精选软件排行榜");

foreach ($categoryArr as $key => $val)
{
    getHotestAllList($client,IPHONE_TONGBU_HOT_ALL,$val,$db,$log_filename,$key,TONGBUTUI_SOURCEID);
}

function getHotestAllList($client,$url,$category,$db,$log_filename,$key,$sourceid)
{
    $sql = "REPLACE INTO ". TABLE . "(APPLEID,APPNAME,APPICON,DISPLAYVERSION,APPCATEGORY,SOURCEID,INSERTTIME,APPDOWNLOADURL) values";
    $i = 0;
    switch ($key)
    {
        case 'Hotest':
            $reg = array('appname' => array('.free .title','text'),'url' => array('.free .icon'  ,'href'),'icon' => array('.free .icon img','lz_src'));
            break;
        case 'Goodest':
            $reg = array('appname' => array('.nofree .title','text'),'url' => array('.nofree .icon'  ,'href'),'icon' => array('.nofree .icon img','lz_src'));
            break;
    }
    
    $sj = QueryList::Query($url, $reg);
    
    $columnArr = $sj->jsonArr;
 
    $data = getHotVersion($client, $columnArr);

    ksort($data);
    
    foreach ($columnArr as $val)
    {
        $version = $data[$i];
        $appleid = str_replace('/','',$val['url']);
        preg_match('/\d+/',$appleid,$appid);
        $appname = $val['appname'];
        $icon = $val['icon'];
        $inserTime = date('Y-m-d H:i:s');
        $app_url = "http://app.tongbu.com".$val['url'];
        $sql.= "('{$appid[0]}','{$appname}','{$icon}','{$version}','{$category}', {$sourceid} ,'{$inserTime}','{$app_url}'),";
        $i++;
    }
    insertDb($db,$sql,$log_filename,WEB_NAME_1,$category);
}

/**
 * 获取同步推热门榜版本号
 * @param type $client    Gearman客户端对象
 * @param type $columnArr URL数组
 * @param type $category  分类
 * @return type
 */
function getHotVersion($client,$columnArr)
{
    //调用多进程处理获取应用版本号
    $client->setCompleteCallback(function(GearmanTask $task) use (&$data) 
    {
	$pattern = "/<span id=\"downversion\">(.*?)<\/span>/im";
	preg_match($pattern, $task->data(), $match);
	if (isset($match[1])) 
	{
	    $version =  $match[1];
	} else 
	{
	    $version = '';
	}
	$data[$task->unique()] = $version;
    });
    
    $i=0;
    foreach($columnArr as $val) 
    {
	$url = "http://app.tongbu.com".$val['url'];
        $appleid = str_replace('/','',$val['url']);
        preg_match('/\d+/',$appleid,$appid);
        $appname = $val['appname'];
        $icon = $val['icon'];
        $inserTime = date('Y-m-d H:i:s');
        $client->addTask('getVersion', $url,null,$i);
	$i++;
    }

    $client->runTasks();
    
    return $data;
}


echo time() - $beginTime."s";die;//88S
/*************************同步推iTunes中国热门榜(精选软件排行榜-100,热门软件排行榜-100)End*************************************/
/**********************************25pp首页数据抓取*************************************************************************/
/**
 * 25PP首页数据抓取
 * @param type $url
 * @param type $client
 * @param type $db
 * @param type $log_filename
 */
function getPPScheduleData($url,$client,$db,$log_filename)
{
    $i = 0;
    //先获取URL
    $reg = array(
                'gamelist'=>array(".gameRank:eq(0) li .nameDown a","href"),
                'applist'=>array(".gameRank:eq(1) li .nameDown a","href"),
                'hot_game'=>array(".gcp1:eq(0) dt a",'href'),
                'new_game'=>array(".gcp1:eq(1) dt a",'href'),
                'recommend_game'=>array(".gcp1:eq(2) dt a",'href'),
                'hot_app'=>array(".gcp2:eq(0) dt a",'href'),
                'new_app'=>array(".gcp2:eq(1) dt a",'href'),
                'recommend_app'=>array(".gcp2:eq(2) dt a",'href'),
            );
    $sj = QueryList::Query($url,$reg);
    
    $columnArr = $sj->jsonArr;
    
    $client->setCompleteCallback(function (GearmanTask $task) use (&$data) 
    {
        $data[] = json_decode($task->data(), true);
    });
    
    foreach($columnArr as $val)
    {
        foreach($val as $k=>$appUrl) 
        {
            $client->addTask('getAppInfo', $appUrl.'##'.$k);
        }
    }
    $client->runTasks();
    $sql = "REPLACE INTO ". TABLE . "(APPLEID,APPNAME,APPICON,DISPLAYVERSION,APPCATEGORY,SOURCEID,INSERTTIME,APPDOWNLOADURL) values";
    $sql.= implode(',',$data);
    $sql.= ',';
    insertDb($db,$sql,$log_filename,WEB_NAME_2);
}

$url = "http://pppc2.25pp.com/pp_api/ios.php";

getPPScheduleData(IPHONE_25PP,$client,$db,$log_filename);

echo time() - $beginTime."s";




//function insertDb($db,$sql,$log_filename)
//{
//    $date = date("Y-m-d H:i:s" , time());
//    try{
//            $db->beginTransaction();
//            $sql = substr($sql,0,-1);
//            $num = $db->exec($sql);//执行SQL语句返回总条数
//            $db->commit();
//            if($num>0)
//            {
//                $message = "成功插入{$num}条数据!";
//                error_log($date . "\t" . $message . "\r\n" , 3 , $log_filename);
//            }
//
//        } catch (PDOException $err) {
//            
//            $db->rollback();
//
//            die(error_log($date . "\t" . $err->getMessage() . "\r\n" , 3 , $log_filename));
//        }
//}

//$appList[] = array('appname' => $data[0]['appname'], 'appversion' => $data[0]['appversion'], 'icon' => $data[0]['icon'], 'source' => $k, 'href' => $appUrl);
//$url = "http://ios.25pp.com/app/1822858/";


//$data[$task->unique()] = $version;