<?php
/**
 * User: Achan
 * Date: 2015/3/11
 * Time: 14:00
 */

$dir = dirname(__FILE__).'/';
require_once($dir.'../public/Signfork.php');
require_once($dir.'../public/simple_html_dom.php');
require_once($dir.'../public/curlUnit.php');
include_once($dir.'../config/config.class.php');

//$domain = 'http://app.tongbu.com';
//$url = "http://app.tongbu.com/iphone-paihang-tb-hot-0-all/";
//$url = "http://app.tongbu.com/iphone-paihang-tb-hot-6000-all/";

$beginTime =time();
// get DOM from URL or file
//$html = file_get_html(DEFAULT_HOTS_LIST);
$html = file_get_html("http://app.tongbu.com/iphone-paihang-tb-hot-6000-all/");
/************************ free data *****************************************/
$freeData = array();
$listName = array();
//获取分类列表
$i = 0;
foreach ($html->find('div [class=topmenu] li a') as $sorting)
{
    $sortName = $sorting->plaintext;

    $listName[$sortName][$i] = $sorting->href;

    $i++;
}
print_r($listName);
die;
//抓取小分类
$i = 0;
foreach($html->find('li.time') as $subCategory){
    foreach($subCategory->parent()->find('a') as $a){
	foreach ($listName as $key=>$value){
	     
	    $name = $a->plaintext;
            
	    $arr[$name] = $a->href;
            
	    $listName[$key] = $arr;
	}
	
	
    }
}

//获取名称
$i = 0;
foreach ($html->find('div [class=title] a') as $title) {
    
    $appName = $title->plaintext;
    
    if($i>=100)
    {
	$freeData['pay'][$i]['name'] = $appName;
    }else{
	$freeData['free'][$i]['name'] = $appName;
    }
    
    
    $i++;
}

$i = 0;
//获取icon
foreach ($html->find('div a[class=icon] img') as $img) {
    
    $appIcon = $img->lz_src;
    
    if($i>=100)
    {
        
	$freeData['pay'][$i]['icon'] = $appIcon;
        
    }else{
        
	$freeData['free'][$i]['icon'] = $appIcon;
    }
    
    $i++;
}

//获取二级页面URL地址
$i = 0;
foreach ($html->find('div [class=top-list free] a[class=icon]') as $apphref) 
{
    $app_version = '';
    
    $appInfoUrl = $domain . $apphref->href;   
    
    $curlUrl[$i] = $appInfoUrl;
    
    $i++;
}

$client = new GearmanClient();

$client->addServer();

$data = array();
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
foreach($curlUrl as $url) 
{
    $client->addTask('getVersion', $url,null,$i);
    $i++;
}
$client->runTasks();

ksort($data);
var_dump($data);die;
foreach($data as $k=>$v){
    if($k>=100){
	$freeData['pay'][$k]['version']=$v;
    }else{
	$freeData['free'][$k]['version']=$v;
    }
}

var_dump($freeData);
echo "Run Time :".(time() - $beginTime);




