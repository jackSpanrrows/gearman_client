<?php
/*
 * @Author AChan
 * Date 20150318
 * describe 抓取同步推软件应用 此文件为初期写的代码，请查看task_tongbu_get_gearman_test.php文件
 */
/****************************************************************************************************/
$dir = dirname(__FILE__).'/';

include_once($dir.'../config/config.class.php');

$client = new GearmanClient();

$client->addServer();

$beginTime = time();

/****************************************同步推热门应用游戏Begin*****************************************/
$urlArr = array("热门游戏" =>"http://app.tongbu.com/iphone-tuijian-1-1/","热门应用" => "http://app.tongbu.com/iphone-tuijian-2-1/", "最新游戏" => "http://app.tongbu.com/iphone-tuijian-3-1/" , "最新应用" => "http://app.tongbu.com/iphone-tuijian-4-1/" , "装机必备"=> "http://app.tongbu.com/iphone-tuijian-5-1/");

//foreach($urlArr as $key => $url)
//{
//   getCurrentPageAppList($url,TOTAL,$key,$db,$log_filename,TONGBUTUI_SOURCEID);
//}

/**
 * 此方法用于获取同步推热门游戏应用并入库处理
 * @param type $url     需抓取网页
 * @param type $total   抓取数量
 * @param type $k       分类名称
 * @param type $db      数据库
 * @param type $log_filename   日志文件
 * @param type $sourceid       来源ID
 */
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
    $totalPage = ceil(($total-$onePageApp) / $secpage_app_num)+1;//9页
    //获取后面几页地址
    $sj->setQuery(array('current_page' => array('.pagination a', 'href')));

    $pageList = $sj->jsonArr;
    
    $needGetPage = array_slice($pageList, 0, $totalPage);   //分页URL
    
    $current_page = substr($needGetPage[0]['current_page'], 0 , -2);
    
    for($j=2;$j<=$totalPage;$j++)
    {        
    	$urls = DEFAULT_DOMAIN.$current_page.$j.'/';
        
    	$sj = QueryList::Query($urls, $reg);

    	$columnArrs = $sj->jsonArr;

    	foreach($columnArrs as $keys => $values)
    	{
    	    $appName = addslashes($values['appName']);
            
            $version = explode("|",$values['version']);
            
            $display_version = $version[0]; 
        
            $app_icon = addslashes($values['href']);

            $app_download_url = "http://app.tongbu.com".$values['download_url'];

            if($i > 199) goto end;  
            
            $sql.= "('{$values['appleid']}','{$appName}','{$app_icon}','{$display_version}','{$k}','{$sourceid}','".date('Y-m-d H:i:s',time())."','{$app_download_url}'),";
            
            $i++;
    	}

    }
    end:
    insertDb($db,$sql,$log_filename,WEB_NAME_1,$k);
}

/****************************************同步推热门应用游戏End *****************************************/


/*************************同步推iTunes中国排行榜(免费榜100,收费榜100,畅销榜100)Begin******************/
$categoryArr = array("Free" => "iPhone免费软件排行榜","noFree" => "iPhone收费软件排行榜", "Grow"=> "iPhone畅销软件排行榜");

//foreach($categoryArr as $isFree => $category)
//{
//    $columnArr[$isFree] = getCurrentIphoneAppList(IPHONE_PAIHANG_ITUNES_CN,$category,$isFree,$db,$log_filename,TONGBUTUI_SOURCEID);
//}
/**
 * 
 * @param type $url
 * @param type $category
 * @param type $isFree
 * @param type $db
 * @param type $log_filename
 * @param type $sourceid
 */
