<?php
//include_once("");
//91热门游戏抓取规则
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