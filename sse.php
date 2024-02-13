<?php

include_once 'lib/database.php';
OpenConnection();
global $include_prefix;
include_once $include_prefix . 'lib/game.functions.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$lastNum = -1;
$prevPoint = -1;
$actualPoint = -1;
$timeoutFlag = 0;
$halftimeFlag = 0;

function sendSSE($data) {
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

function checkForUpdates($gameId) {
    global $lastNum;
    global $prevPoint;
    global $actualPoint;
    global $timeoutFlag;
    global $halftimeFlag; 
    $lastscores = GameLastGoals($gameId, $lastNum);
    $lastevents = GameAllTimeouts($gameId);

    if($halftimeFlag == 0){
        $gameinfo = GameInfoLight($gameId);
    }

    if($timeoutFlag == 1){
        foreach($lastevents as $lastevent){
            if ($lastevent['time'] > $prevPoint && $lastevent['time'] < $actualPoint){
                sendSSE(["action" => "timeout", "gameId" => $gameId, "timeouttime" => SecToMin($lastevent["time"]), "timeouthome" => $lastevent["ishome"]]);
            }
        }
    $timeoutFlag = 0;
    }

    if(!is_null($gameinfo['halftime']) && $halftimeFlag == 0){
        if($gameinfo['halftime'] > $prevPoint && $gameinfo['halftime'] < $actualPoint){
            sendSSE(["action" => "halftime", "halftime" => SecToMin($gameinfo['halftime'])]);
            $halftimeFlag = 1;
        }
    }

    if(!empty($lastscores)){
        foreach($lastscores as $lastscore){
            $lastNum = $lastscore['num'];  
            
            if($lastscore['iscallahan'] == 1){
                    sendSSE(["action" => "update", "gameId" => $gameId, "num" => $lastscore['num'], "time" => SecToMin($lastscore['time']),  "home" => $lastscore['ishomegoal'], "homescore" => $lastscore['homescore'], "visitorscore" => $lastscore['visitorscore'], "assistfirstname" => "Callahan", "assistlastname" => "- goal", "scorerfirstname" => $lastscore['scorerfirstname'], "scorerlastname" => $lastscore['scorerlastname']]);
            } else {
                    sendSSE(["action" => "update", "gameId" => $gameId, "num" => $lastscore['num'], "time" => SecToMin($lastscore['time']), "home" => $lastscore['ishomegoal'], "homescore" => $lastscore['homescore'], "visitorscore" => $lastscore['visitorscore'], "assistfirstname" => $lastscore['assistfirstname'], "assistlastname" => $lastscore['assistlastname'], "scorerfirstname" => $lastscore['scorerfirstname'], "scorerlastname" => $lastscore['scorerlastname']]);
            }
            $prevPoint = $actualPoint;
            $actualPoint = $lastscore['time'];
            $timeoutFlag = 1;
    }
    }/* else {
        sendSSE(["action" => "hearthbeat", "gameId" => $gameId]); 
    }*/

    return $gameinfo["isongoing"];
}

$gameId = $_GET['game'];
$lastscore = GameLastGoal($gameId);
$halftime = GameInfoLight($gameId);
if(!is_null($halftime["halftime"])){
    $halftimeFlag = 1;
}

if (!is_null($lastscore['num'])){
    $lastNum = $lastscore['num'];  
    $actualPoint = $lastscore['time'];
}

sendSSE(["action" => "init", "gameId" => $gameId]);


while (checkForUpdates($gameId)) {
    sleep(5);
}

sendSSE(["action" => "close"]);
?>
