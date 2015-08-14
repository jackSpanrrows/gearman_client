<?php
require 'QueryList.class.php';
$worker = new GearmanWorker();

$worker->addServer();

$worker->addFunction('getAppVersion', function(GearmanJob $job){
    $item = $job->workload();
    return GetHtmlCode($item);
});

function GetHtmlCode($url)
{
    $ch = curl_init();//初始化一个cur对象
    curl_setopt($ch, CURLOPT_URL, $url);//设置需要抓取的网页
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //设置为true表示返回抓取的内容，而不是直接输出到浏览器上
    curl_setopt($ch, CURLOPT_AUTOREFERER, true); //自动设置referer
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //跟踪url的跳转，比如301, 302等
    curl_setopt($ch, CURLOPT_MAXREDIRS, 2); //跟踪最大的跳转次数
    curl_setopt($ch, CURLOPT_HEADER, 0); 
    curl_setopt($ch, CURLOPT_ENCODING, ""); //接受的编码类型
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    $HtmlCode = curl_exec($ch);//运行curl，请求网页
    return $HtmlCode;

}

while ($worker->work());