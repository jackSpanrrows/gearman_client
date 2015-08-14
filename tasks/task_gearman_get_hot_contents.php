<?php
/*
 * @Author 	AChan
 * Date 	20150310
 * describe 抓取数据测试文件(拿同步推热榜进行多进程并发处理抓取数据,测试效率性,数据完整性,安全性等) 
 */


include_once('../config/config.class.php');
$beginTime =time();

$html = file_get_html(DEFAULT_HOTS_LIST);
/************************ free data *****************************************/
//$freeData = array();
$listName = array();
$data = array();
$client = new GearmanClient();
$client->addServer();

$i = 0;
//获取分类列表
foreach($html->find('li.all') as $subCategory)
{
    foreach($subCategory->parent()->find('a') as $a)
    {
	$name = $a->plaintext;
	$href = $a->href;
	$listName[$name][$i] = $href;
	$i++;
    }
}
$i = 0 ;
$j = 0 ;
foreach($listName as $k => $vs)
{
    if($vs = DEFAULT_HOTS_LIST)
    {
	foreach($html->find('li.time') as $Category)
	{
	    foreach($Category->parent()->find('a') as $v)
	    {
		$names = $v->plaintext;
		$urls  = $v->href;
		$freeData = array();
		$noFreeData = array();
		$data = array();
		$row = array();
		if($names == "全部"){
		    //免费榜获取图片地址, 
		    $isFree = "Free";
		    $result[$k][$names][$isFree] = addIsFree($html,$k,$names,$isFree);
		    //print_r($result);die;
		    //获取应用URL地址
		    $freeData = getUrl($isFree,$html);
		    $data = addFreeUrl($client,$html,$isFree,$freeData);
		    $result[$k][$names][$isFree] = addResult($result,$data,$freeData,$isFree,$k,$names);
		    //获取收费榜数据
		    $isFree = "noFree";
		    //获取收费应用URL地址
		    $noFreeData = getUrl($isFree,$html);
		    $result[$k][$names][$isFree] = addNoFree($html,$k,$names,$isFree,$noFreeData);
		    $row = addFreeUrl($client,$html,$isFree,$noFreeData);
		    $result[$k][$names][$isFree] = addResult($result,$row,$noFreeData,$isFree,$k,$names);
		}else{
/******************************************************************************/
		    $htmls = file_get_html(DEFAULT_DOMAIN.$urls);
		    $isFree = "Free";
		    $result[$k][$names][$isFree] = addIsFree($htmls,$k,$names,$isFree);
		     //获取应用URL地址
		    $freeData = getUrl($isFree,$htmls);
		    $data = addFreeUrl($client,$htmls,$isFree,$freeData);
		    $result[$k][$names][$isFree] = addResult($result,$data,$freeData,$isFree,$k,$names);
		    //获取周榜,月榜收费应用数据
		    $isFree = "noFree";
		    $result[$k][$names][$isFree] = addNoFree($htmls,$k,$names,$isFree);
		    //获取应用URL地址
		    $noFreeData = getUrl($isFree,$htmls);
		    $row = addFreeUrl($client,$htmls,$isFree,$noFreeData);
		    $result[$k][$names][$isFree] = addResult($result,$row,$noFreeData,$isFree,$k,$names);
		    //print_r($result);echo time()-$beginTime;die;
//************************************************************************************************
		}
	    }
	    //var_dump($result);
	    echo time()-$beginTime;die;
	}
    }else{
	return false;
    }
}
die;

//添加版本和URL地址
function addResult($result,$data,$freeData,$isFree,$k,$name)
{   
    ksort($data);
    $num = count($data);
    for($i=0;$i<$num;$i++){
	$result[$k][$name][$isFree][$i]['version'] = $data[$i];	
	$result[$k][$name][$isFree][$i]['url'] = $freeData[$i];
    }
    return $result[$k][$name][$isFree];
}


function addIsFree($html,$k,$names,$isFree)
{
    $i = 0;
    //免费榜获取图片地址, 
    foreach ($html->find('div [class=top-list free] ul li a img') as $title) 
    {
	$appName = $title->alt;
	$icon = $title->lz_src;
	$result[$k][$names][$isFree][$i]['Name'] = $appName;
	$result[$k][$names][$isFree][$i]['Icon'] = $icon;
	$result[$k][$names][$isFree][$i]['IsFree'] = 'Free';
	$result[$k][$names][$isFree][$i]['FirstCategory'] = $k;
	$result[$k][$names][$isFree][$i]['SecondCategory'] = $names;
	$result[$k][$names][$isFree][$i]['Category'] = "热榜";
	$result[$k][$names][$isFree][$i]['Source'] = DEFAULT_DOMAIN;
	$i++;
    }
    return $result[$k][$names][$isFree];
}


function addNoFree($html,$k,$names,$isFree)
{
    $i = 0;
    //收费榜获取图片地址, 
    foreach ($html->find('div [class=top-list nofree] ul li a img') as $title) 
    {
	$appName = $title->alt;
	$icon = $title->lz_src;
	$result[$k][$names][$isFree][$i]['Name'] = $appName;
	$result[$k][$names][$isFree][$i]['Icon'] = $icon;
	$result[$k][$names][$isFree][$i]['IsFree'] = 'noFree';
	$result[$k][$names][$isFree][$i]['FirstCategory'] = $k;
	$result[$k][$names][$isFree][$i]['SecondCategory'] = $names;
	$result[$k][$names][$isFree][$i]['Category'] = "热榜";
	$result[$k][$names][$isFree][$i]['Source'] = DEFAULT_DOMAIN;
	$i++;
    }
    return $result[$k][$names][$isFree];
}

//获取应用URL地址
function getUrl($isFree,$html){
    $i = 0;
    if($isFree == "Free")
    {
	//获取免费榜二级域名
	foreach ($html->find('div [class=top-list free] a[class=icon]') as $apphref) 
	{
	    $appInfoUrl = DEFAULT_DOMAIN . $apphref->href;    
	    $freeData[$i] = $appInfoUrl;
	    $i++;
	}
    }else{
	//获取收费榜二级域名
	foreach ($html->find('div [class=top-list nofree] a[class=icon]') as $apphref) 
	{
	    $appInfoUrl = DEFAULT_DOMAIN . $apphref->href;    
	    $freeData[$i] = $appInfoUrl;
	    $i++;
	}
    }
    return $freeData;
}
//获取版本号
function addFreeUrl($client,$html,$isFree,$freeData)
{
    
    //调用多进程处理获取应用版本号
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
    foreach($freeData as $url) 
    {
	$client->addTask('getVersion', $url,null,$i);
	$i++;
    }

    $client->runTasks();
    
    return $data;
}
/**
 * ****************************************************************************************************************
 */






