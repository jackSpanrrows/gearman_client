<?php
/**
 * Created by PhpStorm.
 * User: solo
 * Date: 2015/3/9
 * Time: 14:22
 */

class curlUnit
{   
    public $signfork;
    
    public $pattern = "/<span id=\"downversion\">(.*?)<\/span>/im";
    
    public $url;
    
    function __fork($arg)
    {
        return self::GetHtmlCode($arg);
    }
    
//    public function init($signfork,$pattern,$url)
//    {
//	$this->signfork = new Signfork();
//	$this->pattern = $pattern;
//	$this->url = $url;
//    }
    
    //抓取页面内容
    function GetHtmlCode($url)
    {
        $ch = curl_init();//初始化一个cur对象
        curl_setopt($ch, CURLOPT_URL, $url);//设置需要抓取的网页
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //设置为true表示返回抓取的内容，而不是直接输出到浏览器上
        curl_setopt($ch, CURLOPT_AUTOREFERER, true); //自动设置
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //跟踪url的跳转，比如301, 302等
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2); //跟踪最大的跳转次数
        curl_setopt($ch, CURLOPT_HEADER, 0); //TRUE to include the header in the output.
        curl_setopt($ch, CURLOPT_ENCODING, ""); //接受的编码类型
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);//设置链接延迟
	
        $HtmlCode = curl_exec($ch);//运行curl，请求网页
        preg_match($this->pattern, $HtmlCode, $match);
        if (isset($match[1])) {
           return  $match[1];
        } else {
           return '';
        }
    }
    
//    public function __set($pattern,$value) 
//    {
//	$this->$pattern = $value;
//    }
    
    //去重复
    public function Delunique($result)
    {
	$arr = array();
	if(is_array($result)){
	    foreach($result as $k=>$v)
	    {
		
		if(is_array($v))
		{
		    foreach($v as $value)
		    {
			if(in_array($value,$arr,true))
			{
			    unset($value);
			}else{
			    $arr[]=$value;
			    
			}
		    }
		}
	    }
	    return $arr;
	}
    }
}


