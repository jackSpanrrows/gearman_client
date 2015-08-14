#!/bin/bash
#本脚本是自动杀掉开启的gearmand后台进程
ps -aux|grep gearman_|awk '{print $2}'|xargs kill -s 9

if [ $? -eq 0 ];then
	echo "gearman后台脚本进程已杀死"
fi
