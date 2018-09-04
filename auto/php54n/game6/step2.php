<?php
global $Room;
$id = ceil($connection->user['room']);
act('initroom', $msg, $connection);
$msg = array();
$msg['id'] = 'jsxx';
$msg['html'] = $Room[$id]['xx']['js'] . '&nbsp;/&nbsp;' . $Room[$id]['xx']['zjs'] . '&nbsp;局';
act('html', $msg, $connection);
act('statclear', '', $connection);
act('operationButton', -1, $connection);
if ($Room[$id]['time'] > 0) {
	act('zhuangclear', '', $connection);
	act('djs', $Room[$id]['time'], $connection);
	act('divRobBankerText', 1, $connection);
	if ($connection->user['zt'] == '1' && $connection->user['index'] < 4 && $Room[$id]['user'][$connection->user['id']]->user['qbank'] == '-1') {
		act('operationButton', 1, $connection);
	}
}
foreach ($Room[$id]['user'] as $connection3) {
	if ($connection3->user['qbank'] != '-1' && $connection3->user['index'] < 4 && $connection->user['zt'] == '1') {
		$msg = array();
		$msg['zt'] = $connection3->user['qbank'];
		$msg['index'] = $connection3->user['index'];
		if ($connection3->user['id'] != $connection->user['id']) {
			act('qbankshow', $msg, $connection);
		} else {
			if ($connection3->user['qbank'] == 1) {
				act('operationButton', 9, $connection);
			} else {
				act('operationButton', 8, $connection);
			}
		}
	}
} 