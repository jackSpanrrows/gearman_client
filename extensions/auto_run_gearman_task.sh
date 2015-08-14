#!/bin/bash
#自动启动Gearman的后台进程rman_tongbutui_get_hotest_version.php
#a=nohup php gearman_tongbutui_get_hotest_version.php &
#for((i=0;i<5;i++));do
#	echo $a
#done
i=1
while [ $i -le 5 ]
do
	nohup php gearman_tongbutui_get_hotest_version.php &
	nohup php gearman_tongbutui_get_index_list.php &
	nohup php gearman_tongbutui_ranking.php &
	nohup php gearman_pp_index_list.php &
	nohup php gearman_kwh_index_list.php &
	nohup php gearman_pp_all_list.php &
#	echo $i
	i=$(( $i+1 ))
done

if [ $? -eq 0 ];then
	echo "脚本执行成功"
fi

exit 0
