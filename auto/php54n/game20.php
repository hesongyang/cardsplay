<?php
use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Connection\AsyncTcpConnection;
require_once __DIR__ . '/workerman/Autoloader.php';
error_reporting(E_ALL &~E_NOTICE);
date_default_timezone_set('PRC');
include 'mysql.class.php';
include 'config.php';
$taskid = $argv[3];
$dk = $argv[4];
$db = array();
echo $dk;
$server = array();
ouput("读取配置");
$typelist = array();
$yslist = array();
$worker = new Worker('websocket://0.0.0.0:' . $dk);
$worker->uidConnections = array();
$bonus = array();
$extract = array();
$connection2 = array();
$Room = array();
$Timer = new Timer();
$cards = array('A2', 'A2', 'A3', 'A4', 'A4', 'A5', 'A7', 'A7', 'A8', 'A8', 'A9', 'A10', 'A10', 'A11', 'A11', 'A12', 'A12', 'B4', 'B4', 'B6', 'B6', 'B8', 'B9', 'B10', 'B10', 'C5', 'C6', 'C6', 'C7', 'C8', 'D6', 'D7'); //小牌九一副牌
$worker->onWorkerStart = function ($worker) {
    ouput('程序开始运行');
    global $host;
    global $connection2;
    global $serverdk;
    $connection2 = new AsyncTcpConnection('ws://' . $host . ':' . $serverdk);
    $connection2->onConnect = function ($connection2) {
        global $taskid;
        ouput('链接到主服务器');
        ouput('发送身份信息到主服务器');
        $data['act'] = 'connect';
        $data['task'] = $taskid;
        $connection2->send(json_encode($data));
    } ;
    $connection2->onMessage = function ($connection2, $data) {
        global $db;
        global $taskid;
        global $server;
        $data2 = json_decode($data, true);
        if ($data2['act'] == 'start') {
            $db = new Mysql($data2['host']['hostname'], $data2['host']['username'], $data2['host']['password'], $data2['host']['dbname']);
            $server = $db->getOne("select * from jz_server where id='" . $taskid . "'");
            start();
        } else {
            reqact($data2, $connection);
        } 
    } ;
    $connection2->onClose = function ($connection2) {
        ouput('到主服务器的链接关闭');
    } ;
    $connection2->onError = function ($connection2, $code, $msg) {
        ouput('到主服务器的链接错误' . $msg);
    } ;
    $connection2->connect();
} ;
$worker->onClose = function ($connection) {
    $data2['act'] = 'close';
    reqact($data2, $connection);
    ouput('断开链接');
} ;
$worker->onConnect = function ($connection) {
    global $db;
    global $title;
    ouput("新的链接ip为 " . $connection->getRemoteIp());
} ;
$worker->onMessage = function ($connection, $data) {
    global $db;
    global $bonus;
    global $extract;
    $data2 = json_decode($data, true);
    if ($connection->user['room'] && $data2['act'] != 'timegx') {
        file_put_contents('./php54n/log/' . mb_convert_encoding('房间', "UTF-8", "UTF-8") . ceil($connection->user['room']) . '.txt', date('Y-m-d H:i:s') . ':' . $connection->user['id'] . $data . PHP_EOL, FILE_APPEND);
    } 
    if ($data2['room'] && $data2['act'] != 'timegx') {
        file_put_contents('./php54n/log/' . mb_convert_encoding('房间', "UTF-8", "UTF-8") . ceil($data2['room']) . '.txt', date('Y-m-d H:i:s') . ':' . $data . PHP_EOL, FILE_APPEND);
    } 
    reqact($data2, $connection);
} ;
Worker::runAll();
function start() {
    global $db;
    global $title;
    global $bonus;
    global $extract;
    $bonussql = $db->getOne("select * from jz_options where option_name='bonus'");
    $bonus = json_decode($bonussql['option_value'], true);
    $extractsql = $db->getOne("select * from jz_options where option_name='extract'");
    $extract = json_decode($extractsql['option_value'], true);
    echo date("Y-m-d H:i:s", time());
} 
function reqact($data2, $connection) {
    global $db;
    global $bonus;
    global $extract;
    $tpl = file_get_contents("./php54n/game20/" . $data2['act'] . ".php");
    $tpl = str_replace("<?php", "", $tpl);
    $tpl = str_replace("?>", "", $tpl);
    eval($tpl);
} 
function ouput($str) {
    $zmm = mb_convert_encoding($str, "UTF-8", "UTF-8");
    echo $zmm . "\r\n";
} 
function loginout($msg, $connection) {
    $data['msg'] = $msg;
    $data['act'] = 'loginout';
    $connection->send(json_encode($data));
    return false;
} 
function get_form($data) {
    $str = explode('&', $data);
    $sj = array();
    foreach ($str as $key => $value) {
        $a = explode('=', $value);
        $sj[$a[0]] = urldecode($a[1]);
    } 
    return $sj;
} 
function base64EncodeImage($image_file) {
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
} 
function yzm($w, $h, $code) {
    $img = imagecreatetruecolor($w, $h);
    $color = imagecolorallocate($img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
    imagefilledrectangle($img, 0, $h, $w, 0, $color);
    for ($i = 0; $i < 6; $i++) {
        $color = imagecolorallocate($img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
        imageline($img, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $color);
    } 
    for ($i = 0; $i < 100; $i++) {
        $color = imagecolorallocate($img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
        imagestring($img, mt_rand(1, 5), mt_rand(0, $w), mt_rand(0, $h), '*', $color);
    } 
    $_x = $w / 4;
    $codelist = str_split($code);
    for ($i = 0; $i < count($codelist); $i++) {
        $fontcolor = imagecolorallocate($img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
        imagettftext($img, '20', mt_rand(-30, 30), $_x * $i + mt_rand(1, 5), $h / 1.4, $fontcolor, dirname(__FILE__) . '/font.ttf', $codelist[$i]);
    } 
    imagepng($img, "images/data.png");
    imagedestroy($img);
    return base64EncodeImage("images/data.png");
} 
function randcode($num) {
    $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';
    $code = '';
    $codelist = str_split($charset);
    for ($i = 0; $i < $num; $i++) {
        $code .= $codelist[mt_rand(0, count($codelist) - 1)];
    } 
    return $code;
} 
function checkmobile($phone) {
    if (!is_numeric($phone)) {
        return false;
    } 
    return preg_match('#^13[\\d]{9}$|^14[5,7]{1}\\d{8}$|^15[^4]{1}\\d{8}$|^17[0,6,7,8]{1}\\d{8}$|^18[\\d]{9}$#', $phone) ? true : false;
} 
function sendphone($phone, $content) {
    $post_data['u'] = '975124908';
    $post_data['p'] = md5('admin');
    $post_data['c'] = urlencode("【游戏中心】" . $content);
    $post_data['m'] = $phone;
    $url = 'http://api.smsbao.com/sms?u=' . $post_data['u'] . '&p=' . $post_data['p'] . '&m=' . $post_data['m'] . '&c=' . $post_data['c'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
} 
function action($act, $msg, $connection) {
    $data['msg'] = $msg;
    $data['act'] = $act;
    $connection->send(json_encode($data));
} 
function addhtml($html, $connection) {
    $data['act'] = 'addhtml';
    $msg['html'] = $html;
    $msg['id'] = 'content';
    $data['msg'] = $msg;
    $connection->send(json_encode($data));
} 
function act($act, $msg, $connection) {
    $data['msg'] = $msg;
    $data['act'] = $act;
    if ($connection->user['online'] == 1) {
        $connection->send(json_encode($data));
    } 
    return false;
} 
function title($title, $connection) {
    $data['msg'] = $title;
    $data['act'] = 'Title';
    $connection->send(json_encode($data));
    return false;
} 
function tip($msg, $connection) {
    $data['msg'] = $msg;
    $data['act'] = 'prompt';
    $connection->send(json_encode($data));
    return false;
} 
function loading($url, $connection, $data = array()) {
    $data = $data;
    $data['msg'] = $url;
    $data['act'] = 'loading';
    $connection->send(json_encode($data));
    return false;
} 
function error($msg, $connection) {
    $data['msg'] = $msg;
    $data['act'] = 'error';
    $connection->send(json_encode($data));
    return false;
} 
function success($msg, $url = '', $connection) {
    $zzxx['msg'] = $msg;
    $zzxx['url'] = $url;
    $data['msg'] = $zzxx;
    $data['act'] = 'success';
    $connection->send(json_encode($data));
    return false;
} 
function fenpai($card) { // 随机分4张牌
    for ($i = 0; $i < 4; $i++) {
        $index = rand(0, count($card) - 1);
        $fenpai[] = $card[$index];
        array_splice($card, $index, 1);
    } 
    $result['card'] = $card;
    $result['fenpai'] = $fenpai;
    return $result;
} 
function djs($time, $act, $room, $timexx) {
    global $connection2;
    $data['timeout'] = time() + $time;
    $data['act'] = $act;
    $data['room'] = $room;
    $data['time'] = $timexx;
    $connection2->send(json_encode($data));
} 
function cleardjs($id, $room) {
    global $connection2;
    $data['overtime'] = 1;
    $data['id'] = $id;
    $connection2->send(json_encode($data));
    global $Room;
    foreach ($Room[$room]['user'] as $connection3) {
        if ($connection3->user['online'] != '-1') {
            act('cleardjs', '', $connection3);
        } 
    } 
} 
function niuniu($card) {
    for ($i = 0; $i < 5; $i++) {
        for ($j = $i + 1; $j < 5; $j++) {
            for ($m = $j + 1; $m < 5; $m++) {
                if (($card[$i]['val'] + $card[$j]['val'] + $card[$m]['val']) % 10 == 0) {
                    return explode(',', $i . ',' . $j . ',' . $m);
                } 
            } 
        } 
    } 
    return 0;
} 

function maxcard($card) {
    $max = 0;
    foreach ($card as $key => $value) {
        if ($value['dx'] > $max) {
            $max = $value['dx'];
        } 
    } 
    if ($max < 10) {
        $max = '0' . $max;
    } 
    return $max;
} 


function b2bds($cards) {
	natsort($cards); //自然排序
    $dp = array(// 对牌
        'A12' => '20', //  天牌
        'A2' => '19', //   地牌
        'A8' => '18', //   人牌
        'A4' => '17', //    鹅牌
        'A10' => '16', //    梅牌
        'B6' => '15', //     长三
        'B4' => '14', //     板凳
        'A11' => '13', //     斧头
        'B10' => '12', //     红头十
        'A7' => '11', //       高脚七
        'C6' => '10', //       铜锤六
        'B9' => '9', //        杂9
        'A9' => '8', //        杂9
        'C8' => '7', //        杂8
        'B8' => '6', //        杂8
        'D7' => '5', //        杂7
        'C7' => '4', //        杂7
        'C5' => '3', //        杂5
        'A5' => '2', //        杂5
        'D6' => '1', //        二四
        'A3' => '0' //        丁三
        );
foreach ($cards as  $v){
	$pai9[] =  $dp[$v];
}
return max($pai9);
}


function niu($card,$type=0) {
    natsort($card); //自然排序
    $cards = $card;
	$type=1;
    $dp = array(// 对牌
        'A3,D6' => '32', // 至尊
        // 'C6,D6' => '32', // 至尊 丁三二四可换
        'A12,A12' => '31', // 双天
        'A2,A2' => '30', // 双地
        'A8,A8' => '29', // 双人
        'A4,A4' => '28', // 双鹅
        'A10,A10' => '27', // 双梅花
        'B6,B6' => '26', // 双长三
        'B4,B4' => '25', // 双板凳
        'A11,A11' => '24', // 双斧头
        'B10,B10' => '23', // 双红头
        'A7,A7' => '22', // 双高脚
        'C6,C6' => '21', // 双零林
        'A9,B9' => '20', // 杂九
        'B8,C8' => '19', // 杂八
        'C7,D7' => '18', // 杂七
        'A5,C5' => '17', // 杂五
        'A12,B9' => '16', // 天王
        'A9,A12' => '16', // 天王
        'A2,A9' => '15', // 地王
        'A2,B9' => '15', // 地王
        'A8,A12' => '14', // 天杠
        'A12,B8' => '14', // 天杠
        'A12,C8' => '14', // 天杠
        'A2,A8' => '13', // 地杠
        'A2,B8' => '13', // 地杠
        'A2,C8' => '13', // 地杠
        'A7,A12' => '12', // 天高九
        'A12,C7' => '12', // 天高九
        'A12,D7' => '12', // 天高九
        'A2,A7' => '11', // 地高九
        'A2,C7' => '11', // 地高九
        'A2,D7' => '11' // 地高九
        );
    $card = implode (',', $card); //牌型生成字符串
    if (isset($dp[$card])) { // 如果是对牌
        $ds = $dp[$card];
    } else {
	if ($type == 1){
        foreach ($cards as $k => $v) {
            $cards[$k] = $v;
            if ($v == 'A3') {
                $cards[$k] = 'D6';
            } 
            if ($v == 'D6') {
                $cards[$k] = 'A3';
            } 
        }
	}		
        $cards = implode (',', $cards); //牌型生成字符串	
        $cards = preg_replace("/[A-Z]/", "", $cards);
        $cards = explode(',', $cards); //重新生成数组
        $ds1 = array_sum($cards) % 10 + 1 ; //点数计算
        $card = preg_replace("/[A-Z]/", "", $card);
        $card = explode(',', $card); //重新生成数组
        $ds = array_sum($card) % 10 + 1 ; //点数计算	
        if ($ds1 > $ds) {
            $ds = $ds1;
        } 
    } 

    return $ds;
} 
function sfwhn($card) { // 五花牛
    $zt = 1;
    foreach ($card as $key => $value) {
        if ($value['pai'] < 11) {
            $zt = 0;
        } 
    } 
    return $zt;
} 
function sfzdn($card) { // 炸弹牛
    $zt = 0;
    $paixx = array();
    foreach ($card as $key => $value) {
        $paixx[] = $value['pai'];
    } 
    $count = array_count_values($paixx);
    if (in_array('4', $count)) {
        $zt = 1;
    } 
    return $zt;
} 
function sfwxn($card) { // 五小牛
    $hz = 0;
    $zt = 1;
    foreach ($card as $key => $value) {
        $hz = $value['pai'] + $hz;
    } 
    if ($hz > 10) {
        $zt = 0;
    } 
    return $zt;
} 

function dianshu($card) {
    natsort($card); //自然排序
    $dp = array(// 对牌
        'A3,D6' => '32', // 至尊
        'C3,D6' => '32', // 至尊 丁三二四可换
        'A12,A12' => '31', // 双天
        'A2,A2' => '30', // 双地
        'A8,A8' => '29', // 双人
        'A4,A4' => '28', // 双鹅
        'A10,A10' => '27', // 双梅花
        'B6,B6' => '26', // 双长三
        'B4,B4' => '25', // 双板凳
        'A11,A11' => '24', // 双斧头
        'B10,B10' => '23', // 双红头
        'A7,A7' => '22', // 双高脚
        'C6,C6' => '21', // 双零林
        'A9,B9' => '20', // 杂九
        'B8,C8' => '19', // 杂八
        'C7,D7' => '18', // 杂七
        'A5,C5' => '17', // 杂五
        'A12,B9' => '16', // 天王
        'A9,A12' => '16', // 天王
        'A2,A9' => '15', // 地王
        'A2,B9' => '15', // 地王
        'A8,A12' => '14', // 天杠
        'A12,B8' => '14', // 天杠
        'A12,C8' => '14', // 天杠
        'A2,A8' => '13', // 地杠
        'A2,C8' => '13', // 地杠
        'A7,A12' => '12', // 天高九
        'A12,C7' => '12', // 天高九
        'A12,D7' => '12', // 天高九
        'A2,A7' => '11', // 地高九
        'A2,C7' => '11', // 地高九
        'A2,D7' => '11' // 地高九
        );
    $card = implode (',', $card); //牌型生成字符串
    if (isset($dp[$card])) { // 如果是对牌
        $ds = $dp[$card];
    } else {
        $card = preg_replace("/[A-Z]/", "", $card);
        $card = explode(',', $card); //重新生成数组
        $ds = array_sum($card) % 10; //点数计算
    } 

    return $ds;
} 

