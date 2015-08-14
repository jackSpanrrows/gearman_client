<?php

$worker = new GearmanWorker();

$worker->addServer();

$worker->addFunction('getVersion', function(GearmanJob $job){
    $item = $job->workload();
    return GetHtmlCode($item);
});


function GetHtmlCode($url)
{
    $ch = curl_init();//初始化一个cur对象
    curl_setopt($ch, CURLOPT_URL, $url);//设置需要抓取的网页
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置curl参数，要求结果保存到字符串中还是输出到屏幕上
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置链接延迟

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //设置为true表示返回抓取的内容，而不是直接输出到浏览器上。TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly
    curl_setopt($ch, CURLOPT_AUTOREFERER, true); //自动设置referer。TRUE to automatically set the Referer: field in requests where it follows a Location: redirect.
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //跟踪url的跳转，比如301, 302等
    curl_setopt($ch, CURLOPT_MAXREDIRS, 2); //跟踪最大的跳转次数
    curl_setopt($ch, CURLOPT_HEADER, 0); //TRUE to include the header in the output.
    curl_setopt($ch, CURLOPT_ENCODING, ""); //接受的编码类型,The contents of the "Accept-Encoding: " header. This enables decoding of the response. Supported encodings are "identity", "deflate", and "gzip". If an empty string, "", is set, a header containing all supported encoding types is sent.
//    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    $HtmlCode = curl_exec($ch);//运行curl，请求网页
    return $HtmlCode;
//    preg_match($this->pattern, $HtmlCode, $match);
//    if (isset($match[1])) {
//        return  $match[1];
//    } else {
//        return '';
//    }
}

while ($worker->work());
