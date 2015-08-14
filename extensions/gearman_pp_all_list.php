<?php
/**
 * @author AChan by NetBeans IDE 8.0.2
 * @Date   20150616
 * Since 1.0  
 */
require 'QueryList.class.php';
$worker = new GearmanWorker();
$worker->addServer();

$worker->addFunction('getAppAllList', function(GearmanJob $job){
    $item = $job->workload();
    $item = explode("##",$item);
    $url = $item[0];
    $k = $item[1];
    $sourceid = $item[2];
    return getPPAllList($url,$k,$sourceid);
    
});
while ($worker->work());


function getPPAllList($url,$k,$sourceid)
{
    //$every_pages = $url;
    $url = substr($url,0,-2);
    $reg1 = array('appname' => array('.app_a','title'), 'url' => array('.app_a', 'href') , 'icon' => array('.app_a img', 'src'));
    $reg2 = array('version' => array('.title-stat .txt ul:eq(0) li:eq(0)','text'));
    $num = 100;
    $onePageApp = 30;
    $totalPage = ceil(($num-$onePageApp) / $onePageApp) + 1;//还需3页
    $preg = "/\d{5,9}/";
    $sql = "";
    $j=1;
    $sourceid = isset($sourceid) ? intval($sourceid) : 3;
    for($i=1;$i<=$totalPage;$i++)
    {
        $current_url = $url.$i.'/';
        $sj1 = QueryList::Query($current_url, $reg1);
        $columnArr1 = $sj1->jsonArr;
        foreach($columnArr1 as $val)
        {
            $sj2 = QueryList::Query($val['url'], $reg2);
            $columnArr2 = $sj2->jsonArr;
            foreach($columnArr2 as $v)
            {
                $version = explode("：",$v['version']);
                $appname = addslashes($val['appname']);
                $downloadUrl = $val['url'];
                preg_match($preg,$downloadUrl,$match);
                $str = $match[0];
                $appid = GetHtmlCode($str);
                if($j>100) goto end;
                $sql.= "('{$appid}','{$appname}','{$val['icon']}','{$version[1]}','{$k}',{$sourceid} ,'".date('Y-m-d H:i:s')."','{$downloadUrl}'),";  
                
                $j++;
            }
        }
    }
    end:
    return $sql;
}



/**
 * 此方法通过网页数字ID和PP助手客户端接口获取真实APPID
 * @param type $id   数字ID，为6到9位不等,site 为1 为越狱，3为正版软件
 * @return type      返回字符串COMID
 */
function GetHtmlCode($id)
{
    $url = "http://pppc2.25pp.com/pp_api/ios.php";
    $data = array('site' => 1, 'id' => $id);
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
    return $buid->buid;

}
