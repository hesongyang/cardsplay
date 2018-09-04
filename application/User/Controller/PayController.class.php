<?php
namespace User\Controller;
use Think\Controller;
use Think\Exception;
use Common\Controller\MemberbaseController;
use User\Controller\RechargeController;
use Common\Controller\HomebaseController;

class PayController extends HomebaseController
{
    public function index(){

    		$userid=$_SESSION['user']['id'];
            $goodid=$_GET['tid'];
    		$good=M('goods')->where("id=$goodid")->find();
			$money=$good['money'];
			$point=$good['good'];
			$data['user_id']=$userid;
			$data['out_trade_no']=time().rand(1000,9999);
			$data['money']=$money;
			$data['status']=0;
			$data['type']=2;
			$data['addtime']=time();
			$data['point']=$point;
			$user=M('recharge')->where("user_id=$userid and status=0")->find();
			if(!empty($user)){
			    $u=$user['id'];
                $res=M('recharge')->where("id=$u")->save($data);
                $res=$user['id'];
            }else{
                $res=M('recharge')->add($data);

            }
			//print_r($money);die;
    	    $app  = new RechargeController();
            $a=$app->goPay($res);
//            print_r($a);die;
            $qrcode=$a['qrcode'];
   //          header('Content-type: image/jpeg');
			// //$this->success($qrcode);
			// //header($qrcode);
   //          echo $qrcode;
            //header('Location:'.$qrcode);
        
            $this->assign('qrcode',$qrcode);
            $this->assign('order_no',$res);
            $this->display(':pay');
    }
    public function OrderRes($order_no=''){
        if(!empty($order_no)){
            $where['id']=$order_no;
            $res=M('recharge')->where($where)->find();
            if($res['status']==1){
                echo 1;
            }else{
                echo 0;
            }
        }else{
            echo 0;
        }

    }
}
