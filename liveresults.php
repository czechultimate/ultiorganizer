<?php

include_once 'lib/database.php';
OpenConnection();
global $include_prefix;
include_once $include_prefix . 'lib/game.functions.php';

header('Content-Type: application/json');

$lastTime = 0;
$gameId = $_GET['game'];
$lastTime = $_GET['time'];

$lastevents = GetGameLastGoals($gameId, $lastTime);

$response = array();
foreach ($lastevents as $lastevent) {
    $response[] = array("event" => $lastevent['event_data']);
}

echo json_encode($response);
ob_flush();
flush();

CloseConnection();
?>
