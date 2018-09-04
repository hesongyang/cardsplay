<?php
    //cardtips 随机组牌
        global $Room;
        $id=$connection->user['room'];
		$indexs = $data2['indexs'];
		$pai = $Room[$id]['user'][$connection->user['id']]->user['newcard'];

$dd =array_flip($indexs);	//反装数组键值


		
$Room[$id]['user'][$connection->user['id']]->user['card2'] = array($pai[$indexs[0]],$pai[$indexs[1]]); //生成牌1
$Room[$id]['user'][$connection->user['id']]->user['card1'] = array_values(array_diff_key($pai,$dd));    //生成牌2