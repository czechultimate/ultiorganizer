<?php
ob_start();
?>
<!--
[CLASSIFICATION]
category=database
type=import
format=any
security=superadmin
customization=all

[DESCRIPTION]
title = "Check playerlists"
description = "Check player list from specific series"
-->
<?php
ob_end_clean();
if (!isSuperAdmin()) {
	die('Insufficient user rights');
}

include_once 'lib/season.functions.php';
include_once 'lib/series.functions.php';
include_once 'lib/standings.functions.php';

$html = "";
$title = ("Check series playerlists");
$seriesId = "";

if (!empty($_POST['seriesId'])) {
	$seriesId = $_POST['seriesId']; 
}

if (isset($_POST['check'])) {
	$playlists = GetPlaylistsFromSeries($seriesId);
	$html .= "<p>";
	foreach($playlists as $player){
		$html .= $player['team_name'] . " - " . $player['firstname'] . " " . $player['lastname'] . "<br>"; 
	}
	$html .= "</p>";
}

//season selection
$html .= "<form method='post' id='tables' action='?view=plugins/check_playlists'>\n";


$html .= "<p>" . ("Select event") . ": <select class='dropdown' name='seriesId'>\n";

$series = SeasonSeries(CurrentSeason());

foreach ($series as $serie) {
	$html .= "<option class='dropdown' value='" . utf8entities($serie['series_id']) . "'>" . utf8entities($serie['name']) . "</option>";
}

$html .= "</select></p>\n";
$html .= "<p><input class='button' type='submit' name='check' value='" . ("Check") . "'/></p>";

$html .= "</form>";

showPage($title, $html);
?>