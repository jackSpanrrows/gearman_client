<?php
/**
 * @author Achan by NetBeans IDE 8.0.2
 * @Date   201506151037
 * @Description  此是PP第一个Gearman work后台进程，用于获取首页不同栏目APP相关信息
 */ 
require 'QueryList.class.php';
$worker = new GearmanWorker();
$worker->addServer();

$worker->addFunction('getPPListData', function(GearmanJob $job){
    $item = $job->workload();
    $item = explode("##",$item);
    $url = $item[0];
    $category = $item[1];
    return getPPIndexSql($url,$category);

});
while ($worker->work());

/**
 * 此方法返回SQL语句
 * @param type $url
 * @param type $categroy
 * @return type
 */
function getPPIndexSql($url,$categroy)
{
    $reg = array(
                'appname' => array('.txt:eq(0) h1', 'html'),
                'appversion' => array('.txt:eq(0) ul li:eq(0)', 'html'),
                'icon' => array('.pic:eq(0) img', 'src')
            );
    $sj = QueryList::Query($url, $reg);
    $tmp = $sj->jsonArr;
    $cid = getmypid();
    $version = explode("：",$tmp[0]['appversion']);
    $preg = "/\d+\//";
    preg_match($preg,$url,$match);
    $str = substr($match[0],0,-1);    
    $appid = GetHtmlCode($str);
    $sql = "('{$appid}','{$tmp[0]['appname']}','{$tmp[0]['icon']}','{$version[1]}','{$categroy}', 2 ,'".date('Y-m-d H:i:s')."','{$url}')";
    return json_encode($sql);
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
    $buid = json_decode($returnId,true);
    if($buid['code']!=400)
        return $buid['buid'];
    else
        return NULL;

}




//    return GetHtmlCode($id);
//    $reg = array(
//        'appname' => array('.txt:eq(0) h1', 'html'),
//        'appversion' => array('.txt:eq(0) ul li:eq(0)', 'html'),
//        'icon' => array('.pic:eq(0) img', 'src')
//    );
//    $sj = QueryList::Query($url, $reg);
//    $tmp = $sj->jsonArr;
//    $cid = getmypid();
//    $version = explode("：",$tmp[0]['appversion']);
//    //$str = $tmp[0]['appname']."##".$tmp[0]['appversion']."##".$tmp[0]['icon']."##".$item[0]."##".$item[1];
//    $return = array('appname'=>$tmp[0]['appname'],'appversion'=>$version[1],'icon'=>$tmp[0]['icon'],'appUrl' => $url,'gid'=>$cid);
//    return json_encode($return);