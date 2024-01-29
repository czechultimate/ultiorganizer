<?php
include_once $include_prefix . 'lib/season.functions.php';
include_once $include_prefix . 'lib/series.functions.php';
include_once $include_prefix . 'lib/pool.functions.php';
include_once $include_prefix . 'lib/statistical.functions.php';

$title = _("Club spirit");
$html = "";

$season = iget("season");

if (empty($season)) {
  $season = CurrentSeason();
}

$seasonInfo = SeasonInfo($season);
$series = SeasonSeries($season, true);
$clubSpirit = array();

$isstatdata = IsStatsDataAvailable();

$html .= "<h1>" . _("Club Spirit") . "</h1>";
$html .= "<h2><a href='?view=teams&season=" . $season . "&list=byspirit'>" . $seasonInfo['name'] . "</a></h2>";

$html .= CommentHTML(1, $season);

  if ($seasonInfo['showspiritpoints'] || isSeasonAdmin($season)) {

    $categories = SpiritCategories($seasonInfo['spiritmode']);
    $html .= "<div class='TableContainer3'>\n";
    $html .= "<ol>";
    foreach ($categories as $cat) {
      if ($cat['index'] > 0 && $cat['index'] < 6)
        $html .= "<li>" . utf8entities(_($cat['text'])) . "</li>";
    }
    $html .= "</ol>\n";
    $html .= "</div>\n";

    foreach ($series as $row) {
      $spiritAvg = SeriesSpiritBoard($row['series_id']);
      foreach ($spiritAvg as $teamAvg) {
       
        $clubName = ClubName($teamAvg['club']);
        $clubId = $teamAvg['club'];
        if(!array_key_exists($clubId, $clubSpirit)) {
            $tmpArray = array('clubName' => $clubName);
            $tmpArray['clubId'] = $clubId;
            $tmpArray['games'] = $teamAvg['games'];
            foreach ($categories as $cat) {
                if ($cat['index'] > 0 && $cat['index'] < 6 && isset($teamAvg[$cat['category_id']])){
                  $tmpArray[$cat['category_id']] = number_format($teamAvg[$cat['category_id']], 2);
                }
            }
            $tmpArray['total'] = number_format($teamAvg['total'], 2);
            $clubSpirit[$clubId] = $tmpArray;
        } else {
            $clubSpirit[$clubId]['games'] += $teamAvg['games'];
            foreach ($categories as $cat) {
                if ($cat['index'] > 0 && $cat['index'] < 6 && isset($teamAvg[$cat['category_id']])){
                    $clubSpirit[$clubId][$cat['category_id']] = SafeDivide($clubSpirit[$clubId][$cat['category_id']] + number_format($teamAvg[$cat['category_id']], 2), 2);
                }
            }
            $clubSpirit[$clubId]['total'] = SafeDivide($clubSpirit[$clubId]['total'] + number_format($teamAvg['total'], 2), 2);
         }
      }
      
    }
    
      usort($clubSpirit, function ($a, $b) {
        return $a['total'] < $b['total'];
      });
      $html .= "<div class='TableContainer3'>\n";
      $html .= "<table cellspacing='0' border='0' width='100%' id='clubspirit'>\n";
      $html .= "<tr><th style='width:150px'>" . _("Club") .  "</th>";
      $html .= "<th>" . _("Games") . "</th>";
      foreach ($categories as $cat) {
        if ($cat['index'] > 0 && $cat['index'] < 6)
          $html .= "<th class='center'>" . _($cat['index']) . "</th>";
      }
      $html .= "<th class='center'>" . _("Tot.") . "</th>";
      $html .= "</tr>\n";


      foreach ($clubSpirit as $clubAvg) {
        $html .= "<td><a href='?view=clubcard&club=" . $clubAvg['clubId'] . "'>" . $clubAvg['clubName'] . "</a></td>";
        $html .= "<td>" . $clubAvg['games'] . "</td>";
        foreach ($categories as $cat) {
          if ($cat['index'] > 0 && $cat['index'] < 6 && isset($clubAvg[$cat['category_id']])) {
            if ($cat['factor'] != 0)
              $html .= "<td class='center'><b>" . number_format($clubAvg[$cat['category_id']], 2) . "</b></td>";
            else
              $html .= "<td class='center'>" . number_format($teaclubAvgmAvg[$cat['category_id']], 2) . "</td>";
          }
        }
        $html .= "<td class='center'><b>" . number_format($clubAvg['total'], 2) . "</b></td>";
        $html .= "</tr>\n";
      }
      $html .= "</table>";
      $html .= "</div>\n";
    }
  


showPage($title, $html);
