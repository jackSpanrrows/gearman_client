<?php
/**
 * @Description 抓取调研配置文件，常量，数据库配置连接
 */
$dir = dirname(__FILE__).'/';
include_once($dir.'../public/simple_html_dom.php');
include_once($dir.'../extensions/QueryList.class.php');
//include_once('../public/function.php');
require_once($dir.'../public/Signfork.php');
require_once($dir.'../public/curlUnit.php');
define('DEFAULT_DOMAIN' , "http://app.tongbu.com");//同步推官网
define('DEFAULT_HOTS_LIST' , "http://app.tongbu.com/iphone-paihang-tb-hot-0-all/");
define('HOST' , "192.168.110.218");
define('USERNAME' , "root");
define('PASSWORD' , "123456");
define('IPHONE_PAIHANG_ITUNES_CN' , "http://app.tongbu.com/iphone-paihang-itunes-cn-0-free");
define('IPHONE_TONGBU_HOT_ALL',"http://app.tongbu.com/iphone-paihang-tb-hot-0-all/");
define('PLAY_91' , "http://play.91.com");//91官网
define('DB',"test");
define('TABLE',"app_get_base_infor");
define('CHIOCENESS' , "http://play.91.com/iphone/Game/");//91精选
define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/../'))."/");
define('TOTAL',200);
define('TONGBUTUI_SOURCEID',3);
define('PP_SOURCEID',2);
define("IPHONE_25PP","http://ios.25pp.com/");//25PP首页
define("PP_SOFTWARE","http://ios.25pp.com/software/");//25PP软件库
define("PP_GAME","http://ios.25pp.com/game/");//25PP游戏库
define("APP_KUWANHUI","http://play.91.com/");//91酷玩汇首页
define("WEB_NAME_1","同步推");
define("WEB_NAME_2","PP");
define("WEB_NAME_3","91助手");
$date = date('Y-m-d H:i:s',time());

$log_filename = BASE_PATH."logs/".date("Y-m-d")."_mysql_error.log";

$db_infor = array("HOST" => "192.168.110.218" , "USERNAME" => "test" , "PASSWORD" => "123456");

try{
        $dsn = "mysql:host={$db_infor['HOST']};dbname=".DB;

        $db = new PDO($dsn , $db_infor["USERNAME"] , $db_infor["PASSWORD"]);

        $db->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION);

        $db->exec("set names 'UTF8'");

    } catch (PDOException $err){

            die(error_log($date . "\t" . $err->getMessage() . "\r\n" , 3 , $log_filename));
    }
    
/****************************************数据库处理方法 *****************************************/
/**
 * 插入数据库方法
 * @param type $db          数据库名
 * @param type $sql         SQL语句
 * @param type $log_filename    日志文件
 */
function insertDb($db,$sql,$log_filename,$webName,$k=null)
{
    $date = date("Y-m-d H:i:s" , time());
    try{
            $db->beginTransaction();
            $sql = substr($sql,0,-1);
            $num = $db->exec($sql);//执行SQL语句返回总条数
            $db->commit();
            
            if($num>0){
                $message = "成功插入{$webName}{$k}{$num}条数据!";
                error_log($date . "\t" . $message . "\r\n" , 3 , $log_filename);
            }
        } catch (PDOException $err) {
            $db->rollback();
            die(error_log($date . "\t" . $err->getMessage() . "\r\n" , 3 , $log_filename));
        }
}



/**
 * 此方法通过网页数字ID和PP助手客户端接口获取真实APPID
 * @param type $id   数字ID，为6到9位不等,site 为1 为越狱，3为正版软件
 * @return type      返回字符串COMID
 */
//function GetHtmlCode($id,$site=3)
function GetComAppid($id,$site=3)
{
    $url = "http://pppc2.25pp.com/pp_api/ios.php";
    $data = array('site' => $site, 'id' => $id);
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
    if($buid['code']!=400 && isset($buid['buid']))
        return $buid['buid'];
    else
        return NULL;
}

/**
 * 所查字符串需要对特殊字符如'-','（','('进行判断截取分割
 * @param type $string  需要处理的字符串
 * @return type   返回200代表有数据，400代表无数据返回
 */
function str_explode($string)
{
    $spechars = array('-','(',':','（');
    foreach($spechars as $val)
    {
        $str = strpos($string,$val);
        if($str != FALSE)
        {
            $strs = explode($val,$string);
            break;
        }
    }
    if(!empty($strs))
        return array('code' => '200','data' => $strs);
    else 
        return array('code'=>'400','data' => array('appname' => $string));
}

/**
 * 通过关键词搜索相关应用APPID
 * @param string $durl    所需抓取页面
 * @param type $keyword   接口对应关键词
 * @return type  array    返回200代表有数据，400代表无数据返回
 */
function curl_file_get_appid($keyword)
{
    $ch = curl_init();
    $durl = 'http://suggestion.sj.91.com/service.ashx?act=1001&keyword='.$keyword;
    ob_start();
    curl_setopt($ch, CURLOPT_URL, $durl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //设置为true表示返回抓取的内容，而不是直接输出到浏览器上
    curl_setopt($ch, CURLOPT_AUTOREFERER, true); //自动设置referer
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //跟踪url的跳转，比如301, 302等
    curl_setopt($ch, CURLOPT_MAXREDIRS, 2); //跟踪最大的跳转次数
    curl_setopt($ch, CURLOPT_HEADER, 0); 
    curl_setopt($ch, CURLOPT_ENCODING, ""); //接受的编码类型
    $returnId = curl_exec($ch);
    curl_close($ch);
    $buid = json_decode($returnId,true);
    if($buid['cnt'] == 1)
        return array('identifier' => $buid['result']['apps'][0]['identifier'],'name' => $buid['result']['apps'][0]['name']);
    else
        return array('code' => '400');
}

function getTrueAppid($appname)
{
        $data = str_explode($appname);
        if($data['code'] && $data['code'] != 400) 
        {
            $strs = $data['data'][0];
            $contents = curl_file_get_appid( $strs ) ;
        }else{
            $contents = curl_file_get_appid($appname);
        }
        if (isset($contents['name']) && $contents['name'] == $appname)
        {
            $appid = $contents['identifier']; 
            return $appid;
        }
        return NULL;
}


/**
 * 此方法通过网页数字ID和PP助手客户端接口获取真实APPID
 * @param type $id   数字ID，为6到9位不等,site 为1 为越狱，3为正版软件
 * @return type      返回字符串COMID
 */
//function GetHtmlCode($id,$site=3)
//{
//    $url = "http://pppc2.25pp.com/pp_api/ios.php";
//    $data = array('site' => $site, 'id' => $id);
//    $ch = curl_init();//初始化一个cur对象
//    ob_start();
//    curl_setopt ($ch, CURLOPT_URL, $url);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //设置为true表示返回抓取的内容，而不是直接输出到浏览器上
//    curl_setopt($ch, CURLOPT_AUTOREFERER, true); //自动设置referer
//    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //跟踪url的跳转，比如301, 302等
//    curl_setopt($ch, CURLOPT_MAXREDIRS, 2); //跟踪最大的跳转次数
//    curl_setopt($ch, CURLOPT_HEADER, 0); 
//    curl_setopt($ch, CURLOPT_ENCODING, ""); //接受的编码类型
//    $returnId = curl_exec($ch);
//    curl_close($ch);
//    $buid = json_decode($returnId,true);
//    if($buid['code']!=400)
//        return $buid['buid'];
//    else
//        return NULL;
//}