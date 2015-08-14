<?php
/**
 * @author   Achan
 * Date      2015.03.19 上午10点
 * describe  91助手数据模拟抓取
 */

$dir = dirname(__FILE__).'/';
include_once($dir.'../config/config.class.php');

$client = new GearmanClient();

$client->addServer();

$beginTime = time();

/************************************91专区首页Begin****************************************************/
$categoryArr = array("91热门游戏推荐","91热门软件推荐","91游戏排行榜","91软件排行榜","91网游排行榜");

foreach($categoryArr as $k => $category)
{
	getPlayRankings($db,$category,$k,$log_filename);
}
echo time()-$beginTime;
//专区首页需25S
function getPlayRankings($db,$category,$k,$log_filename){
	switch ($k) {
		case '0':
			$reg = array('appName' => array('#tabMain2 ul li h4 a','text'), 'app_url' => array('#tabMain2 ul li h4 a', 'href') , 'small_icon_url' => array('#tabMain2 ul li img' , 'src') );
			break;
		case '1':
			$reg = array('appName' => array('#tabMain3 ul h4 a','text'), 'app_url' => array('#tabMain3 ul li h4 a', 'href') , 'small_icon_url' => array('#tabMain3 ul li img' , 'src') );
			break;
		case '2':
			$reg = array('appName' => array('.game-rank ol li  a img','title'), 'app_url' => array('.game-rank .rank-s-name-in', 'href') , 'small_icon_url' => array('.game-rank ol li a img' , 'src') );
			break;
		case '3':
			$reg = array('appName' => array('.soft-rank .rank-s-name-in','text'), 'app_url' => array('.soft-rank .rank-s-name-in', 'href') , 'small_icon_url' => array('.soft-rank ol li img' , 'src') );
			break;
		default:
			$reg = array('appName' => array('.hot-rank ol li a img','title'), 'app_url' => array('.hot-rank .rank-s-name-in', 'href') , 'small_icon_url' => array('.hot-rank ol li img' , 'src') );
			break;
	}

	$sj = QueryList::Query(PLAY_91, $reg);

	$columnArr = $sj->jsonArr;

	$i = 0;

	foreach($columnArr as $key=>$value)
	{
		$reg = array('version' => array('.ssoft-info','text'));

		$url = $value['app_url'];

		$sj = QueryList::Query($url, $reg);

		$columnArrs = $sj->jsonArr;

		$versions = $columnArrs[0];

		$appName = $columnArr[$key]['appName'];
		
		$small_icon_url = $columnArr[$key]['small_icon_url'];

		$version = getVersion($versions,$log_filename);

		$columnArr[$key]['Version'] = $version;

		$columnArr[$key]['Category'] = $category;

		$columnArr[$key]['Source'] = "http://play.91.com/";

	}

}


//获取应用版本号
function getVersion($versions,$log_filename)
{
	if(is_array($versions))
	{

		$version = explode('|',$versions['version']);

		$version = explode('：',$version[0]);

		$version = $version[1];

		return $version;
	}else{

		$date = date("Y-m-d H:i:s" , time());
		$error_message = "参数错误";
		die(error_log($date . "\t" . $error_message . "\r\n" , 3 , $log_filename));
	}

}


/************************************91专区首页End****************************************************/
/************************************热门游戏Begin****************************************************/




/************************************热门游戏End****************************************************/




