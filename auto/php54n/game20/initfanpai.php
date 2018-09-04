<?php
         global $Room;
         $id=$data2['room'];

        if($data2['time']!=$Room[$id]['timexx']){
            return false;
        }

        $Room[$id]['timexx']=time();
        $Room[$id]['xx']['zt']=5;
        cleardjs($Room[$id]['djs'],$id);



         $time_interval = 8;
         //翻牌时间
         $Room[$id]['time']=time()+$time_interval;
         $Room[$id]['timexx']=time();
        djs($time_interval,'setfanpai',$id,$Room[$id]['timexx']);


        foreach ($Room[$id]['user'] as $connection3) {
            if($connection3->user['zt']=='1'){
                 $Room[$id]['user'][$connection3->user['id']]->user['tpzt']='-1';
            }
            if($connection3->user['online']!='-1'){
                act('djs',$Room[$id]['time'],$connection3);
                act('divRobBankerText',4,$connection3);


                if($connection3->user['zt']=='1'){
                    act('operationButton',10,$connection3);
                    $msg=array();
                    $msg['card']=$Room[$id]['allnewcard'];
                    $msg['newcard']=$Room[$id]['allnewcard'];
                    $msg['allniu']=$Room[$id]['allniu'];
                    //$msg['sfniu']=$Room[$id]['allsfniu'];
                    act('fapaistart',$msg,$connection3);
                }
            }
        }