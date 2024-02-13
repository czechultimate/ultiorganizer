<?php

include_once 'lib/database.php';
OpenConnection();
global $include_prefix;
include_once $include_prefix . 'lib/game.functions.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$lastTime = 0;

function sendSSE($data) {
    echo "data: " . $data . "\n\n";
    ob_flush();
    flush();
}

function checkForUpdates($gameId) {
    global $lastTime;
    $lastevents = GetGameLastGoals($gameId, $lastTime);

    foreach($lastevents as $lastevent){
        sendSSE($lastevent['event_data']);
        $lastTime = $lastevent['time'];
    }

    return $lastTime;
}

$gameId = $_GET['game'];
$lastscore = GetGameLastPoint($gameId);
if (!is_null($lastscore['time'])){
    $lastTime = 8;//$lastscore['time'];
}


sendSSE(json_encode(["action" => "init", "gameId" => $gameId]));


while (checkForUpdates($gameId)) {
    sleep(5);
}

sendSSE(json_encode(["action" => "close"]));
?>
