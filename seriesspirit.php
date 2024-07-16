<?php
include_once 'lib/season.functions.php';
include_once 'lib/series.functions.php';
include_once 'lib/pool.functions.php';
include_once 'lib/team.functions.php';

$title = _("Spirit") . " ";
$viewUrl = "?view=seriesspirit";
$sort = "ranking";
$spsort = "spabs";
$html = "";
$submenuseriesid = 0;

if (iget("series")) {
  $seriesinfo = SeriesInfo(iget("series"));
  $viewUrl .= "&amp;series=" . $seriesinfo['series_id'];
  $seasoninfo = SeasonInfo($seriesinfo['season']);
  $title .= U_($seriesinfo['name']);
  $submenuseriesid = $seriesinfo['series_id'];
}

if (iget("sort")) {
  $sort = iget("sort");
}

if (iget("spsort")) {
  $spsort = iget("spsort");
}

$spiritAvg = SeriesSpiritBoardOnlyFilled($seriesinfo['series_id']);
$spiritAvg = SeriesRankingForSpirit($spiritAvg, $seriesinfo['series_id']);
$html .= "<h2>". utf8entities($seriesinfo['name']) . "</h2>";
if ($seasoninfo['showspiritpoints']) {

	if ($spsort == "ranking") {
	  mergesort($spiritAvg, function ($a, $b) use ($spsort) {
		$va = $a[$spsort];
		$vb = $b[$spsort];
		return $va == $vb ? 0 : ($va == null ? 1 : ($vb = null ? -1 : ($a[$spsort] < $b[$spsort] ? -1 : 1)));
	  });
	} else if ($spsort == "teamname" || $spsort == "pool" || $spsort == "against" || $spsort == "seed") {
	  mergesort($spiritAvg, function ($a, $b) use ($spsort) {
		return $a[$spsort] == $b[$spsort] ? 0 : ($a[$spsort] < $b[$spsort] ? -1 : 1);
	  });
	} else {
	  mergesort($spiritAvg, function ($a, $b) use ($spsort) {
      if ($a[$spsort] == $b[$spsort]) {
          return $a['ranking'] < $b['ranking'] ? -1 : 1;
      }
      return $a[$spsort] > $b[$spsort] ? -1 : 1;
  });
	}

  $categories = SpiritCategories($seasoninfo['spiritmode']);
  $html .= "<a name='spiritboard'></a>";
  $html .= "<h2>" . _("Spirit points average per category") . "</h2>\n";

  $html .= "<table cellspacing='0' border='0' width='100%' id='multicoloured'>\n";
  //$html .= "<tr><th style='width:150px'>" . _("Team") . "</th>";
	if ($spsort == "teamname") {
	  $html .= "<th style='width:150px'>" . _("Team") . "</th>";
	} else {
	  $html .= "<th style='width:150px'><a class='thsort' href='" . $viewUrl . "&amp;Spsort=teamname'>" . _("Team") . "</a></th>";
	}

  //$html .= "<th>" . _("Games") . "</th>";
  foreach ($categories as $cat) {
    if ($cat['index'] > 0 && $cat['index'])
      $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Spsort=".$cat['category_id']."'>" . _($cat['index']) . "</th>";
	}
	if ($spsort == "spabs"){
		$html .= "<th class='center'>" . _("Tot.") . "</th>";
	} else {
		$html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Spsort=spabs'>" . _("Tot.") . "</a></th>";
	}
  $html .= "</tr>\n";

  foreach ($spiritAvg as $teamAvg) {
    $html .= "<td><a href='?view=teamcard&amp;team=" . $teamAvg['team_id'] . "'>" . utf8entities($teamAvg['teamname']) . "</a></td>";
    //$html .= "<td>" . $teamAvg['games'] . "</td>";
    foreach ($categories as $cat) {
      if ($cat['index'] > 0 && isset($teamAvg[$cat['category_id']])) {
          $html .= "<td class='center'>" . number_format($teamAvg[$cat['category_id']], 2) . "</td>";
      }
    }
    $html .= "<td class='center'>" . number_format($teamAvg['total'], 2) . "</td>";
    $html .= "</tr>\n";
  }
  $html .= "</table>";
  
  $html .= "<table cellspacing='0' border='0' width='100%'>\n<tr>\n";
  $html .= "<td style='width:150px'><b>Average of all games</b></td>";
  $catSum = 0;
  $teamCount = 0;
  foreach ($categories as $cat) {
	if ($cat['index'] > 0 && isset($teamAvg[$cat['category_id']])) {
		$catSum = 0;
		$teamCount = 0;
		foreach ($spiritAvg as $teamAvg) {
			$catSum += number_format($teamAvg[$cat['category_id']], 2);
			$teamCount++;
		}
		
		$html .= "<td class='center'><b>". number_format(SafeDivide($catSum, $teamCount),2) ."</b></td>";
	}
  }
  
  $catSum = 0;
  $teamCount = 0;
  foreach ($spiritAvg as $teamAvg) {
			$catSum += number_format($teamAvg['total'], 2);
			$teamCount++;
  }
  if ($catSum > 0)
  $html .= "<td class='center'><b>". number_format(SafeDivide($catSum, $teamCount),2) ."</b></td>";
  $html .= "</tr>\n";
  $html .= "</table>";
  
  //
  
  $html .= "<ul>";
  foreach ($categories as $cat) {
    if ($cat['index'] > 0)
      $html .= "<li>" . $cat['index'] . " " . $cat['text'] . "</li>";
  }
  $html .= "</ul>\n";
}


showPage($title, $html, false, $submenuseriesid);