function getCurrentIphoneAppList($url,$category,$isFree,$db,$log_filename,$sourceid)
{
    $sql = "REPLACE INTO ". TABLE . "(APPLEID,APPNAME,APPICON,DISPLAYVERSION,APPCATEGORY,SOURCEID,INSERTTIME,APPDOWNLOADURL) values";
    $i = 0;
    switch($isFree){
        case "Free":
            $reg = array('appName' => array('.free .info .title','text'),  'version' => array('.free .downcount', 'text') , 'url' => array('.free .icon img'  ,'lz_src'),'download_url' => array('.free  .icon','href'));
            break;
        case "noFree":
            $reg = array('appName' => array('.nofree .info .title','text'),  'version' => array('.nofree .downcount', 'text') , 'url' => array('.free .icon img' , 'lz_src'),'download_url' => array('.nofree  .icon','href'));
            break;
        default:
            $reg = array('appName' => array('.grow .info .title','text'),  'version' => array('.grow .downcount', 'text') , 'url' => array('.free .icon img' , 'lz_src') , 'download_url' => array('.grow  .icon','href'));
            break;
    }
    $sj = QueryList::Query($url, $reg);
    
    $columnArr = $sj->jsonArr;
    
    foreach($columnArr as $key => $value)
    {
    	$version = explode("：",$value['version']);
        
    	$app_name = addslashes($value['appName']);
        
        $appleid = strstr($value['download_url'],'_',TRUE);
        
        $appleid = substr($appleid,1);

        $app_download_url = "http://app.tongbu.com".$value['download_url'];
        
        $display_version = trim($version[1]);
        
        $app_icon = $value['url'];
        
        $href = "";

    	if($i > 99) 
    	{
    	    goto end;
    	}
        $sql.= "('{$appleid}','{$app_name}','{$app_icon}','{$display_version}','{$category}', {$sourceid} ,'".date("Y-m-d,H:i:s")."','{$app_download_url}'),";
        $i++;
    }
    end:
    insertDb($db,$sql,$log_filename,$category,WEB_NAME_1);
}

/*************************同步推iTunes中国排行榜(免费榜100,收费榜100,畅销榜100)End***************************/

/*************************同步推iTunes中国热门榜(精选软件排行榜-100,热门软件排行榜-100)Begin******************/
$categoryArr = array("Hotest" => "热门软件排行榜","Goodest" => "精选软件排行榜");
//foreach ($categoryArr as $key => $val)
//{
//    getHotestAllList($client,IPHONE_TONGBU_HOT_ALL,$val,$db,$log_filename,$key,TONGBUTUI_SOURCEID);
//}

function getHotestAllList($client,$url,$category,$db,$log_filename,$key,$sourceid)
{
    $sql = "REPLACE INTO ". TABLE . "(APPLEID,APPNAME,APPICON,DISPLAYVERSION,APPCATEGORY,SOURCEID,INSERTTIME,APPDOWNLOADURL) values";
    $i = 0;
    switch ($key)
    {
        case 'Hotest':
            $reg = array('appname' => array('.free .title','text'),'url' => array('.free .icon'  ,'href'),'icon' => array('.free .icon img','lz_src'));
            break ;
        case 'Goodest':
            $reg = array('appname' => array('.nofree .title','text'),'url' => array('.nofree .icon'  ,'href'),'icon' => array('.nofree .icon img','lz_src'));
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
        $sql.= "('{$appid[0]}','{$appname}','{$icon}','{$version}','{$category}', {$sourceid} ,'".date("Y-m-d,H:i:s")."','{$app_url}'),";
        $i++;
    }
    insertDb($db,$sql,$log_filename,$category,WEB_NAME_1);
}



//获取版本号
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
        $client->addTask('getVersion', $url,null,$i);
	$i++;
    }

    $client->runTasks();
    
    return $data;
}

/*************************同步推iTunes中国热门榜(精选软件排行榜-100,热门软件排行榜-100)End******************/

/**********************************25pp首页数据抓取******************************************************/
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
//getPPScheduleData(IPHONE_25PP,$client);
/**********************************25pp首页数据抓取End********************************************************/
/**********************************25pp软件首页Begin*********************************************************/
/**
 * 
 * @param type $key
 * @param type $url
 * @param type $client
 * @param type $db
 * @param type $log_filename
 */
