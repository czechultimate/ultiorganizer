<?php
include_once 'lib/club.functions.php';
include_once 'lib/series.functions.php';

$title = _("Club vs Club");
$html = "";

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

print($clubId1 . " - " . $clubId2 . " - " . $type);

$html .= "<h1>$title</h1>\n";
$html .= "<table style='white-space: nowrap;'><tr>\n";

$clubs = ClubList(true, $filter);
$seriesTypes = SeriesTypes();
$html .= "<form method='post' enctype='multipart/form-data' action='?view=clubvsclub'>\n";
$html .= "<p>" . ("Select First Club") . ": <select class='dropdown' name='club1'>\n";
foreach($clubs as $club){
  $html .= "<option class='dropdown' value='" . $club['club_id'] . "'>" . $club['name'] . "</option>";
}
$html .= "</select></p>\n";

$html .= "<p>" . ("Select Second Club") . ": <select class='dropdown' name='club2'>\n";
foreach($clubs as $club){
  $html .= "<option class='dropdown' value='" . $club['club_id'] . "'>" . $club['name'] . "</option>";
}
$html .= "</select></p>\n";

$html .= "<p>" . ("Select type") . ": <select class='dropdown' name='type'>\n";
$html .= "<option class='dropdown' value='All'>All</option>";
foreach($seriesTypes as $seriesType){
  $html .= "<option class='dropdown' value='" . $seriesType . "'>" . $seriesType . "</option>";
}
$html .= "</select></p>\n";

$html .= "<p><input class='button' type='submit' name='select' value='" . ("Select") . "'/></p>";
$html .= "</form>";

$result = ClubVsClub($clubId1, $clubId2, $type);

if(empty($result)){
  $html .= "<h1>No match found</h1>\n";
}
print_r($result);

showPage($title, $html);
