<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'lib/database.php';
OpenConnection();
global $include_prefix;
include_once $include_prefix . 'lib/game.functions.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$lastNum = -1;
$prevPoint = -1;


function sendSSE($data) {
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

function checkForUpdates($gameId) {
    global $lastNum;
    global $prevPoint;
    $lastscore = GameLastGoal($gameId);
    $gameevents = GameEvents($gameId);
    $lastevent = GameLastEvent($gameId);
    $gameevent = 0;
    if(count($gameevents)){
       $gameevent = 1;

    }

    if($lastscore['num'] > $lastNum){
        $lastNum = $lastscore['num'];  
        
        
        if($lastscore['iscallahan'] == 1){
            if($lastevent["type"] == "timeout" && ($lastevent['time'] > $prevPoint)){
                sendSSE(["action" => "update", "gameId" => $gameId, "gameevent" => $gameevent, "num" => $lastscore['num'], "time" => SecToMin($lastscore['time']),  "home" => $lastscore['ishomegoal'], "homescore" => $lastscore['homescore'], "visitorscore" => $lastscore['visitorscore'], "assistfirstname" => "Callahan", "assistlastname" => "- goal", "scorerfirstname" => $lastscore['scorerfirstname'], "scorerlastname" => $lastscore['scorerlastname'], "timeout" => 1, "timeouttime" => SecToMin($lastevent["time"]), "timeouthome" => $lastevent["ishome"]]);
            }else{
                sendSSE(["action" => "update", "gameId" => $gameId, "gameevent" => $gameevent, "num" => $lastscore['num'], "time" => SecToMin($lastscore['time']),  "home" => $lastscore['ishomegoal'], "homescore" => $lastscore['homescore'], "visitorscore" => $lastscore['visitorscore'], "assistfirstname" => "Callahan", "assistlastname" => "- goal", "scorerfirstname" => $lastscore['scorerfirstname'], "scorerlastname" => $lastscore['scorerlastname'], "timeout" => 0]);
            }
        } else {
            if($lastevent["type"] == "timeout" && ($lastevent["time"] > $prevPoint)){
                sendSSE(["action" => "update", "gameId" => $gameId, "gameevent" => $gameevent, "num" => $lastscore['num'], "time" => SecToMin($lastscore['time']), "home" => $lastscore['ishomegoal'], "homescore" => $lastscore['homescore'], "visitorscore" => $lastscore['visitorscore'], "assistfirstname" => $lastscore['assistfirstname'], "assistlastname" => $lastscore['assistlastname'], "scorerfirstname" => $lastscore['scorerfirstname'], "scorerlastname" => $lastscore['scorerlastname'], "timeout" => 1, "timeouttime" => SecToMin($lastevent["time"]), "timeouthome" => $lastevent["ishome"]]);
            } else {
                sendSSE(["action" => "update", "gameId" => $gameId, "gameevent" => $gameevent, "num" => $lastscore['num'], "time" => SecToMin($lastscore['time']), "home" => $lastscore['ishomegoal'], "homescore" => $lastscore['homescore'], "visitorscore" => $lastscore['visitorscore'], "assistfirstname" => $lastscore['assistfirstname'], "assistlastname" => $lastscore['assistlastname'], "scorerfirstname" => $lastscore['scorerfirstname'], "scorerlastname" => $lastscore['scorerlastname'], "timeout" => 0]);
            }
        }
        $prevPoint = $lastscore['time'];
    } else {
        sendSSE(["action" => "hearthbeat", "gameId" => $gameId]); 
    }
    // Zde by měl být tvůj kód pro kontrolu změn v databázi
    // Například, dotaz na tabulku uo_goal pro daný gameid
    // Pokud je nový bod, odešli zprávu klientovi
    // Zde je jen příklad pro ilustraci:
   // tvůj kód pro kontrolu nových bodů
}

// Přijmi gameid od klienta
$gameId = $_GET['game'];
$lastscore = GameLastGoal($gameId);
$lastNum = $lastscore['num'];  
$prevPoint = $lastscore['time'];
// Při připojení odešli aktuální stav
sendSSE(["action" => "init", "gameId" => $gameId]);

// Kontrola změn každých 30 sekund
while (true) {
    checkForUpdates($gameId);
    sleep(10); // Přizpůsobte podle potřeby
}
?>
