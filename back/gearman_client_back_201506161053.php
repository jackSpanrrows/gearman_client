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

$urlArr = array("热门游戏" =>"http://app.tongbu.com/iphone-tuijian-1-1/","热门应用" => "http://app.tongbu.com/iphone-tuijian-2-1/", "最新游戏" => "http://app.tongbu.com/iphone-tuijian-3-1/" , "最新应用" => "http://app.tongbu.com/iphone-tuijian-4-1/" , "装机必备"=> "http://app.tongbu.com/iphone-tuijian-5-1/");

function getIndexAppListSql($client,$urlArr)
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
    
    return $data;
}
$return_index_data = getIndexAppListSql($client , $urlArr);

foreach ($return_index_data as $v)
{
    insertDb($db,$v,$log_filename,WEB_NAME_1);
}
echo time() - $beginTime."s";
die;
//die;
/**
 * 插入数据库方法
 * @param type $db          数据库名
 * @param type $sql         SQL语句
 * @param type $log_filename    日志文件
 */
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
//
//
//
/*****************************************iTunes排行榜-中国区Begin***************************************************************/
$categoryArr = array("Free" => "iPhone免费软件排行榜","noFree" => "iPhone收费软件排行榜", "Grow"=> "iPhone畅销软件排行榜");

function getSoftRankingList($client,$categoryArr)
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
    
    return $returnData;
}
//
//
$return_soft_ranking_data = getSoftRankingList($client,$categoryArr);

//foreach ($return_soft_ranking_data as $v)
//{
//    insertDb($db,$v,$log_filename);
//}
//die;
/*****************************************iTunes排行榜-中国区End***************************************************************/

/*************************同步推iTunes中国热门榜(精选软件排行榜-100,热门软件排行榜-100)Begin**************************************/
$categoryArr = array("Hotest" => "热门软件排行榜","Goodest" => "精选软件排行榜");

foreach ($categoryArr as $key => $val)
{
    getHotestAllList($client,IPHONE_TONGBU_HOT_ALL,$val,$db,$log_filename,$key,TONGBUTUI_SOURCEID);
}
die;
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
 
    $data = getHotVersion($client, $columnArr,$category);

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
    insertDb($db,$sql,$log_filename,$category);
}



//获取版本号
function getHotVersion($client,$columnArr,$category)
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
/*************************同步推iTunes中国热门榜(精选软件排行榜-100,热门软件排行榜-100)End*************************************/


function getPPScheduleData($url,$client)
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
            //$appList[] = array('appname' => $data[0]['appname'], 'appversion' => $data[0]['appversion'], 'icon' => $data[0]['icon'], 'source' => $k, 'href' => $appUrl);
        }
    }
    
    $client->runTasks();
    
    var_dump($data);
//    return $data;
}


function GetHtmlCode($url)
{
    
    $data = array('site' => 1, 'id' => 625123);
    $ch = curl_init();//初始化一个cur对象
    ob_start();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //设置为true表示返回抓取的内容，而不是直接输出到浏览器上
    curl_setopt($ch, CURLOPT_AUTOREFERER, true); //自动设置referer
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //跟踪url的跳转，比如301, 302等
    curl_setopt($ch, CURLOPT_MAXREDIRS, 2); //跟踪最大的跳转次数
    curl_setopt($ch, CURLOPT_HEADER, 0); 
    curl_setopt($ch, CURLOPT_ENCODING, ""); //接受的编码类型
    $returnId = curl_exec($ch);
    curl_close($ch);
    $buid = json_decode($returnId);
    echo $buid->buid;

}
$url = "http://pppc2.25pp.com/pp_api/ios.php";
//GetHtmlCode($url);

getPPScheduleData(IPHONE_25PP,$client);
//echo time() - $beginTime."s";IPHONE_25PP;

