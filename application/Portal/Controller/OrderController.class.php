<?php
namespace Portal\Controller;

use Common\Controller\HomebaseController;

class OrderController extends HomebaseController
{
	protected $user_model;
	protected $activation_code;
	protected $all_record;
	protected $provide_help;
	protected $get_help;
	protected $match;
	protected $provide_order;

	public function __construct()
	{
		parent::__construct();
		$this->check_login();
		$this->activation_code = M('ActivationCode');
		$this->all_record = M('AllRecord');
		$this->provide_help = M('ProvideHelp');
		$this->get_help = M('GetHelp');
		$this->match = M('match');
		$this->provide_order = M('ProvideOrder');
	}

	public function index()
	{
		$provide_order = M('ProvideOrder');
		$where2['user_login'] = $this->user_login;
		$where2['status'] = 0;
		$count = $provide_order->where($where2)->count();
		$provide_order_page = $this->page($count, 20);
		$order = $provide_order->where($where2)->order(array("id" => "desc"))->limit($provide_order_page->firstRow, $provide_order_page->listRows)->select();
		foreach ($order as $key => $value) {
			$order[$key]['detail'] = $this->orderDetail($value);
		}
		$this->assign('order', $order);
		$this->assign("provide_order_page", $provide_order_page->show('default'));
		$where['user_login'] = $this->user_login;
		$where['wallet'] = 'money';
		$count = $this->all_record->where($where)->count();
		$money_page = $this->page($count, 20);
		$money = $this->all_record->where($where)->order(array("id" => "desc"))->limit($money_page->firstRow, $money_page->listRows)->select();
		$this->assign('money', $money);
		$this->assign("money_page", $money_page->show('default'));
		$where['user_login'] = $this->user_login;
		$where['wallet'] = "recommend_money";
		$count = $this->all_record->where($where)->count();
		$recommend_money_page = $this->page($count, 20);
		$recommend_money = $this->all_record->where($where)->order(array("id" => "desc"))->limit($recommend_money_page->firstRow, $recommend_money_page->listRows)->select();
		$this->assign('recommend_money', $recommend_money);
		$this->assign("recommend_money_page", $recommend_money_page->show('default'));
		$where['user_login'] = $this->user_login;
		$where['wallet'] = 'manger_money';
		$count = $this->all_record->where($where)->count();
		$manger_money_page = $this->page($count, 20);
		$manger_money = $this->all_record->where($where)->order(array("id" => "desc"))->limit($manger_money_page->firstRow, $manger_money_page->listRows)->select();
		$this->assign('manger_money', $manger_money);
		$this->assign("manger_money_page", $manger_money_page->show('default'));
		$this->display();
	}

	public function isFreeze($match_id)
	{
		$match_info = $this->match->where(array('id' => $match_id))->find();
		if ($match_id['status'] == 2) {
			$rule = new rule();
			$freeze_time = $this->bonus['freeze'];
			$confirm_time = $match_info['confirm_time'];
			$diffDays = $rule->differenceDays($confirm_time, $this->time);
			if ($diffDays < $freeze_time) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return TRUE;
		}
	}

	public function interest($match_id)
	{
	}

	public function getMatchStatus($match_id)
	{
		$match_info = $this->match->where(array('id' => $match_id))->find();
		$status[0] = '待付款';
		$status[1] = '已付款';
		$status[2] = '已完成';
		return $status[$match_info['status']];
	}

