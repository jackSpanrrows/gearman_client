<?php
/**
 * @author AChan
 * @Date   20150609
 * Since 1.1  优化版本
 */
require 'QueryList.class.php';
$worker = new GearmanWorker();
$worker->addServer();

$worker->addFunction('getIndexAppList', function(GearmanJob $job){
    $item = $job->workload();
    $item = explode("##",$item);
    $url = $item[0];
    $k = $item[1];
    $sourceid = $item[2];
    return getTongBuTuiIndexAppList($url,$k,$sourceid);
    
});
while ($worker->work());


/**
 * 获取同步推热门最新系列数据
 * @param type $url        URL地址
 * @param type $k          分类
 * @param type $sourceid   来源ID
 * @return string  SQL语句
 */
function getTongBuTuiIndexAppList($url,$k,$sourceid)
{
    $reg = array('appName' => array('.app-pod ul li img','alt'), 'href' => array('.app-pod ul li img', 'src') , 'version' => array('.category', 'text'),'download_url' => array('.app-pod .icon ','href'),'appleid'=>array('.app-pod ul li','appleid'));

    $sj = QueryList::Query($url, $reg);

    $columnArr = $sj->jsonArr;
    
    $i = 0;
    
    $sql = "REPLACE INTO app_get_base_infor(APPLEID,APPNAME,APPICON,DISPLAYVERSION,APPCATEGORY,SOURCEID,INSERTTIME,APPDOWNLOADURL) values";
    
    $sourceid = isset($sourceid) ? intval($sourceid) : 3;
    
    foreach($columnArr as $key => $value)
    {
        $appName = addslashes($value['appName']);
        
        $version = explode("|",$value['version']);
        
        $display_version = $version[0]; 
        
        $app_icon = addslashes($value['href']);
        
        $app_download_url = "http://app.tongbu.com".$value['download_url'];
        
        $sql.= "('{$value['appleid']}','{$appName}','{$app_icon}','{$display_version}','{$k}', {$sourceid} ,'".date('Y-m-d H:i:s',time())."','{$app_download_url}'),";
        
    	$i++;
    }
    //当前页应用统计
    $onePageApp = count($columnArr);

    //第二页应用个数
    $secpage_app_num = 21;

    //总共还需获取页数
    $totalPage = ceil((200-$onePageApp) / $secpage_app_num) + 1;//还需9页

    //获取后面几页地址
    $sj->setQuery(array('current_page' => array('.pagination  a', 'href')));

    $pageList = $sj->jsonArr;

    $needGetPage = array_slice($pageList, 0, $totalPage);//分页URL
    
    $current_page = substr($needGetPage[0]['current_page'], 0 , -2);
    
    for($j=2;$j<=$totalPage;$j++)
    {
    	$urls = "http://app.tongbu.com".$current_page.$j.'/';
       
    	$sj = QueryList::Query($urls, $reg);

    	$columnArrs = $sj->jsonArr;

    	foreach($columnArrs as $keys => $values)
    	{
    	    $appName = addslashes($values['appName']);
            
            $version = explode("|",$values['version']);
            
            $display_version = $version[0]; 
        
            $app_icon = addslashes($values['href']);

            $app_download_url = "http://app.tongbu.com".$values['download_url'];
            if($i > 200) break;  
            
            $sql.= "('{$values['appleid']}','{$appName}','{$app_icon}','{$display_version}','{$k}','{$sourceid}','".date('Y-m-d H:i:s',time())."','{$app_download_url}'),";
            
            $i++;
    	}

    }
    return $sql;
}

//getCurrentPageAppList("http://app.tongbu.com/iphone-tuijian-1-1/","热门游戏",2);
//$urls = "http://app.tongbu.com".$val['current_page'];




