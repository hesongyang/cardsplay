<?php 
// cardtips 随机组牌
global $Room;
$id = $connection->user['room'];

$mas['card1'] = $Room[$id]['user'][$connection->user['id']]->user['card1'];
$mas['card2'] = $Room[$id]['user'][$connection->user['id']]->user['card2'];

act('cardDoneResult', $mas, $connection);
        //$Room[$id]['user'][$connection->user['id']]->user['tpzt']='1';


        $tpsl=0;
        $zbsl=0;
        foreach ($Room[$id]['user'] as $connection3) {

            if($connection3->user['zt']=='1'){
                $zbsl=$zbsl+1;
            }
            if($connection3->user['tpzt']!='-1' && $connection3->user['zt']=='1'){
                $tpsl=$tpsl+1;
            }

            if($connection3->user['online']!='-1' && $connection3->user['id']!=$connection->user['id']){
                act('cardDoneResultOther',$connection->user['index'],$connection3);
            }
        }
          if($zbsl==$tpsl &&  $Room[$id]['xx']['zt']=='5' && $data2['wczt']!=1){
           $Room[$id]['xx']['zt']='6';
            $data=array();
            $data['act']='setfanpai';
            $data['time']=$Room[$id]['timexx'];
            $data['room']=$id;
            reqact($data,'');
        }     		