<?php
/**
 * Created by PhpStorm.
 * User: solo
 * Date: 2015/3/9
 * Time: 14:22
 */

/**
 * Project: Signfork: php多线程库
 * File:    Signfork.class.php
 */
ini_set('default_socket_timeout', -1);
class Signfork{
    /**
     * 设置子进程通信文件所在目录
     * @var string
     */
    private $tmp_path='/tmp/';


    /**
     * Signfork引擎主启动方法
     * 1、判断$arg类型,类型为数组时将值传递给每个子进程;类型为数值型时,代表要创建的进程数.
     * @param object $obj 执行对象
     * @param string|array $arg 用于对象中的__fork方法所执行的参数
     * 如:$arg,自动分解为:$obj->__fork($arg[0])、$obj->__fork($arg[1])...
     * @return array  返回   array(子进程序列=>子进程执行结果);
     */
    public function run($obj,$arg=1){
        if(!method_exists($obj,'__fork')){
            exit("Method '__fork' not found!");
        }
        $redis = new redis();
        $redis->connect('192.168.110.41');
        if(is_array($arg)){
            $i=0;
            foreach($arg as $key=>$val){
                $spawns[$i]=$key;
                $i++;
                $this->spawn($obj,$key,$val,$redis);
            }
            $spawns['total']=$i;
	    if($i>=199) exit(0);
        }elseif($spawns=intval($arg)){
            for($i = 0; $i < $spawns; $i++){
		//if($i>=100) sleep(3000);
                $this->spawn($obj,$i,$redis);
		
            }
        }else{
            exit('Bad argument!');
        }

//        if($i>1000) exit('Too many spawns!');
        return $this->request($spawns,$redis);
    }

    /**
     * Signfork主进程控制方法
     * 1、$tmpfile 判断子进程文件是否存在，存在则子进程执行完毕，并读取内容
     * 2、$data收集子进程运行结果及数据，并用于最终返回
     * 3、删除子进程文件
     * 4、轮询一次0.03秒，直到所有子进程执行完毕，清理子进程资源
     * @param  string|array $arg 用于对应每个子进程的ID
     * @return array  返回   array([子进程序列]=>[子进程执行结果]);
     */
    private function request($spawns,$redis)
    {
        $data=array();
        $i=is_array($spawns)?$spawns['total']:$spawns;
        for($ids = 0; $ids<$i; $ids++){
            while(!($cid=pcntl_waitpid(-1, $status, WNOHANG))) break;
            $tmpfile=$this->tmp_path.'sfpid_'.$cid;
            $data[$spawns['total']?$spawns[$ids]:$ids]=file_get_contents($tmpfile);
            unlink($tmpfile);
            $data[$spawns['total']?$spawns[$ids]:$ids] =  $redis->get($cid);
        }
        return $data;
    }
    
    /**
     * Signfork子进程执行方法
     * 1、pcntl_fork 生成子进程
     * 2、file_put_contents 将'$obj->__fork($val)'的执行结果存入特定序列命名的文本
     * 3、posix_kill杀死当前进程
     * @param object $obj        待执行的对象
     * @param object $i                子进程的序列ID，以便于返回对应每个子进程数据
     * @param object $param 用于输入对象$obj方法'__fork'执行参数
     */
    private function spawn($obj,$i,$param=null,$redis){
        
	if(pcntl_fork()===0){
            $cid=getmypid();
	    var_dump(array($cid=>$obj->__fork($param)));
            file_put_contents($this->tmp_path.'sfpid_'.$cid,$obj->__fork($param));
            $redis->set($cid,$obj->__fork($param));
            //$redis->ZADD('version',$cid,$obj->__fork($param));
            posix_kill($cid, SIGTERM);
            exit;
	}
    }
}


