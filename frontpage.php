<?php
include_once 'lib/series.functions.php';

$html = "";
$title = _("Frontpage");

if (iget("hideseason")) {
  $propId = getPropId($user, 'editseason', iget("hideseason"));
  RemoveEditSeason($user, $propId);
  header("location:?view=frontpage");
  exit;
}

$html .= "<h1> TURNAJE </h1>";

$series = GetUpcomingSeries();

foreach($series as $s){
  $html .= "<p><a href=https://www.uniulti.cz/?view=games&series=" . $s['series']. "&filter=upcoming&group=all><h1>" . $s['seriesname'] . "</a></h1>
              <ul>
                <li><b>Místo konání:</b> <a href=https://www.uniulti.cz/?view=reservationinfo&reservation=" . $s['reservation'] . ">" . $s['name'] . "</a></li>
                <li><b>Termín konání:</b> " . date("d-m-Y", strtotime($s['starttime'])) . " - " . date("d-m-Y", strtotime($s['endtime'])) . "</li>
                </ul>
            </p>";
}


$html .= "<p>";
$html .= "<a href='?view=user_guide'>" . _("User Guide") . "</a>\n";
$html .= "</p>";

$urls = GetUrlListByTypeArray(array("admin"), 0);
if (!empty($urls)) {
  $html .= "<p>";
  $html .= _("In case of feedback, improvement ideas or any other questions, please contact:");
  foreach ($urls as $url) {
    $html .= "<br/><a href='mailto:" . $url['url'] . "'>" . U_($url['name']) . "</a>\n";
  }
  $html .= "</p>";
}

showPage($title, $html);
