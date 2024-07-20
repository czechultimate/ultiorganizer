<?php
include_once $include_prefix .'lib/club.functions.php';
include_once $include_prefix .'lib/timetable.functions.php';
include_once $include_prefix .'lib/series.functions.php';

$title = _("Club vs Club");
$html = "";

$clubId1 = 0;
$clubId2 = 0;
$type = "All";

if (!empty($_POST['club1'])){
  $clubId1 = intval($_POST['club1']);
}
if (!empty($_POST['club2'])){
  $clubId2 = intval($_POST['club2']);
}
if (!empty($_POST['type'])){
  $type = $_POST['type'];
}

$html .= "<h1>" . _("Club vs Club") . "</h1>";


$clubs = ClubList(true, $filter);
$seriesTypes = SeriesTypes();
$html .= "<form method='post' enctype='multipart/form-data' action='?view=clubvsclub'>\n";
$html .= "<p>" . ("Select First Club") . ": <select class='dropdown' name='club1'>\n";
foreach($clubs as $club){
  if($clubId1 == $club['club_id']){
    $html .= "<option class='dropdown' value='" . $club['club_id'] . "'selected>" . $club['name'] . "</option>";
  }else {
    $html .= "<option class='dropdown' value='" . $club['club_id'] . "'>" . $club['name'] . "</option>";
  }
}
$html .= "</select></p>\n";

$html .= "<p>" . ("Select Second Club") . ": <select class='dropdown' name='club2'>\n";
foreach($clubs as $club){
  if($clubId2 == $club['club_id']){
    $html .= "<option class='dropdown' value='" . $club['club_id'] . "'selected>" . $club['name'] . "</option>";
  }else {
    $html .= "<option class='dropdown' value='" . $club['club_id'] . "'>" . $club['name'] . "</option>";
  }
}
$html .= "</select></p>\n";

$html .= "<p>" . ("Select type") . ": <select class='dropdown' name='type'>\n";
$html .= "<option class='dropdown' value='All'>All</option>";
foreach($seriesTypes as $seriesType){
  if($type == $seriesType){
    $html .= "<option class='dropdown' value='" . $seriesType . "'selected>" . $seriesType . "</option>";
  } else {
    $html .= "<option class='dropdown' value='" . $seriesType . "'>" . $seriesType . "</option>";
  }
}
$html .= "</select></p>\n";

$html .= "<p><input class='button' type='submit' name='select' value='" . ("Select") . "'/></p>";
$html .= "</form>";

$games = ClubVsClub($clubId1, $clubId2, $type);
$prevType = "";
$isTableOpen = false;
$club1wins = 0;
$club2wins = 0;

if(mysqli_num_rows($games) != 0){
  while ($game = mysqli_fetch_assoc($games)) {
    if(intval($game['homescore']) > intval($game['visitorscore'])){
      if($game['homeclub'] == $clubId1){
        $club1wins++;
      } else {
        $club2wins++;
      }
    } else {
      if($game['visitorclub'] == $clubId1){
        $club1wins++;
      } else {
        $club2wins++;
      }
    }
  }
}

$games = ClubVsClub($clubId1, $clubId2, $type);
if(mysqli_num_rows($games) == 0){
  $html .= "<h1>No match found</h1>\n";
} else {
  $html .= "<h2>". ClubName($clubId1) . " " . $club1wins . " - " . $club2wins . " " . ClubName($clubId2) . "</h2>\n";
  $html .= DivisionTypeView($games);
}

showPage($title, $html);
