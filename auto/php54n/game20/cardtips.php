<?php
    //cardtips 随机组牌
        global $Room;
        $id=$connection->user['room'];
		
		$pai = $Room[$id]['user'][$connection->user['id']]->user['newcard'];
         
		
foreach ($pai as $k => $v) {
    for ($i = 0; $i < 3 - $k; $i++) {
        $card[] = array('zh' => array($v, $pai[$k + $i+1]),'ds' =>niu(array($v, $pai[$k + $i+1])));
    } 
} 
//生成新卡排序
foreach ($card as $k => $v){
	
	$ds[$k] = $v['ds'];
	
}		
		
$mas=$card[array_search(max($ds),$ds)]['zh'];		
		
		act('cardTipsSelect',$mas,$connection);