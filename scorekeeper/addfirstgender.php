<?php
$html = "";

$gameId = isset($_GET['game']) ? $_GET['game'] : $_SESSION['game'];
$_SESSION['game'] = $gameId;

$game_result = GameResult($gameId);

if (isset($_POST['save'])) {
	if (!empty($_POST['gender'])) {
		$starting = $_POST['gender'];
		if ($starting == "M") {
			GameSetStartingGender($gameId, 1);
		} elseif ($starting == "W") {
			GameSetStartingGender($gameId, 0);
		}
	}
	header("location:?view=addscoresheet&game=" . $gameId);
}

$mgender = "";
$wgender = "";
$ismen = GameIsFirstGenderMen($gameId);
if ($ismen == 1) {
	$mgender = "checked='checked'";
} elseif ($ismen == 0) {
	$wgender = "checked='checked'";
}

$html .= "<div data-role='header'>\n";
$html .= "<h1>" . _("First Gender") . ": " . utf8entities($game_result['hometeamname']) . " - " . utf8entities($game_result['visitorteamname']) . "</h1>\n";
$html .= "</div><!-- /header -->\n\n";

$html .= "<div data-role='content'>\n";


$html .= "<form action='?view=addfirstgender' method='post' data-ajax='false'>\n";
$html .= "<fieldset data-role='controlgroup' id='teamselection'>";
$html .= "<input type='radio' name='gender' id='mstart' value='M' $mgender />";
$html .= "<label for='mstart'>Men</label>";
$html .= "<input type='radio' name='gender' id='wstart' value='W' $wgender  />";
$html .= "<label for='wstart'>Women</label>";
$html .= "</fieldset>";
$html .= "<input type='submit' name='save' data-ajax='false' value='" . _("Save") . "'/>";
$html .= "<a href='?view=addscoresheet&amp;game=" . $gameId . "' data-role='button' data-ajax='false'>" . _("Back to score sheet") . "</a>";
$html .= "</form>";
$html .= "</div><!-- /content -->\n\n";

echo $html;
