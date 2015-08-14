<?php
/**
 * @author Achan by NetBeans IDE 8.0.2
 * @Date   201506181444 于201506251550测试完成
 * @Description  此是91助手酷玩汇第一个Gearman work后台进程，用于获取首页不同栏目APP相关信息
 */
$dir = dirname(__FILE__).'/';

require 'QueryList.class.php';

include_once($dir.'../config/config.class.php');

$worker = new GearmanWorker();

$worker->addServer();

$worker->addFunction('getKwhAppList', function(GearmanJob $job){
    $item = $job->workload();
    $item = explode("##",$item);
    $url = $item[0];
    $category = $item[1];
    return getKwhIndexList($url,$category);

});
while ($worker->work());

function getKwhIndexList($url,$category)
{
    $reg = array("appname" => array('.ssoft-name','text'),'icon' => array('.ssoft-img','src'),'version' => array('.ssoft-info:eq(0)','text'),'appid' => array('.s-down-btn-2','href'));
    $sj = QueryList::Query($url, $reg);
    $columnArr = $sj->jsonArr;
    $str = "";
    $appid = '';
    foreach($columnArr as $key => $val)
    {
        $version = explode('|', $val['version']);
        $appversion = explode("：",$version[0]);
        $version = trim($appversion[1]);
        $appname = $val['appname'];
        if ($val['appid'] != "")
        {
            $appUrl = preg_match('/\d{9}/',$val['appid'],$match);
            $appid = $match[0];
            $appleid = GetHtmlCode($appid);
            if(empty($appleid) == TRUE)
            {
                $string = getTrueAppid($appname);
                $sql.= "('{$string}','{$appname}','{$val['icon']}','{$version}','{$category}', 1 ,'".date('Y-m-d H:i:s')."','{$url}')";
                continue;
            }
            $sql.= "('{$appleid}','{$appname}','{$val['icon']}','{$version}','{$category}', 1 ,'".date('Y-m-d H:i:s')."','{$url}')";
            continue;
        }
        $string = getTrueAppid($appname);
        $sql.= "('{$string}','{$appname}','{$val['icon']}','{$version}','{$category}', 1 ,'".date('Y-m-d H:i:s')."','{$url}')";
    }
    return json_encode($sql);
}