function getPPAppList($key,$url,$client,$db,$log_filename)
{   
    $beginTime = microtime();
    $sql = "REPLACE INTO ". TABLE . "(APPLEID,APPNAME,APPICON,DISPLAYVERSION,APPCATEGORY,SOURCEID,INSERTTIME,APPDOWNLOADURL) values";
    $i = 0;
    switch ($key)
    {
        case 'software':
            $reg = array(
                'PP最新软件' => array('.sort_ul li:eq(0) a','href'),
                'PP最热软件' => array('.sort_ul li:eq(1) a','href'),
                'PP推荐软件' => array('.sort_ul li:eq(2) a','href'),        
            );
            break;
        case 'game':
            $reg = array(
                'PP最新游戏' => array('.sort_ul li:eq(0) a','href'),
                'PP最热游戏' => array('.sort_ul li:eq(1) a','href'),
                'PP推荐游戏' => array('.sort_ul li:eq(2) a','href'),        
            );
            break;
    }
    $sj = QueryList::Query($url,$reg);
    $columnArr = $sj->jsonArr;
    $client->setCompleteCallback(function (GearmanTask $task) use (&$returnPPAllData) 
    {
         $returnPPAllData[] = $task->data();
    });
    foreach ($columnArr as $v)
    {
       foreach ($v as $k => $appUrl)
       {
            $client->addTask('getAppAllList', $appUrl.'##'.$k."##".PP_SOURCEID);
       }
    }
    $client->runTasks();
    $sql.= implode('',$returnPPAllData);
    insertDb($db,$sql,$log_filename,WEB_NAME_2);
}

//getPPAppList("software",PP_SOFTWARE,$client,$db,$log_filename);
//getPPAppList("game",PP_GAME,$client,$db,$log_filename);

/**********************************25pp软件首页End*********************************************************/
/**********************************91软件首页Begin*********************************************************/
function getKwhAppList($url,$client,$db,$log_filename)
{
    $reg = array(
                "recommend_game" => array("#tabMain2 li h4 a",'href'),
                "recommend_software" => array("#tabMain3 li h4 a",'href'),
                "ranking_game" => array('.game-rank ol li .rank-s-name ','href'),
                "ranking_software" => array('.soft-rank ol li .rank-s-name ','href'),
                "ranking_online_game" => array('.hot-rank ol li .rank-s-name ','href'),
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
            $client->addTask('getKwhAppList', $appUrl.'##'.$k);
        }
    }
    $client->runTasks();
    $sql = "REPLACE INTO ". TABLE . "(APPLEID,APPNAME,APPICON,DISPLAYVERSION,APPCATEGORY,SOURCEID,INSERTTIME,APPDOWNLOADURL) values";
    $sql.= implode(',',$data);
    $sql.= ',';
    insertDb($db,$sql,$log_filename,WEB_NAME_3);
}
//getKwhAppList(APP_KUWANHUI ,$client ,$db ,$log_filename);
/**********************************91软件首页End*********************************************************/

/**********************************91软件热门游戏**Begin*************************************************/

$kwh_hotest_game_url = array("Selection" => "http://play.91.com/iphone/Game/" ,"Chosen2" => "http://play.91.com/iphone/Chosen2/" ,"Patch2" => "http://play.91.com/iphone/Patch2/" ,"Patch2Unlock" => "http://play.91.com/iphone/Patch2/index-1.html?type=unlock" ,"gameLarge" => "http://play.91.com/iphone/Large/");
function getKwhGameList($url,$category,$client,$db,$log_filename)
{
    //第一步获取最热最新评分URL地址，第二步获取页面应用URL地址
    $reg = array(
                "kwh_hotest_game" => array(".mt-18 .sort-by a:eq(0)",'href'),
                "kwh_newest_game" => array(".sort-by a:eq(1)",'href'),
                "kwh_scored_game" => array(".sort-by a:eq(2)",'href'),
            );

    $sj = QueryList::Query($url,$reg);
    $columnArr = $sj->jsonArr;
    $client->setCompleteCallback(function (GearmanTask $task) use (&$data) 
    {
        $data[] = json_decode($task->data(), true);
    });
    foreach($columnArr as $val)
    {
        foreach($val as $category => $appUrl)
        {
            $client->addTask('getKwhGameList', $appUrl.'##'.$category);
        }
    }
    
    $client->runTasks();
    $sql = "REPLACE INTO ". TABLE . "(APPLEID,APPNAME,APPICON,DISPLAYVERSION,APPCATEGORY,SOURCEID,INSERTTIME,APPDOWNLOADURL) values";
    $sql.= implode(',',$data);
    $sql.= ',';
    insertDb($db,$sql,$log_filename,WEB_NAME_3);
}
//$url = "http://play.91.com/iphone/Game/index-1.html";
//getKwhGameList($url, "Selection", $client, $db, $log_filename);
//die;
//foreach($kwh_hotest_game_url as $k => $url)
//{
//    getKwhGameList($url, "Selection", $client, $db, $log_filename);
//}

