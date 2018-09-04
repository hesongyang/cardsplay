<?php
global $Room;
$id = $connection->user['room'];
act('initroom', $msg, $connection);
act('djs', $Room[$id]['time1'], $connection); 