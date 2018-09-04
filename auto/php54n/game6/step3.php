<?php
global $Room;
$id = ceil($connection->user['room']);
act('initroom', $msg, $connection);
$msg = array();
$msg['id'] = 'jsxx';
$msg['html'] = $Room[$id]['xx']['js'] . '&nbsp;/&nbsp;' . $Room[$id]['xx']['zjs'] . '&nbsp;局';
act('html', $msg, $connection);
act('zhuangclear', '', $connection);
act('sss', $Room[$id]['bank']['index'], $connection);
act('statclear', '', $connection);
act('operationButton', '-1', $connection);
if ($Room[$id]['xx']['shangxia'] == 1) {
	act('allfapai', '3', $connection);
} else {
	act('allfapai', '2', $connection);
	act('shoupai', $Room[$id]['oldcard']['all'], $connection);
}
if ($Room[$id]['time'] > 0) {
	act('djs', $Room[$id]['time'], $connection);
	act('divRobBankerText', 3, $connection);
	if ($connection->user['index'] != $Room[$id]['bank']['index']) {
		if ($connection->user['index'] < 4) {
			act('operationButton', 10, $connection);
		}
		act('cmxx', $Room[$id]['cm'], $connection);
	} else {
		act('operationButton', 11, $connection);
	}
	act('xianstart', $Room[$id]['bank']['index'], $connection);
}
foreach ($Room[$id]['user'] as $connection3) {
	foreach ($connection3->user['xiazhu'] as $key => $value) {
		if ($connection3->user['id'] == $connection->user['id']) {
			$msg = array();
			$msg['xz'] = $value;
			if ($msg['xz'] % 10 == 1) {
				$msg['xz'] = $value - 1;
			}
			$msg['xzindex'] = $key;
			act('showmeplus', $msg, $connection);
		}
		$msg = array();
		$msg['index'] = $connection3->user['index'];
		$msg['xz'] = $value;
		if ($msg['xz'] % 10 == 1) {
			$msg['xz'] = $value - 1;
		}
		$msg['xzindex'] = $key;
		$msg['bank'] = $Room[$id]['bank']['index'];
		act('addxz', $msg, $connection);
	}
}