/**********************************91软件热门游戏**End***************************************************/
/**********************************91软件精选软件Begin***************************************************/
//$kwh_selection_software = array('kwh_soft' => 'http://play.91.com/iphone/Soft/','kwh_Star5' => 'http://play.91.com/iphone/Star5/', 'kwh_bibei' => 'http://play.91.com/iphone/bibei/');
$kwh_selection_software = array('kwh_soft_newest' => 'http://play.91.com/iphone/Soft/index-16.html','kwh_soft_hotest' => 'http://play.91.com/iphone/Soft/hot-16.html','kwh_soft_score' => 'http://play.91.com/iphone/Soft/score-16.html');
function getKwhSoftwareList($url,$k,$client,$db,$log_filename)
{
    $reg = array(                
                    "kwh_hotest_software" => array(".sort-by a:eq(0)",'href'),
                    "kwh_newest_software" => array(".sort-by a:eq(1)",'href'),
                    "kwh_scored_software" => array(".sort-by a:eq(2)",'href'),
            );
    $sj = QueryList::Query($url, $reg);
    $columnArr = $sj->jsonArr;
    print_r($columnArr);
}

//foreach($kwh_selection_software as $k => $url)
//{
//    getKwhSoftwareList($url,$k,$client,$db,$log_filename);
//}
/**********************************91软件精选软件**End***************************************************/



echo "\n";
echo time()-$beginTime.'s';echo "\n";



//function GetHtmlCode($id,$site=3)
//{
//    $url = "http://pppc2.25pp.com/pp_api/ios.php";
//    $data = array('site' => 3, 'id' => $id);
//    $ch = curl_init();//初始化一个cur对象
//    ob_start();
//    curl_setopt ($ch, CURLOPT_URL, $url);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //设置为true表示返回抓取的内容，而不是直接输出到浏览器上
//    curl_setopt($ch, CURLOPT_AUTOREFERER, true); //自动设置referer
//    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //跟踪url的跳转，比如301, 302等
//    curl_setopt($ch, CURLOPT_MAXREDIRS, 2); //跟踪最大的跳转次数
//    curl_setopt($ch, CURLOPT_HEADER, 0); 
//    curl_setopt($ch, CURLOPT_ENCODING, ""); //接受的编码类型
//    $returnId = curl_exec($ch);
//    curl_close($ch);
//    $buid = json_decode($returnId,true);
//    if($buid['code']!=400 && isset($buid['buid']))
//        return $buid['buid'];
//    else
//        return NULL;
//}

//    $onePageApp = 27;//每页27条
//    $get_app_num = 100;//每项100条应用
//    $totalPage = ceil($get_app_num / $onePageApp) + 1;//所需页数
//    $string = "";
//
//    $reg_one = array(
//                'url' => array('.ss-list-top img','src'),
//                'appname' => array('.ss-list-top img','title'),
//                'version' => array('.ss-list-ver','text'),
//            );
//
//        foreach($columnArr as $val)
//        {
//            foreach($val as $k => $appurl)
//            {
//                for($i=1;$i<6;$i++)
//                {
//                    if($i==1)
//                    {
//                        $str_url = $appurl;
//                    }else{
//                        $str_url = str_replace('-1.','-1-'.$i.'.',$appurl);
//                    }
//                    $sj_one = QueryList::Query($str_url, $reg_one);
//                    $columnArr_one = $sj_one->jsonArr;
//                    print_r($columnArr_one);
//                }
//            }
//        }
//        
//        die;