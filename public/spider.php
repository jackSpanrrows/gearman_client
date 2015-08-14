<?php
/**
 * 获取同步推全部榜单
 */
include('simple_html_dom.php');
//热榜全部
$domain = 'http://app.tongbu.com';
$url = "http://app.tongbu.com/iphone-paihang-tb-hot-0-all/";
// get DOM from URL or file
$html = file_get_html($url);
$beginTime =time();
/************************ free data *****************************************/
$freeData = array();
//获取名称
$i = 0;
foreach ($html->find('div[class=top-list free] [class=title] a') as $title) {
    $appName = $title->plaintext;
    $freeData[$i]['name'] = $appName;
    $i++;
}
$i = 0;
//获取icon
foreach ($html->find('div[class=top-list free] a[class=icon] img') as $img) {
    $appIcon = $img->lz_src;
    $freeData[$i]['icon'] = $appIcon;
    $i++;
}
//var_dump($freeData);die;
//获取版本
$i = 0;
foreach ($html->find('div[class=top-list free] a[class=icon]') as $apphref) {
    $app_version = '';
    $appInfoUrl = $domain . $apphref->href;
    $freeData[$i]['inforurl'] = $appInfoUrl;
    $curlUrl[$i] = $appInfoUrl;
//    echo $i . '_' . $appInfoUrl . "\r\n";
//    $appinfor = GetHtmlCode($appInfoUrl);
    //   $pattern = "/<span id=\"downversion\">(.*?)<\/span>/im";
//        preg_match_all($pattern, $appd, $match);
    //   preg_match($pattern, $appinfor, $match);
    //   if (isset($match[1])) {
    //       $app_version = $match[1][0];
//        $freeData[$i]['appversion'] = $app_version;
    $i++;
//    }
}
$html->clear();
$i = 0;
$pattern = "/<span id=\"downversion\">(.*?)<\/span>/im";
//foreach ($freeData as $val) {
//    $html = GetHtmlCode($val['inforurl']);
//    echo $i . '__' . $val['inforurl'] . "\r\n";
//    preg_match($pattern, $html, $match);
//    if (isset($match[1])) {
//        $freeData[$i]['appversion'] = (string)$match[1][0];;
//    }
//    $i++;
//}
$appVersionArr = multiDownload($curlUrl);
$i = 0;
foreach($appVersionArr as $version){
    $freeData[$i]['appversion'] = $version;
    $i++;
}
var_dump($freeData);
echo "Run Time :".(time() - $beginTime);
die;

//function GetHtmlCode($url){
//    $ch = curl_init();//初始化一个cur对象
//    curl_setopt ($ch, CURLOPT_URL, $url);//设置需要抓取的网页
//    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);//设置crul参数，要求结果保存到字符串中还是输出到屏幕上
//    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,1000);//设置链接延迟
//    $HtmlCode = curl_exec($ch);//运行curl，请求网页
//    return $HtmlCode;
//}


function multiDownload($urlArray)
{
    if (empty($urlArray)) return false;

    $mh = curl_multi_init(); //curl_multi_init --  Returns a new cURL multi handle
    $curlArray = array();
    foreach ($urlArray as $i => $url) {
        $curlArray[$i] = curl_init($url);
        curl_setopt($curlArray[$i], CURLOPT_RETURNTRANSFER, true); //设置为true表示返回抓取的内容，而不是直接输出到浏览器上。TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly
        curl_setopt($curlArray[$i], CURLOPT_AUTOREFERER, true); //自动设置referer。TRUE to automatically set the Referer: field in requests where it follows a Location: redirect.
        curl_setopt($curlArray[$i], CURLOPT_FOLLOWLOCATION, true); //跟踪url的跳转，比如301, 302等
        curl_setopt($curlArray[$i], CURLOPT_MAXREDIRS, 2); //跟踪最大的跳转次数
        curl_setopt($curlArray[$i], CURLOPT_HEADER, 0); //TRUE to include the header in the output.
        curl_setopt($curlArray[$i], CURLOPT_ENCODING, ""); //接受的编码类型,The contents of the "Accept-Encoding: " header. This enables decoding of the response. Supported encodings are "identity", "deflate", and "gzip". If an empty string, "", is set, a header containing all supported encoding types is sent.
        curl_setopt($curlArray[$i], CURLOPT_CONNECTTIMEOUT, 5); //连接超时时间
        curl_multi_add_handle($mh, $curlArray[$i]); //curl_multi_add_handle --  Add a normal cURL handle to a cURL multi handle
    }
    $running = NULL;
    $count = 0;
    do {
//10秒钟没退出，就超时退出
        if ($count++ > 100) break;
        usleep(100000);
        curl_multi_exec($mh, $running); //curl_multi_exec --  Run the sub-connections of the current cURL handle
    } while ($running > 0);
    $i = 0;
    $pattern = "/<span id=\"downversion\">(.*?)<\/span>/im";
    $content = array();
    foreach ($urlArray as $i => $url) {
//        echo $i . '____' . $url . "\r\n";
        $content = curl_multi_getcontent($curlArray[$i]); //curl_multi_getcontent --  Return the content of a cURL handle if CURLOPT_RETURNTRANSFER is set
        preg_match($pattern, $content, $match);
        if (isset($match[1])) {
            $return[$i] = $match[1];
        } else {
            $return[$i] = '';
        }
        $i++;
    }
//curl_multi_remove_handle --  Remove a multi handle from a set of cURL handles
    foreach ($urlArray as $i => $url) {
        curl_multi_remove_handle($mh, $curlArray[$i]);
    }
//curl_multi_close --  Close a set of cURL handles
    curl_multi_close($mh);
    return $return;
}





