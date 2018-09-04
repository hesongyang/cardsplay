<?php

class time
{
	private $year;
	private $month;
	private $day;
	private $hour;
	private $minute;
	private $second;
	private $microtime;
	private $weekday;
	private $longDate;
	private $diffTime;
	private $time;

	function getyear($time = "", $type = "")
	{
		if ($time == "") {
			$time = time();
		}
		if ($type == 1) {
			return $this->year = date("y", $time);
		} else {
			return $this->year = date("Y", $time);
		}
	}

	function getmonth($time = "", $type = "")
	{
		if ($time == "") {
			$time = time();
		}
		switch ($type) {
			case 1:
				$this->month = date("n", $time);
				break;
			case 2:
				$this->month = date("m", $time);
				break;
			case 3:
				$this->month = date("M", $time);
				break;
			case 4:
				$this->month = date("F", $time);
				break;
			default:
				$this->month = date("n", $time);
		}
		return $this->month;
	}

	function getday($time = "", $type = "")
	{
		if ($time == "") {
			$time = time();
		}
		if ($type == 1) {
			$this->day = date("d", $time);
		} else {
			$this->day = date("j", $time);
		}
		return $this->day;
	}

	function gethour($time = "", $type = "")
	{
		if ($time == "") {
			$time = time();
		}
		switch ($type) {
			case 1:
				$this->hour = date("H", $time);
				break;
			case 2:
				$this->hour = date("h", $time);
				break;
			case 3:
				$this->hour = date("G", $time);
				break;
			case 4:
				$this->hour = date("g", $time);
				break;
			default :
				$this->hour = date("H", $time);
		}
		return $this->hour;
	}

	function getminute($time = "", $type = "")
	{
		if ($time == "") {
			$time = time();
		}
		$this->minute = date("i", $time);
		return $this->minute;
	}

	function getTime($time = "")
	{
		if ($time == "") {
			$time = time();
		}
		$this->time = date("Y-m-d H:i:s", $time);
		return $this->time;
	}

	function getsecond($time = "", $type = "")
	{
		if ($time == "") {
			$time = time();
		}
		$this->second = date("s", $time);
		return $this->second;
	}

	function getweekday($time = "", $type = "")
	{
		if ($time == "") {
			$time = time();
		}
		if ($type == 1) {
			$this->weekday = date("D", $time);
		} else if ($type == 2) {
			$this->weekday = date("l", $time);
		} else {
			$this->weekday = date("w", $time);
		}
		return $this->weekday;
	}

	function compare($time1, $time2)
	{
		$time1 = strtotime($time1);
		$time2 = strtotime($time2);
		if ($time1 >= $time2) {
			return 1;
		} else {
			return -1;
		}
	}

	function diffdate($time1 = "", $time2 = "")
	{
		if ($time1 == "") {
			$time1 = date("Y-m-d H:i:s");
		}
		if ($time2 == "") {
			$time2 = date("Y-m-d H:i:s");
		}
		$date1 = strtotime($time1);
		$date2 = strtotime($time2);
		if ($date1 > $date2) {
			$diff = $date1 - $date2;
		} else {
			$diff = $date2 - $date1;
		}
		if ($diff >= 0) {
			$day = floor($diff / 86400);
			$hour = floor(($diff % 86400) / 3600);
			$minute = floor(($diff % 3600) / 60);
			$second = floor(($diff % 60));
			$this->diffTime = '相差' . $day . '天' . $hour . '小时' . $minute . '分钟' . $second . '秒';
		}
		return $this->diffTime;
	}

	function buildDate($time = "", $type = "")
	{
		if ($type == 1) {
			$this->longDate = $this->getyear($time) . '年' . $this->getmonth($time) . '月' . $this->getday($time) . '日';
		} else {
			$this->longDate = $this->getyear($time) . '年' . $this->getmonth($time) . '月' . $this->getday($time) . '日' . $this->gethour($time) . ':' . $this->getminute($time) . ':' . $this->getsecond($time);
		}
		return $this->longDate;
	}
} 