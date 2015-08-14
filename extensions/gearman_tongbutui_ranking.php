<?php
#class_name gearman_tongbutui_ranking.php
require 'QueryList.class.php';
$worker = new GearmanWorker();
$worker->addServer();
$worker->addFunction('getRankingAppInfo', function(GearmanJob $job){
    $item = $job->workload();
    $item = explode("##",$item);
    $url = $item[0];
    $isFree = $item[1];
    $category = $item[2];
    $sourceid = $item[3];
    return getTongBuTuiRanking($url,$category,$isFree,$sourceid);
    
});
while ($worker->work());

function getTongBuTuiRanking($url,$category,$isFree,$sourceid)
{
    $sql = "REPLACE INTO app_get_base_infor(APPLEID,APPNAME,APPICON,DISPLAYVERSION,APPCATEGORY,SOURCEID,INSERTTIME,APPDOWNLOADURL) values";
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

    	if($i > 100) 
    	{
    	    break;
    	}
        $sql.= "('{$appleid}','{$app_name}','{$app_icon}','{$display_version}','{$category}', '2' ,'".date("Y-m-d,H:i:s")."','{$app_download_url}'),";
        $i++;
    }
    return $sql;
}