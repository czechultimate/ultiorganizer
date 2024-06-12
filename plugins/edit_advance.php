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
title = "Edit advance to the league"
description = "Edit advance to the league"
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

//season selection
$html .= "<form method='post' id='tables' action='?view=plugins/edit_advance'>\n";


$html .= "<p>" . ("Select event") . ": <select class='dropdown' name='seriesId'>\n";

$series = SeasonSeries(CurrentSeason());

foreach ($series as $serie) {
	$html .= "<option class='dropdown' value='" . utf8entities($serie['series_id']) . "'>" . utf8entities($serie['name']) . "</option>";
}

$html .= "</select></p>\n";
$html .= "<p><input class='button' type='submit' name='select' value='" . ("Select") . "'/></p>";

$html .= "</form>";

if (isset($_POST['edit'])) {
    $maxplacements = 0;
    $maxplacements = max(count(SeriesTeams($seriesId)), $maxplacements);
    for ($i = 1; $i < $maxplacements+1; $i++) {
        SetAdvanceBySeries($seriesId, $_POST['position_' . $i], $_POST['advance_' . $i]);
    }
}

if (isset($_POST['select'])) {

    $html .= "<form method='post' id='tables' action='?view=plugins/edit_advance'>\n";

    $maxplacements = 0;
    $ser = GetSeries($seriesId);
    $advance = GetAdvanceBySeries($ser["series_id"]);
      $htmlteams = array();
      $teams  = SeriesRanking($ser['series_id']);
      foreach ($teams as $team) {
        if (isset($team['team_id'])) {
          $htmltmp = "";
          if (intval($seasonInfo['isinternational'])) {
            $htmltmp .= "<img height='10' src='images/flags/tiny/" . $team['flagfile'] . "' alt=''/> ";
          }
          $htmltmp .= "<a href='?view=teamcard&amp;team=" . $team['team_id'] . "'>" . utf8entities($team['name']) . "</a>";
          $htmlteams[] = $htmltmp;
        } else {
          $htmlteams[] = "&nbsp;";
        }
      }    
  
    $html .= "<table cellpadding='2' style='width:100%;'>\n";
    $html .= "<tr>";
    $html .= "<th style='width:20%;'>" . _("Placement") . "</th>";
    
      $html .= "<th style='width:40%;'>" . utf8entities(U_($ser['name'])) . "</th>";
      $maxplacements = max(count(SeriesTeams($ser['series_id'])), $maxplacements);
      $html .= "<th style='width:40%;'>" . _("Advance") . "</th>";
    $html .= "</tr>\n";
    for ($i = 0; $i < $maxplacements; $i++) {

      if ($i < 3) {
        $html .= "<tr style='font-weight:bold;border-bottom-style:dashed;border-bottom-width:1px;border-bottom-color:#E0E0E0;'>";
      } else {
        $html .= "<tr style='border-bottom-style:dashed;border-bottom-width:1px;border-bottom-color:#E0E0E0;'>";
      }

      $html .= "<td><input type='text' size=5 id='position_"  . ($i + 1) ."' name='position_".($i + 1)."' value='"  .($i + 1).  "' readonly></td>";
      $html .= "<td>" . $htmlteams[$i] . "</td>";

      $html .= "<td> <input type='text' size=50 id='advance_" .($i + 1)."' name='advance_".($i + 1)."' value='" . $advance[$i]["advance"] . "'></td>";
     
      $html .= "</tr>\n";
    }
    $html .= "</table>\n";
    $html .= "<input type='hidden' id='seriesId' name='seriesId' value='". $seriesId ."'>";
    $html .= "<p><input class='button' type='submit' name='edit' value='" . ("Edit") . "'/></p>";

    $html .= "</form>";
}

showPage($title, $html);
?>