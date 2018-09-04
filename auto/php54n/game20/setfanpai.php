<?php
global $Room;
$id = $data2['room'];
if ($data2['time'] != $Room[$id]['timexx']) {
    return false;
} 

$Room[$id]['timexx'] = time();
$Room[$id]['xx']['zt'] = '6';

cleardjs($Room[$id]['djs'], $id);
$Room[$id]['user'][$Room[$id]['bank']['id']]->user['wins'] = 1;
foreach ($Room[$id]['user'] as $connection3) {
    if ($connection3->user['zt'] == '1' && $connection3->user['tpzt'] == '-1') {
        $Room[$id]['user'][$connection3->user['id']]->user['tpzt'] = '1';

        foreach ($Room[$id]['user'] as $connection4) {
            // 闲家变量定义
            $card1 = $Room[$id]['user'][$connection3->user['id']]->user['card1'];
            $card2 = $Room[$id]['user'][$connection3->user['id']]->user['card2'];
            $max1 = b2bds($card1);
            $max2 = b2bds($card2);
            $cardType1 = niu($Room[$id]['user'][$connection3->user['id']]->user['card1']);
            $cardType2 = niu($Room[$id]['user'][$connection3->user['id']]->user['card2']); 
            // 庄家
            $zjcardType1 = niu($Room[$id]['user'][$Room[$id]['bank']['id']]->user['card1']);
            $zjcardType2 = niu($Room[$id]['user'][$Room[$id]['bank']['id']]->user['card2']);
            $maxz1 = b2bds($Room[$id]['user'][$Room[$id]['bank']['id']]->user['card1']);
            $maxz2 = b2bds($Room[$id]['user'][$Room[$id]['bank']['id']]->user['card2']);
			$info['win1'] = true;
			$info['win2'] = true;
            if ($connection4->user['online'] != '-1') {
                if ($Room[$id]['bank']['id'] != $connection3->user['id'] && $connection3->user['zt'] == '1') {
                    // 比大小
                    if ($cardType1 > $zjcardType1) {
                        $info['win1'] =  $Room[$id]['user'][$connection3->user['id']]->user['win1'] = true;
                    } 
                    if ($cardType1 == $zjcardType1) { // 点数相同比大小
                        $info['win1'] = $Room[$id]['user'][$connection3->user['id']]->user['win1'] = ($max1 > $maxz1) ? true : false;
                    } 
                    if ($cardType1 < $zjcardType1) {
                        $info['win1'] = $Room[$id]['user'][$connection3->user['id']]->user['win1'] =  false;
                    } 
                    if ($cardType2 > $zjcardType2) {
                        $info['win2'] = $Room[$id]['user'][$connection3->user['id']]->user['win2'] = true;
                    } 
                    if ($cardType2 == $zjcardType2) { // 点数相同比大小
                        $info['win2'] =  $Room[$id]['user'][$connection3->user['id']]->user['win2'] = ($max2 > $maxz2) ? true : false;
                    } 
                    if ($cardType2 < $zjcardType2) {
                        $info['win2'] = $Room[$id]['user'][$connection3->user['id']]->user['win2'] = false;
                    } 
					if ($info['win1'] == true && $info['win2'] == true){
						$Room[$id]['user'][$connection3->user['id']]->user['wins'] = 2; //赢
					}
					if ($info['win1'] == false || $info['win2'] == false)
					{
                        $Room[$id]['user'][$connection3->user['id']]->user['wins'] = 1;  //和
					}
					if ($info['win1'] == false && $info['win2'] == false)
					{
						
						$Room[$id]['user'][$connection3->user['id']]->user['wins'] = 0;  //输
					}
                } 
                // {"msg":{"index":1,"win1":true,"win2":true,"card1":["A11","A4"],"cardType1":6,"card2":["A11","D7"],"cardType2":9},"act":"showothertanpai"}
                // $Room[$id]['user'][$connection->user['id']]->user['card2']
                $info['index'] = $connection3->user['index'];
                $info['card1'] = $card1 ;
                $info['cardType1'] = $cardType1;
                $info['card2'] = $card2;
                $info['cardType2'] = $cardType2;
                act('showothertanpai', $info, $connection4);
            } 
        } 
    } 
} 
$jibixx = array();
$bankjf = 0;
if ($Room[$id]['minuser'] == $Room[$id]['bank']['id']) {
    $fx = 1;
} elseif ($Room[$id]['maxuser'] == $Room[$id]['bank']['id']) {
    $fx = 2;
} else {
    $fx = 0;
} 
foreach ($Room[$id]['user'] as $connection3) {
    // 比大小
    if ($Room[$id]['bank']['id'] != $connection3->user['id'] && $connection3->user['zt'] == '1') {
        $data = array();
        $data['fx'] = $fx;
        $data['bank']['index'] = $Room[$id]['bank']['index']; 
        // ouput('倍数：'.$connection3->user['beishu']);
        if (!$connection3->user['beishu']) {
            $connection3->user['beishu'] = 1;
        } 
        if (!$Room[$id]['beishu']) {
            $Room[$id]['beishu'] = 1;
        } 
        $jifen = $Room[$id]['df'] * $connection3->user['beishu'] * $Room[$id]['beishu'];
        if ($Room[$id]['user'][$connection3->user['id']]->user['wins'] ==2 ) { //赢
            $jifen = $jifen * $Room[$id]['niuniu'][$Room[$id]['user'][$connection3->user['id']]->user['niu']];
            $Room[$id]['user'][$connection3->user['id']]->user['dqjf'] = $Room[$id]['user'][$connection3->user['id']]->user['dqjf'] + $jifen;
            $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'] = $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'] - $jifen;
            $data['lose']['index'] = $Room[$id]['bank']['index'];
            $data['lose']['dqjf'] = $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'];
            $data['win']['index'] = $connection3->user['index'];
            $data['win']['dqjf'] = $Room[$id]['user'][$connection3->user['id']]->user['dqjf'];
            $bankjf = $bankjf - $jifen;
        } 
 /*        if ($Room[$id]['user'][$connection3->user['id']]->user['niu'] == $Room[$id]['user'][$Room[$id]['bank']['id']]->user['niu']) {
            if ($Room[$id]['user'][$connection3->user['id']]->user['cardmax'] > $Room[$id]['user'][$Room[$id]['bank']['id']]->user['cardmax']) {
                $jifen = $jifen * $Room[$id]['niuniu'][$Room[$id]['user'][$connection3->user['id']]->user['niu']];
                $Room[$id]['user'][$connection3->user['id']]->user['dqjf'] = $Room[$id]['user'][$connection3->user['id']]->user['dqjf'] + $jifen;
                $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'] = $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'] - $jifen;
                $data['lose']['index'] = $Room[$id]['bank']['index'];
                $data['lose']['dqjf'] = $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'];
                $data['win']['index'] = $connection3->user['index'];
                $data['win']['dqjf'] = $Room[$id]['user'][$connection3->user['id']]->user['dqjf'];
                $bankjf = $bankjf - $jifen;
            } else {
                $jifen = $jifen * $Room[$id]['niuniu'][$Room[$id]['user'][$Room[$id]['bank']['id']]->user['niu']];
                $Room[$id]['user'][$connection3->user['id']]->user['dqjf'] = $Room[$id]['user'][$connection3->user['id']]->user['dqjf'] - $jifen;
                $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'] = $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'] + $jifen;
                $data['win']['index'] = $Room[$id]['bank']['index'];
                $data['win']['dqjf'] = $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'];
                $data['lose']['index'] = $connection3->user['index'];
                $data['lose']['dqjf'] = $Room[$id]['user'][$connection3->user['id']]->user['dqjf'];
                $bankjf = $bankjf + $jifen;
                $jifen = 0 - $jifen;
            } 
        }  */
		if ($Room[$id]['user'][$connection3->user['id']]->user['wins'] == 1 ) { // 和
           $jifen = 0;
            $Room[$id]['user'][$connection3->user['id']]->user['dqjf'] = $Room[$id]['user'][$connection3->user['id']]->user['dqjf'] - $jifen;
            $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'] = $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'] + $jifen;
            $data['win']['index'] = $Room[$id]['bank']['index'];
            $data['win']['dqjf'] = $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'];
            $data['lose']['index'] = $connection3->user['index'];
            $data['lose']['dqjf'] = $Room[$id]['user'][$connection3->user['id']]->user['dqjf'];
            $bankjf = $bankjf + $jifen;
            $jifen = 0 - $jifen;
        } 
        if ($Room[$id]['user'][$connection3->user['id']]->user['wins'] == 0 ) { // 输
            $jifen = $jifen * $Room[$id]['niuniu'][$Room[$id]['user'][$Room[$id]['bank']['id']]->user['niu']];
            $Room[$id]['user'][$connection3->user['id']]->user['dqjf'] = $Room[$id]['user'][$connection3->user['id']]->user['dqjf'] - $jifen;
            $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'] = $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'] + $jifen;
            $data['win']['index'] = $Room[$id]['bank']['index'];
            $data['win']['dqjf'] = $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'];
            $data['lose']['index'] = $connection3->user['index'];
            $data['lose']['dqjf'] = $Room[$id]['user'][$connection3->user['id']]->user['dqjf'];
            $bankjf = $bankjf + $jifen;
            $jifen = 0 - $jifen;
        } 
        $jibixx[] = array('dqjf' => $Room[$id]['user'][$connection3->user['id']]->user['dqjf'], 'index' => $Room[$id]['user'][$connection3->user['id']]->user['index']);

        foreach ($Room[$id]['user'] as $connection4) {
            if ($connection4->user['online'] != -1) {
                // act('mp3play','mp3gold',$connection3);
                act('jibi', $data, $connection4);
            } 
        } 

        $djxx[] = array('user' => $connection3->user, 'sfbank' => '0', 'jf' => $jifen, 'beishu' => $connection3->user['beishu']);
    } 
} 
$jibixx[] = array('dqjf' => $Room[$id]['user'][$Room[$id]['bank']['id']]->user['dqjf'], 'index' => $Room[$id]['user'][$Room[$id]['bank']['id']]->user['index'], 'fx' => $fx);

$djxx[] = array('user' => $Room[$id]['user'][$Room[$id]['bank']['id']]->user, 'sfbank' => '1', 'jf' => $bankjf, 'beishu' => $Room[$id]['beishu']);

foreach ($Room[$id]['user'] as $connection3) {
    if ($connection3->user['online'] != -1) {
        act('jibichange', $jibixx, $connection3);
    } 
} 
// 牌局信息入库
foreach ($djxx as $key => $value) {
    unset($djxx[$key]['user']['nickname']);
} 
$add['room'] = $id;
$add['js'] = $Room[$id]['xx']['js'];
$add['djxx'] = json_encode($djxx, JSON_UNESCAPED_UNICODE);
$db->insert('jz_dj_room', $add);

if ($fx == 0) {
    $time_interval = 8;
} else {
    $time_interval = 6;
} 
$Room[$id]['time'] = time() + $time_interval;
$Room[$id]['timexx'] = time();
djs($time_interval, 'initroom', $id, $Room[$id]['timexx']);

