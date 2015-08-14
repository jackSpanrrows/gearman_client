<?php
require './../QueryList.class.php';
$url = 'http://ios.25pp.com';
$client = new GearmanClient();
$client->addServer();


/***********首页数据***************************/
$columns = array('gamelist','applist','hot_game','new_game','recommend_game','hot_app','new_app','recommend_app');
$reg = array(
    'gamelist'=>array(".gameRank:eq(0) li .nameDown a","href"),
    'applist'=>array(".gameRank:eq(1) li .nameDown a","href"),
    'hot_game'=>array(".gcp1:eq(0) dt a",'href'),
    'new_game'=>array(".gcp1:eq(1) dt a",'href'),
    'recommend_game'=>array(".gcp1:eq(2) dt a",'href'),
    'hot_app'=>array(".gcp2:eq(0) dt a",'href'),
    'new_app'=>array(".gcp2:eq(1) dt a",'href'),
    'recommend_app'=>array(".gcp2:eq(2) dt a",'href'),
);
$t1 = microtime(true);
$sj = QueryList::Query($url,$reg);
$Arr = $sj->jsonArr;
//var_dump($Arr);die;
$data = array();
$client->setCompleteCallback(function (GearmanTask $task) use (&$data) {
    //echo 1;
    $data[] = json_decode($task->data(), true);
});

foreach($Arr as $val){
    foreach($val as $k=>$appUrl) {
//        echo $appUrl."\r\n";
        $client->addTask('getAppInfo', $appUrl.'##'.$k);
        //$appList[] = array('appname' => $data[0]['appname'], 'appversion' => $data[0]['appversion'], 'icon' => $data[0]['icon'], 'source' => $k, 'href' => $appUrl,);
    }
}

//
$client->runTasks();
$t2 = microtime(true);
echo 'spent :'.round($t2-$t1,3).'s';
var_dump($data);die;


/********************************ios 软件 （全部）************************************************/

//获取 最新，热门，推荐 链接
$total = 100;
$url = 'http://ios.25pp.com/software/';
$reg = array('name' => array('.sort_ul li a', 'html'), 'href' => array('.sort_ul li a ', 'href'));
$sj = QueryList::Query($url, $reg);
$columnArr = $sj->jsonArr;
//当前页为第一个column,删除第一个column
$thisPageColumn = array_shift($columnArr);

//当前页数据获取
getCurrentPageAppList($sj,$thisPageColumn['name'],$thisColumnApp);

function getCurrentPageAppList($sj, $name,&$thisColumnApp)
{
    $sj->setQuery(array('appinfo' => array('.apps .app_title  h4 .h4_a', 'href')));
    $tmp = i_array_column($sj->jsonArr, 'appinfo');
    $thisColumnApp[$name] = $tmp;
    //当前页应用统计
    $onePageApp = count($tmp);
    //总共还需获取页数
    $totalPage = ceil(100 / $onePageApp) - 1;
    //获取当前页地址
    $sj->setQuery(array('current_page' => array('.pagearea a', 'href')));
    $pageList = $sj->jsonArr;
    $needGetPage = array_slice($pageList, 2, $totalPage);
    foreach ($needGetPage as $val) {
        $url = $val['current_page'];
        $sj = QueryList::Query($url, array('appinfo' => array('.apps .app_title  h4 .h4_a', 'href')));
        $tmp = i_array_column($sj->jsonArr, 'appinfo');
        $thisColumnApp[$name] = array_merge($thisColumnApp[$name], $tmp);
    }
}

foreach ($columnArr as $column) {
    $sj = QueryList::Query($column['href'], $reg);
    getCurrentPageAppList($sj,$column['name'],$thisColumnApp);
}
var_dump($thisColumnApp);die;
foreach($thisColumnApp as $key=>$val){
    foreach($val as $v){
        $client->addTask('getAppInfo', $v.'##'.$key);
    }
}
$client->runTasks();
var_dump($data);die;

function i_array_column($input, $columnKey, $indexKey = null)
{
    if (!function_exists('array_column')) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
        $result = array();
        foreach ((array)$input as $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : null;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    } else {
        return array_column($input, $columnKey, $indexKey);
    }
}
