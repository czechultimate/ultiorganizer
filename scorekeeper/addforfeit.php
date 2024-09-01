<?php
$html = "";

$gameId = isset($_GET['game']) ? $_GET['game'] : $_SESSION['game'];
$_SESSION['game'] = $gameId;

$game_result = GameResult($gameId);

if (isset($_POST['save'])) {
	if (!empty($_POST['forfeit'])) {
		$forfeit = $_POST['forfeit'];
		if($forfeit == "yes"){
			GameSetForfeit($gameId, 1);
		} else if($forfeit == "no") {
			GameSetForfeit($gameId, 0);
		}
	}
	header("location:?view=addresult&game=" . $gameId);
}

$isForfeited = GameIsForfeited($gameId);
if ($isForfeited == 1) {
	$yes = "checked='checked'";
} elseif ($ishome == 0) {
	$no = "checked='checked'";
}

$html .= "<div data-role='header'>\n";
$html .= "<h1>" . _("Game Forfeited?") . ": " . utf8entities($game_result['hometeamname']) . " - " . utf8entities($game_result['visitorteamname']) . "</h1>\n";
$html .= "</div><!-- /header -->\n\n";

$html .= "<div data-role='content'>\n";


$html .= "<form action='?view=addforfeit' method='post' data-ajax='false'>\n";
$html .= "<fieldset data-role='controlgroup' id='forfeitedgame'>";
$html .= "<input type='radio' name='forfeit' id='yes' value='yes' $yes/>";
$html .= "<label for='yes'>Yes</label>";
$html .= "<input type='radio' name='forfeit' id='no' value='no' $no/>";
$html .= "<label for='no'>No</label>";
$html .= "</fieldset>";
$html .= "<input type='submit' name='save' data-ajax='false' value='" . _("Save") . "'/>";
$html .= "<a href='?view=respgames' data-role='button' data-rel='back'>" . _("Back") . "</a>";
$html .= "</form>";
$html .= "</div><!-- /content -->\n\n";

echo $html;
