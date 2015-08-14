<?php
/*
 * @Author AChan
 * Date 20150310
 * explain 抓取数据测试文件(拿同步推热榜进行多进程并发处理抓取数据,测试效率性,数据完整性,安全性等) 
 */

require_once('../public/Signfork.php');
require_once('../public/simple_html_dom.php');
require_once("../public/curlUnit.php");
include_once('../config/config.class.php');
$beginTime =time();

$html = file_get_html(DEFAULT_HOTS_LIST);
/************************ free data *****************************************/
//$freeData = array();
$listName = array();
$data = array();
$client = new GearmanClient();
$client->addServer();


//获取分类列表
foreach($html->find('li.all') as $subCategory)
{
    foreach($subCategory->parent()->find('a') as $a)
    {
	$name = $a->plaintext;
	$href = $a->href;
	$listName[$name] = $href;
	   
	
    }
}

$i=0;
foreach($listName as $key=>$value)
{
    $values = DEFAULT_DOMAIN.$value;
    $htmls = file_get_html($values);
    foreach($htmls->find('li.time') as $Category){
	foreach($Category->parent()->find('a') as $v){
	    $names = $v->plaintext;
	    $urls = $v->href;
	    $arr[$key][$names] = DEFAULT_DOMAIN.$urls;
	    $i++;
	}
    }
}


$result = array();
$category_url = array("/iphone-paihang-tb-hot-0-all/","/iphone-paihang-tb-hot-0-week/");
foreach($arr as $key=>$rows)
{
    foreach($rows as $k=>$v)
    {
	$i = 0;
	$html = file_get_html("{$v}");
	//免费榜获取图片地址,应用名称
	foreach ($html->find('div [class=top-list free] ul li a img') as $title) 
	{
	    $appName = $title->alt;
	    $icon = $title->lz_src;
	    $result[$key][$k]['free'][$i]['name'] = $appName;
	    $result[$key][$k]['free'][$i]['icon'] = $icon;
	    $result[$key][$k]['free'][$i]['isfree'] = 'Free';
	    $result[$key][$k]['free'][$i]['firstCategory'] = $key;
	    $result[$key][$k]['free'][$i]['category'] = "热榜";
	    $result[$key][$k]['free'][$i]['SecondCategory'] = $k;
	    $result[$key][$k]['free'][$i]['Source'] = DEFAULT_DOMAIN;
	    $i++;
	}
	
	$i=0;
	//收费榜获取图片地址,应用名称
	foreach ($html->find('div [class=top-list nofree] ul li a img') as $title) 
	{
	    $appName = $title->alt;
	    $icon = $title->lz_src;
	    $result[$key][$k]['nofree'][$i]['name'] = $appName;
	    $result[$key][$k]['nofree'][$i]['icon'] = $icon;
	    $result[$key][$k]['nofree'][$i]['isfree'] = 'noFree';
	    $result[$key][$k]['nofree'][$i]['firstCategory'] = $key;
	    $result[$key][$k]['nofree'][$i]['SecondCategory'] = $k;
	    $result[$key][$k]['nofree'][$i]['category'] = "热榜";
	    $result[$key][$k]['nofree'][$i]['Source'] = DEFAULT_DOMAIN;
	    $i++;
	}
	
	$i = 0;
	//获取免费榜二级域名
	foreach ($html->find('div [class=top-list free] a[class=icon]') as $apphref) 
	{
	    $appInfoUrl = DEFAULT_DOMAIN . $apphref->href;    
	    $freeData[$i] = $appInfoUrl;
	    $i++;
	}
	
	//获取收费榜二级域名
	foreach ($html->find('div [class=top-list free] a[class=icon]') as $apphref) 
	{
	    $appInfoUrl = DEFAULT_DOMAIN . $apphref->href;    
	    $noFreeData[$i] = $appInfoUrl;
	    $i++;
	}
	
	//抓取二级域名免费版本号调用多进程处理
	$client->setCompleteCallback(function(GearmanTask $task) use (&$data) 
	{
	    $pattern = "/<span id=\"downversion\">(.*?)<\/span>/im";

	    //<p>更新时间：2015/2/21</p>
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
	foreach($freeData as $url) 
	{
	    $client->addTask('getVersion', $url,null,$i);
	    $i++;
	}
	
	$client->runTasks();
	
	$results[$k]['free'] = addResult($result[$key][$k]['free'],$data,$freeData);
	//抓取二级域名收费版本号调用多进程处理
	$client->setCompleteCallback(function(GearmanTask $task) use (&$row) 
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
	    $row[$task->unique()] = $version;
	});

	$i=0;
	foreach($noFreeData as $url) 
	{
	    $client->addTask('getVersion', $url,null,$i);
	    $i++;
	}
	
	$client->runTasks();
	
	$results[$k]['nofree'] = addResult($result[$key][$k]['nofree'],$row,$noFreeData);
	
	if($key == "月榜"){
	    var_dump($results);
	    echo time()-$beginTime;
	    exit(0);
	}
	
	
    }
}

//var_dump($results);
//echo time()-$beginTime;
//die;
function addResult($result,$data,$freeData)
{
    ksort($data);
    $i=0;
    foreach($data as $ks=>$vs)
    {
       $result[$i]['version'] = $vs;
       $i++;
    }
    $i=0;
    foreach($freeData as $ky => $va)
    {
	$result[$i]['url'] = $va;
	$i++;
    }
    return $result;
    
}