	public function orderDetail($provide_order)
	{
		$match_info = $this->match->where(array('id' => $provide_order['match_id']))->find();
		$status[0] = '待付款';
		$status[1] = '已付款';
		$status[2] = '已完成';
		$detail['status_notice'] = $status[$match_info['status']];
		$rule = new rule();
		if ($provide_order['status'] == 1) {
			$detail['is_freeze'] = FALSE;
			$detail['line_days'] = $provide_order['line_days'];
			$detail['line_interest'] = $provide_order['line_interest'];
			$detail['paidui_days'] = $provide_order['paidui_days'];
			$detail['paidui_interest'] = $provide_order['paidui_interest'];
		} else {
			if ($match_info['status'] == 2) {
				$provide_help_info = $this->provide_help->where(array('id' => $match_info['pid']))->find();
				$match_time = $match_info['create_time'];
				$diffDays = $rule->differenceDays($provide_help_info['create_time'], $match_time);
				$max_dividend_days = $this->bonus['max_dividend_days'];
				if ($diffDays >= $max_dividend_days) {
					$diffDays = $max_dividend_days;
				}
				$detail['paidui_days'] = $diffDays;
				$interest = $this->bonus['interest'];
				$num = $provide_order['money'] * $interest / 100 * $diffDays;
				$detail['paidui_interest'] = (int)$num;
				$line_income = $this->getLineIncome($provide_help_info);
				$detail['line_interest'] = $line_income;
				$line_days = $this->getLineFreeze($provide_help_info);
				$lineDiffDays = $rule->differenceDays($match_time, $this->time);
				if ($lineDiffDays < $line_days) {
					$detail['is_freeze'] = TRUE;
				} else {
					$detail['is_freeze'] = FALSE;
				}
				if ($lineDiffDays >= $line_days) {
					$this->provide_help->where(array('id' => $match_info['pid']))->save(array('line_status' => 1));
					$lineDiffDays = $line_days;
				}
				$detail['line_days'] = $lineDiffDays;
			} else {
				$provide_help_info = $this->provide_help->where(array('id' => $match_info['pid']))->find();
				$match_time = $match_info['create_time'];
				$diffDays = $rule->differenceDays($provide_help_info['create_time'], $match_time);
				$max_dividend_days = $this->bonus['max_dividend_days'];
				if ($diffDays >= $max_dividend_days) {
					$diffDays = $max_dividend_days;
				}
				$detail['paidui_days'] = $diffDays;
				$interest = $this->bonus['interest'];
				$num = $provide_order['money'] * $interest / 100 * $diffDays;
				$detail['paidui_interest'] = (int)$num;
				$line_income = $this->getLineIncome($provide_help_info);
				$detail['line_interest'] = $line_income;
				$line_days = $this->getLineFreeze($provide_help_info);
				$lineDiffDays = $rule->differenceDays($match_time, $this->time);
				if ($lineDiffDays < $line_days) {
					$detail['is_freeze'] = TRUE;
				} else {
					$detail['is_freeze'] = FALSE;
				}
				if ($lineDiffDays >= $line_days) {
					$this->provide_help->where(array('id' => $match_info['pid']))->save(array('line_status' => 1));
					$lineDiffDays = $line_days;
				}
				$detail['line_days'] = $lineDiffDays;
				$detail['is_freeze'] = TRUE;
			}
		}
		return $detail;
	}

	public function getLineFreeze($provide_help_info)
	{
		$line_freeze['a'] = 4;
		$line_freeze['b'] = 5;
		$line_freeze['c'] = 6;
		return $line_freeze[$provide_help_info['line_type']];
	}

	public function getLineIncome($provide_help_info)
	{
		$line_income['a'] = 5;
		$line_income['b'] = 7;
		$line_income['c'] = 10;
		$num = $line_income[$provide_help_info['line_type']] * $provide_help_info['money'] / 100;
		return (int)$num;
	}

	public function income()
	{
		$provide_order_id = I('get.id');
		$order = $this->provide_order->where(array('id' => $provide_order_id))->find();
		$url = U('portal/order/index');
		if ($order['user_login'] != $this->user_login) {
			$this->msg('系统繁忙', '/');
		}
		if ($order['status'] == 1) {
			$this->msg('请勿重复提交', $url);
		}
		$detail = $this->orderDetail($order);
		if ($detail['is_freeze'] == TRUE) {
			$this->msg('冻结中。。', $url);
		}
		$data['line_days'] = $detail['line_days'];;
		$data['line_interest'] = $detail['line_interest'];
		$data['paidui_days'] = $detail['paidui_days'];;
		$data['paidui_interest'] = $detail['paidui_interest'];
		$data['status'] = 1;
		$this->provide_order->where(array('id' => $provide_order_id))->save($data);
		$this->addRecord($order['user_login'], 'money', "+{$order['money']}", '本金转入');
		$res = $this->user_model->where(array('user_login' => $order['user_login']))->setInc('money', $order['money']);
		$num = $detail['paidui_interest'] + $detail['line_interest'];
		$uu = new UserController();
		$uu->bonus($this->user_login, $num);
		$this->addRecord($order['user_login'], 'manger_money', "+{$num}", '排队利息和航线收益收入');
		$res = $this->user_model->where(array('user_login' => $order['user_login']))->setInc('manger_money', $num);
		if ($res) {
			$this->msg('转出成功', $url);
		} else {
			$this->msg('转出失败', $url);
		}
	}
} 