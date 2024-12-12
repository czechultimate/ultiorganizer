<?php
ob_start();
?>
<!--
[CLASSIFICATION]
category=database
type=edit
format=any
security=superadmin
customization=all

[DESCRIPTION]
title = "Edit schedule"
description = "Edit schedule"
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
$title = ("Edit schedule");
$seriesId = "";

if (!empty($_POST['seriesId'])) {
	$seriesId = $_POST['seriesId']; 
}

//season selection
$html .= "<form method='post' id='tables' action='?view=plugins/edit_schedule'>\n";


$html .= "<p>" . ("Select event") . ": <select class='dropdown' name='seriesId'>\n";

$series = SeasonSeries(CurrentSeason());

foreach ($series as $serie) {
	$html .= "<option class='dropdown' value='" . utf8entities($serie['series_id']) . "'>" . utf8entities($serie['name']) . "</option>";
}

$html .= "</select></p>\n";
$html .= "<p><input class='button' type='submit' name='select' value='" . ("Select") . "'/></p>";

$html .= "</form>";

if (isset($_POST['edit'])) {
    foreach ($_POST as $key => $value) {
      if (strpos($key, 'datetime_') === 0) {
        $parts = explode('_', $key);
        $gameId = $parts[1];
        SetScheduleByGameTime($gameId, $value);
      }
  }
}

if (isset($_POST['select'])) {

    $html .= "<form method='post' id='tables' action='?view=plugins/edit_schedule'>\n";

    $ser = GetSeries($seriesId);
    $teams  = GetScheduleBySeries($ser["series_id"]);
  
    $html .= "<table cellpadding='2' style='width:100%;'>\n";
    $html .= "<tr>";
    $html .= "<th style='width:30%;'>" . _("Hometeam") . "</th>";
    $html .= "<th style='width:30%;'>" . _("Visitorteam") . "</th>";
    $html .= "<th style='width:10%;'>" . _("Field") . "</th>";
    $html .= "<th style='width:30%;'>" . _("DateTime") . "</th>";
    $html .= "</tr>\n";
    foreach ($teams as $team) {
      $html .= "<tr style='border-bottom-style:dashed;border-bottom-width:1px;border-bottom-color:#E0E0E0;'>";

      $html .= "<td>" . $team['hometeam'] . "</td>";
      $html .= "<td>" . $team['visitorteam'] . "</td>";
      $html .= "<td>" . $team['fieldname'] . "</td>";
      $html .= "<td><input type='datetime-local' name='datetime_" . $team['game_id'] . "' value='" . date('Y-m-d\TH:i', strtotime($team['time'])) . "' /></td>";
     
      $html .= "</tr>\n";
    }
    $html .= "</table>\n";
    $html .= "<input type='hidden' id='seriesId' name='seriesId' value='". $seriesId ."'>";
    $html .= "<p><input class='button' type='submit' name='edit' value='" . ("Save") . "'/></p>";

    $html .= "</form>";
}

showPage($title, $html);
?>