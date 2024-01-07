<?php
$html = "";
$maxtimeouts = 4;

$gameId = isset($_GET['game']) ? $_GET['game'] : $_SESSION['game'];
$_SESSION['game'] = $gameId;

$game_result = GameResult($gameId);

$time = 0;



if(is_null($game_result['starttime'])){
  $timestart = strtotime($game_result['time']);
  $actualtime = time();
  $time = $actualtime -$timestart;
} else{
  $timestart = strtotime($game_result['starttime']);
  $actualtime = time();
  $time = $actualtime -$timestart;
}

if (isset($_POST['save'])) {
  $timeouts = GameTimeouts($gameId);
  $h = 0;
  $v = 0;
  while ($timeout = mysqli_fetch_assoc($timeouts)) {
    if (intval($timeout['ishome'])) {
      $h++;
    } else {
      $v++;
    }
  }

	if (!empty($_POST['team'])) {
		$home = $_POST['team'];
		if ($home == "H") {
			GameAddTimeout($gameId, $h++, $time, 1);
		} elseif ($home == "V") {
			GameAddTimeout($gameId, $v++, $time, 0);
		}
	}
	header("location:?view=addtimeouts&game=" . $gameId);
}

if (isset($_POST['delete'])) {
  if (!empty($_POST["check"])) {
    foreach($_POST["check"] as $timeoutId) {
      GameRemoveTimeout($gameId, $timeoutId);
    }
  }
	//header("location:?view=addtimeouts&game=" . $gameId);
}

$html .= "<div data-role='header'>\n";
$html .= "<h1>" . _("Time-outs") . ": " . utf8entities($game_result['hometeamname']) . " - " . utf8entities($game_result['visitorteamname']) . "</h1>\n";
$html .= "</div><!-- /header -->\n\n";

$html .= "<div data-role='content'>\n";

$html .= "<form action='?view=addtimeouts' method='post' data-ajax='false'>\n";
$html .= "<fieldset data-role='controlgroup' id='teamselection'>";
$html .= "<input type='radio' name='team' id='htime' value='H'/>";
$html .= "<label for='htime'>" . utf8entities($game_result['hometeamname']) . "</label>";
$html .= "<input type='radio' name='team' id='vtime' value='V'/>";
$html .= "<label for='vtime'>" . utf8entities($game_result['visitorteamname']) . "</label>";
$html .= "</fieldset>";
$html .= "<input type='submit' name='save' data-ajax='false' value='" . _("Add") . "'/>";
$html .= "<a href='?view=addscoresheet&amp;game=" . $gameId . "' data-role='button' data-ajax='false'>" . _("Back to score sheet") . "</a>";
$html .= "</form>";
$html .= "<br>";
$html .= "<form action='?view=addtimeouts' method='post' data-ajax='false'>\n";
$html .= "<b>Timeouts " . $game_result['hometeamname'] . "</b><br>";

$timeouts = GameTimeouts($gameId);
$h = 0;
$v = 0;
while ($timeout = mysqli_fetch_assoc($timeouts)) {
  if (intval($timeout['ishome'])) {
    $h++;
    $html .= "<label><input type='checkbox' name='check[]' value='" . $timeout['timeout_id'] ."' />";
    $html .= $h . " - " .  SecToMin($timeout['time']) . "</label>";
  }
    
}

$html .= "<b>Timeouts " . $game_result['visitorteamname'] . "</b><br>";

$h = 0;
$v = 0;
$timeouts = GameTimeouts($gameId);
while ($timeout = mysqli_fetch_assoc($timeouts)) {
  if (!intval($timeout['ishome'])) {
    $v++;
    $html .= "<label><input type='checkbox' name='check[]' value='" . $timeout['timeout_id'] . "' />";
    $html .= $v . " - " .  SecToMin($timeout['time']) . "</label>";
  }
}


$html .= "<input type='submit' name='delete' data-ajax='false' value='" . _("Delete") . "'/>";
$html .= "</div><!-- /content -->\n\n";

echo $html;
