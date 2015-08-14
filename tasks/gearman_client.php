<?php

$client = new GearmanClient();
$client->addServer();

$curlArray = array(
    '0' => 'http://app.tongbu.com/947223546_buzhengchangmaoxianxiaominggunchuqu.html',
    '1' => 'http://app.tongbu.com/816042425_biziyishengmingxingyouxi.html',
    '2' => 'http://app.tongbu.com/10009205_dawanju.html',
    '3' => 'http://app.tongbu.com/750958035_princess_makeover.html',
    '4' => 'http://app.tongbu.com/852182147_3d_bus_driving_simulator_.html',
    '5' => 'http://app.tongbu.com/824354676_xuezhirongyao:shenbing.html',
    '6' => 'http://app.tongbu.com/498958534_chaojihuochemianfei.html',
    '7' => 'http://app.tongbu.com/606076615_lollipop_yum_free.html',
    '8' => 'http://app.tongbu.com/908179524_fennudexiaoniaosidaila.html',
    '9' => 'http://app.tongbu.com/446324234_huanledoudizhu.html',
    '10' => 'http://app.tongbu.com/639516529_zhiwudazhanjiangshi2.html',
    '11' => 'http://app.tongbu.com/594415728_auqidanche.html',
    '12' => 'http://app.tongbu.com/10010128_.html',
    '13' => 'http://app.tongbu.com/778207176_auzuogongjiao.html',
    '14' => 'http://app.tongbu.com/594756107_audangqiuqian.html',
    '15' => 'http://app.tongbu.com/10010131_.html',
    '16' => 'http://app.tongbu.com/10009997_.html',
    '17' => 'http://app.tongbu.com/731868291_princess_tailor.html',
    '18' => 'http://app.tongbu.com/730307970_ASwagSurfandTwerkWaterAdventure.html',
    '19' => 'http://app.tongbu.com/765725588_gongzhuzhuangbanpintunvhaizhi_.html',
    '20' => 'http://app.tongbu.com/913292932_simcity_buildit.html',
    '21' => 'http://app.tongbu.com/592065092_chaohejinzhanjizhiluanshiyinghao.html',
    '22' => 'http://app.tongbu.com/494214323_lunch_food_maker.html',
    '23' => 'http://app.tongbu.com/445375097_aiqiyippsyingyin.html',
    '24' => 'http://app.tongbu.com/844664083_xiaoxiaojiaoyisheng_.html',
    '25' => 'http://app.tongbu.com/594414566_aujiawawa.html',
    '26' => 'http://app.tongbu.com/721033234_yingyinxianfeng.html',
    '27' => 'http://app.tongbu.com/10010112_ertongbaobeidongwunongchang.html',
    '28' => 'http://app.tongbu.com/586871187_uc_liulanqi.html',
    '29' => 'http://app.tongbu.com/897845314_drancia.html',
    '30' => 'http://app.tongbu.com/452186370_baidudituyuyindaohang.html',
    '31' => 'http://app.tongbu.com/646653869_duonaxueyingyuchaoshi.html',
    '32' => 'http://app.tongbu.com/789680891_leitingzhanji.html',
    '33' => 'http://app.tongbu.com/10009142_.html',
    '34' => 'http://app.tongbu.com/10010111_ertongbaobaowanjudian.html',
    '35' => 'http://app.tongbu.com/441216572_360shoujiweishi.html',
    '36' => 'http://app.tongbu.com/963454352_baobaonongchang.html',
    '37' => 'http://app.tongbu.com/364183992_qqkongjian.html',
    '38' => 'http://app.tongbu.com/823483783_huishuohuadebaodi.html',
    '39' => 'http://app.tongbu.com/387682726_taobao_.html',
    '40' => 'http://app.tongbu.com/689180123_huanlemajiangquanji.html',
    '41' => 'http://app.tongbu.com/654946670_trucker_parking_3d.html',
    '42' => 'http://app.tongbu.com/699419265_fankonghangdongzhichengshijujishou.html',
    '43' => 'http://app.tongbu.com/794752802_baby_learn_painting_amp_craft_make_amp_shopping_.html',
    '44' => 'http://app.tongbu.com/413627309_quanguoweizhangchaxun_.html',
    '45' => 'http://app.tongbu.com/553055479_ice_cream_yum_free.html',
    '46' => 'http://app.tongbu.com/561053256_guyinjia.html',
    '47' => 'http://app.tongbu.com/806032199_gongzhuhunlishalong_.html',
    '48' => 'http://app.tongbu.com/10008866_.html',
    '49' => 'http://app.tongbu.com/967708500_ertongbaobeizhilileyuan.html',
    '50' => 'http://app.tongbu.com/653350791_tiantiankupao.html',
    '51' => 'http://app.tongbu.com/589113075_gt_racing_2zhenshisaichetiyan.html',
    '52' => 'http://app.tongbu.com/448165862_momo.html',
    '53' => 'http://app.tongbu.com/10004297_ygame.html',
    '54' => 'http://app.tongbu.com/10006938_.html',
    '55' => 'http://app.tongbu.com/10008785_.html',
    '56' => 'http://app.tongbu.com/594756361_auzhenglifangjian.html',
    '57' => 'http://app.tongbu.com/966769411_xingzhizhu.html',
    '58' => 'http://app.tongbu.com/10000771_fennudexiaoniaolianliankan.html',
    '59' => 'http://app.tongbu.com/689948210_qicaizumachuanqi.html',
    '60' => 'http://app.tongbu.com/780078941_xiaoxiaohoulongyisheng_.html',
    '61' => 'http://app.tongbu.com/839516875_meiweide_froyo_zhizaoshang.html',
    '62' => 'http://app.tongbu.com/917670924_sougoushurufa.html',
    '63' => 'http://app.tongbu.com/914058799_anheifuchouzhe2.html',
    '64' => 'http://app.tongbu.com/963993893_yinhuojinglinganyingzhongzhong.html',
    '65' => 'http://app.tongbu.com/604688379_caitu:gongzhu!_.html',
    '66' => 'http://app.tongbu.com/866132884_biecaibaikuaier_4_.html',
    '67' => 'http://app.tongbu.com/419805549_wannianli.html',
    '68' => 'http://app.tongbu.com/502804922_zhe800.html',
    '69' => 'http://app.tongbu.com/793777537_star_girl_langmanzhiri.html',
    '70' => 'http://app.tongbu.com/10009485_.html',
    '71' => 'http://app.tongbu.com/494481220_saichejulebugaizhuangfengbao.html',
    '72' => 'http://app.tongbu.com/735164370_pop_star_xingxiaoxiao.html',
    '73' => 'http://app.tongbu.com/10004300_shenguihuanxiang.html',
    '74' => 'http://app.tongbu.com/790766978_xingzhixiaochupop_star.html',
    '75' => 'http://app.tongbu.com/10008743_.html',
    '76' => 'http://app.tongbu.com/370139302_qq_liulanqi_shangwangzuikuaixiaoshuoshipinxinwenyiwangdajindeshoujiliulanqi.html',
    '77' => 'http://app.tongbu.com/10004383_tiantiankupaomiji.html',
    '78' => 'http://app.tongbu.com/860350028_xiaoxiaoshetouyisheng_.html',
    '79' => 'http://app.tongbu.com/491075156_lego_juniors_create__cruise.html',
    '80' => 'http://app.tongbu.com/471802217_meirenxiangji.html',
    '81' => 'http://app.tongbu.com/874866593_overkill_3.html',
    '82' => 'http://app.tongbu.com/509885060_changba_.html',
    '83' => 'http://app.tongbu.com/567565750_ice_popsicles_free.html',
    '84' => 'http://app.tongbu.com/597795429_senlinzhongyundonghui.html',
    '85' => 'http://app.tongbu.com/685282601_shejianjingdianban.html',
    '86' => 'http://app.tongbu.com/823500957_mingxingshoushushi.html',
    '87' => 'http://app.tongbu.com/913801809_ailunzhanji.html',
    '88' => 'http://app.tongbu.com/655926338_duonaxueyingyucanting.html',
    '89' => 'http://app.tongbu.com/660724371_sprinkle_islands_free(chaojijiuhuodui_2_mianfei).html',
    '90' => 'http://app.tongbu.com/694972182_diaoyufashaoyou.html',
    '91' => 'http://app.tongbu.com/10006742_kaixinxiaoxiaolemianfeiban.html',
    '92' => 'http://app.tongbu.com/953406861_henri.html',
    '93' => 'http://app.tongbu.com/502046597_barbie_fashionistas.html',
    '94' => 'http://app.tongbu.com/475966832_tongchenglvyou.html',
    '95' => 'http://app.tongbu.com/510040874_banana_kong.html',
    '96' => 'http://app.tongbu.com/605150550_wodeyingxiong!.html',
    '97' => 'http://app.tongbu.com/843073621_haizimendeshouyisheng.html',
    '98' => 'http://app.tongbu.com/858395890_ice_road_trucker_parking_simulator_games.html',
    '99' => 'http://app.tongbu.com/839263141_duomingzhuluoji.html'
);

$data = array();
$client->setCompleteCallback(function(GearmanTask $task) use (&$data) {
    $pattern = "/<span id=\"downversion\">(.*?)<\/span>/im";
    preg_match($pattern, $task->data(), $match);
    if (isset($match[1])) {
        $version =  $match[1];
    } else {
        $version = '';
    }
    $data[$task->unique()] = $version;
//    echo ' version:', $version, PHP_EOL;
});
$i=0;
foreach($curlArray as $url) {
    $client->addTask('getVersion', $url,null,$i);
    $i++;
}
echo 'fetching'."\r\n";
$client->runTasks();
ksort($data);
var_dump($data);