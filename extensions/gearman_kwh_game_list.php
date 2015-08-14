<?php
/**
 * @author Achan by NetBeans IDE 8.0.2
 * @Date   201506251559
 * @Description  此是91助手酷玩汇游戏页面获取APPID 多进程处理
 * @ 抓取有问题此脚本待完成
 */
$dir = dirname(__FILE__).'/';

require 'QueryList.class.php';

include_once($dir.'../config/config.class.php');

$worker = new GearmanWorker();

$worker->addServer();

$worker->addFunction('getKwhGameList', function(GearmanJob $job){
    $item = $job->workload();
    $item = explode("##",$item);
    $url = $item[0];
    $category = $item[1];
    return getKwhGameList($url,$category);

});
while ($worker->work());


function getKwhGameList($url,$category)
{
    //这里分2步，第一步是获取每个分类首页应用信息，第二步获取剩余应用信息
    $reg_one = array(
                'icon' => array('.ss-list-top img','src'),
                'appname' => array('.ss-list-top img','title'),
                'version' => array('.ss-list-ver','text'),
                'url' => array('.ss-btn','href'),
            );
    $reg_two = array('download_url' => array('.ssoft-btns .s-down-btn-2','href'),'download_pc' => array('.ssoft-btns .s-down-btn-1','href'));
    $onePageApp = 27;//每页27条
    $get_app_num = 100;//每项100条应用
    $totalPage = ceil($get_app_num / $onePageApp);//所需页数
    $preg = "/\d{5,10}/";
    $sql = "";
    for($i=1;$i<=4;$i++)
    {
        if($i==1)
        {
            $current_url = $url;
        }else{
            $current_url = str_replace('-1.','-1-'.$i.'.',$url);
        }
        $sj_one = QueryList::Query($current_url, $reg_one);
        $columnArr_one = $sj_one->jsonArr;
        foreach($columnArr_one as $val)
        {
            $sj_two = QueryList::Query($val['url'], $reg_two);
            $columnArr_two = $sj_two->jsonArr;
            foreach($columnArr_two as $v)
            {
                $downloadUrl = isset($v['download_url'])?$v['download_url']:NULL;
                $version = str_replace('V','',$val['version']);
                $appname = addslashes($val['appname']);
                if($downloadUrl != "")
                {
                    preg_match($preg,$downloadUrl,$match);
                    $str = trim($match[0]);
                    $appid = GetComAppid($str);
                    if(empty($appid) == TRUE)
                    {
                        $string = addslashes(getTrueAppid($appname));
                        $sql.= "('{$string}','{$appname}','{$val['icon']}','{$version}','{$category}', 3 ,'".date('Y-m-d H:i:s')."','{$val['url']}')";  
                        continue;
                    }
                    $sql.= "('{$appid}','{$appname}','{$val['icon']}','{$version}','{$category}', 1 ,'".date('Y-m-d H:i:s')."','{$val['url']}')";
                    continue;
                }
                $string = addslashes(getTrueAppid($appname));
                $sql.= "('{$string}','{$appname}','{$val['icon']}','{$version}','{$category}', 3 ,'".date('Y-m-d H:i:s')."','{$val['url']}')";
            }
        }
    }
    return json_encode($sql);
}