<?php
include_once 'lib/season.functions.php';
include_once 'lib/series.functions.php';
include_once 'lib/pool.functions.php';
include_once 'lib/team.functions.php';

$LAYOUT_ID = SERIESTATUS;

$title = _("Statistics") . " ";
$viewUrl = "?view=seriesstatus";
$sort = "ranking";
$spsort = "total";
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

$teamstats = array();
$allteams = array();
$teams = SeriesTeams($seriesinfo['series_id']);
$spiritAvg = SeriesSpiritBoardOnlyFilled($seriesinfo['series_id']);
$spiritTtl = SeriesAllSpiritPointsOnlyFilled($seriesinfo['series_id']);

foreach ($teams as $team) {
  $stats = TeamStats($team['team_id']);
  $points = TeamPoints($team['team_id']);

  $teamstats['name'] = $team['name'];
  $teamstats['team_id'] = $team['team_id'];
  $teamstats['seed'] = $team['rank'];
  $teamstats['flagfile'] = $team['flagfile'];

  $teamstats['wins'] = $stats['wins'];
  $teamstats['games'] = $stats['games'];

  $teamstats['for'] = $points['scores'];
  $teamstats['against'] = $points['against'];

  $teamstats['losses'] = $teamstats['games'] - $teamstats['wins'];
  $teamstats['diff'] = $teamstats['for'] - $teamstats['against'];

  $teamstats['spirit'] = isset($spiritAvg[$team['team_id']]) ? $spiritAvg[$team['team_id']]['total'] : null;

  $teamstats['winavg'] = number_format(SafeDivide(intval($stats['wins']), intval($stats['games'])) * 100, 1);

  $teamstats['ranking'] = 0;
  $teamstats['spiritpoints'] = isset($spiritTtl[$team['team_id']]) ? $spiritTtl[$team['team_id']]['total_value'] : null;
  $allteams[] = $teamstats;
}

$rankedteams  = SeriesRanking($seriesinfo['series_id']);
$rank = 0;
foreach ($rankedteams as $rteam) {
  $rank++;
  foreach ($allteams as &$ateam) {
    if (isset($rteam['team_id']) && $ateam['team_id'] == $rteam['team_id']){
      $ateam['ranking'] = $rank;
    }
  }
}

unset($ateam);

$html .= CommentHTML(2, $seriesinfo['series_id']);

//$html .= "<h2>" . _("Division statistics:") . " " . utf8entities($seriesinfo['name']) . "</h2>";
$html .= "<h2>". utf8entities($seriesinfo['name']) . "</h2>";
$style = "";

$html .= "<table border='1' style='width:100%'>\n";
$html .= "<tr>";

if ($sort == "ranking") {
  mergesort($allteams, function ($a, $b) use ($sort) {
    $va = $a[$sort];
    $vb = $b[$sort];
    return $va == $vb ? 0 : ($va == null ? 1 : ($vb = null ? -1 : ($a[$sort] < $b[$sort] ? -1 : 1)));
  });
} else if ($sort == "name" || $sort == "pool" || $sort == "against" || $sort == "seed") {
  mergesort($allteams, function ($a, $b) use ($sort) {
    return $a[$sort] == $b[$sort] ? 0 : ($a[$sort] < $b[$sort] ? -1 : 1);
  });
} else {
  mergesort($allteams, function ($a, $b) use ($sort) {
    return $a[$sort] == $b[$sort] ? 0 : ($a[$sort] > $b[$sort] ? -1 : 1);
  });
}

if ($sort == "name") {
  $html .= "<th style='width:180px'>" . _("Team") . "</th>";
} else {
  $html .= "<th style='width:180px'><a class='thsort' href='" . $viewUrl . "&amp;Sort=name'>" . _("Team") . "</a></th>";
}

/*
 if($sort == "pool") {
 $html .= "<th style='width:200px'>"._("Pool")."</th>";
 }else{
 $html .= "<th style='width:200px'><a href='".$viewUrl."&amp;Sort=pool'>"._("Pool")."</a></th>";
 }
 */

if ($sort == "seed") {
  $html .= "<th class='center'>" . _("Seeding") . "</th>";
} else {
  $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=seed'>" . _("Seeding") . "</a></th>";
}

if ($sort == "ranking") {
  $html .= "<th class='center'>" . _("Ranking") . "</th>";
} else {
  $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=ranking'>" . _("Ranking") . "</a></th>";
}

if ($sort == "games") {
  $html .= "<th class='center'>" . _("Games") . "</th>";
} else {
  $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=games'>" . _("Games") . "</a></th>";
}

if ($sort == "wins") {
  $html .= "<th class='center'>" . _("Wins") . "</th>";
} else {
  $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=wins'>" . _("Wins") . "</a></th>";
}

if ($sort == "losses") {
  $html .= "<th class='center'>" . _("Losses") . "</th>";
} else {
  $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=losses'>" . _("Losses") . "</a></th>";
}

if ($sort == "for") {
  $html .= "<th class='center'>" . _("Goals for") . "</th>";
} else {
  $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=for'>" . _("Goals for") . "</a></th>";
}

if ($sort == "against") {
  $html .= "<th class='center'>" . _("Goals against") . "</th>";
} else {
  $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=against'>" . _("Goals against") . "</a></th>";
}

if ($sort == "diff") {
  $html .= "<th class='center'>" . _("Goals diff") . "</th>";
} else {
  $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=diff'>" . _("Goals diff") . "</a></th>";
}

if ($sort == "winavg") {
  $html .= "<th class='center'>" . _("Win-%") . "</th>";
} else {
  $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=winavg'>" . _("Win-%") . "</a></th>";
}

if ($sort == "spiritpoints") {
  $html .= "<th class='center'>" . _("Spirit points") . "</th>";
} else {
  $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=spiritpoints'>" . _("Spirit points") . "</a></th>";
}

if ((isset($seasoninfo['spiritmode']) && $seasoninfo['spiritmode'] > 0) && ($seasoninfo['showspiritpoints'] || isSeasonAdmin($seriesinfo['season']))) {
  if ($sort == "spirit") {
    $html .= "<th class='center'>" . _("Spirit/ Game") . "</th>";
  } else {
    $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Sort=spirit'>" . _("Spirit/ Game") . "</a></th>";
  }
}


$html .= "</tr>\n";

foreach ($allteams as $stats) {
  $html .= "<tr>";
  $flag = "";
  if (intval($seasoninfo['isinternational'])) {
    $flag = "<img height='10' src='images/flags/tiny/" . $stats['flagfile'] . "' alt=''/> ";
  }
  if ($sort == "name") {
    $html .= "<td class='highlight'>$flag<a href='?view=teamcard&amp;team=" . $stats['team_id'] . "'>" . utf8entities(U_($stats['name'])) . "</a></td>";
  } else {
    $html .= "<td>$flag<a href='?view=teamcard&amp;team=" . $stats['team_id'] . "'>" . utf8entities(U_($stats['name'])) . "</a></td>";
  }
  /*
   if($sort == "pool") {
   $html .= "<td class='highlight'>",utf8entities(U_($stats['pool'])),"</td>";
   }else{
   $html .= "<td>",utf8entities(U_($stats['pool'])),"</td>";
   }
   */
  if ($sort == "seed") {
    $html .= "<td class='center highlight'>" . intval($stats['seed']) . ".</td>";
  } else {
    $html .= "<td class='center'>" . intval($stats['seed']) . ".</td>";
  } {
    $rank = $stats['ranking'];
    if ($rank == null)
      $rank = "-";
    else
      $rank = intval($rank);
    if ($sort == "ranking") {
      $html .= "<td class='center highlight'>" . $rank . "</td>";
    } else {
      $html .= "<td class='center'>" . $rank . "</td>";
    }
  }

  if ($sort == "games") {
    $html .= "<td class='center highlight'>" . intval($stats['games']) . "</td>";
  } else {
    $html .= "<td class='center'>" . intval($stats['games']) . "</td>";
  }
  if ($sort == "wins") {
    $html .= "<td class='center highlight'>" . intval($stats['wins']) . "</td>";
  } else {
    $html .= "<td class='center'>" . intval($stats['wins']) . "</td>";
  }
  if ($sort == "losses") {
    $html .= "<td class='center highlight'>" . intval($stats['losses']) . "</td>";
  } else {
    $html .= "<td class='center'>" . intval($stats['losses']) . "</td>";
  }
  if ($sort == "for") {
    $html .= "<td class='center highlight'>" . intval($stats['for']) . "</td>";
  } else {
    $html .= "<td class='center'>" . intval($stats['for']) . "</td>";
  }
  if ($sort == "against") {
    $html .= "<td class='center highlight'>" . intval($stats['against']) . "</td>";
  } else {
    $html .= "<td class='center'>" . intval($stats['against']) . "</td>";
  }
  if ($sort == "diff") {
    $html .= "<td class='center highlight'>" . intval($stats['diff']) . "</td>";
  } else {
    $html .= "<td class='center'>" . intval($stats['diff']) . "</td>";
  }

  if ($sort == "winavg") {
    $html .= "<td class='center highlight'>" . $stats['winavg'] . "%</td>";
  } else {
    $html .= "<td class='center'>" . $stats['winavg'] . "%</td>";
  }
  
	if ($sort == "spiritpoints") {
	  $html .= "<td class='center highlight'>" . $stats['spiritpoints'] . "</td>";
	} else {
	  $html .= "<td class='center'>" . $stats['spiritpoints'] . "</td>";
	}

  if ((isset($seasoninfo['spiritmode']) && $seasoninfo['spiritmode'] > 0) && ($seasoninfo['showspiritpoints'] || isSeasonAdmin($seriesinfo['season']))) {
    if ($sort == "spirit") {
      $html .= "<td class='center highlight'>" . ($stats['spirit'] ? number_format($stats['spirit'], 2) : "-") . "</td>";
    } else {
      $html .= "<td class='center'>" . ($stats['spirit'] ? number_format($stats['spirit'], 2) : "-") . "</td>";
    }
  }


  $html .= "</tr>\n";
}
$html .= "</table>\n";
$html .= "<a href='?view=poolstatus&amp;series=" . $seriesinfo['series_id'] . "'>" . _("Show all pools") . "</a>";

if($seriesinfo["stats"] == 1){
  $html .= "<h2>" . _("Scoreboard leaders") . "</h2>\n";
  $html .= "<table cellspacing='0' border='0' width='100%' id='multicoloured'>\n";
  $html .= "<tr><th style='width:200px'>" . _("Player") . "</th><th style='width:200px'>" . _("Team") . "</th><th class='center'>" . _("Games") . "</th>
  <th class='center'>" . _("Assists") . "</th><th class='center'>" . _("Goals") . "</th><th class='center'>" . _("Tot.") . "</th><th class='center'>" . _("Avg.") . "</th></tr>\n";


  $scores = SeriesScoreBoardAvg($seriesinfo['series_id'], "total", 10);
  while ($row = mysqli_fetch_assoc($scores)) {
    $html .= "<tr><td>" . utf8entities($row['firstname'] . " " . $row['lastname']) . "</td>";
    $html .= "<td>" . utf8entities($row['teamname']) . "</td>";
    $html .= "<td class='center'>" . intval($row['games']) . "</td>";
    $html .= "<td class='center'>" . intval($row['fedin']) . "</td>";
    $html .= "<td class='center'>" . intval($row['done']) . "</td>";
    $html .= "<td class='center'>" . intval($row['total']) . "</td>";
    $html .= "<td class='center'>" . $row['avg'] . "</td></tr>\n";
  }

  $html .= "</table>";
  $html .= "<a href='?view=scorestatus&amp;series=" . $seriesinfo['series_id'] . "'>" . _("Scoreboard") . "</a>";
}

if (ShowDefenseStats()) {
  $html .= "<h2>" . _("Defenseboard leaders") . "</h2>\n";
  $html .= "<table cellspacing='0' border='0' width='100%'>\n";
  $html .= "<tr><th style='width:200px'>" . _("Player") . "</th><th style='width:200px'>" . _("Team") . "</th><th class='center'>" . _("Games") . "</th>
	<th class='center'>" . _("Total defenses") . "</th></tr>\n";


  $defenses = SeriesDefenseBoard($seriesinfo['series_id'], "deftotal", 10);
  while ($row = mysqli_fetch_assoc($defenses)) {
    $html .= "<tr><td>" . utf8entities($row['firstname'] . " " . $row['lastname']) . "</td>";
    $html .= "<td>" . utf8entities($row['teamname']) . "</td>";
    $html .= "<td>" . _("Games") . "</td>";
    $html .= "<td class='center'>" . intval($row['games']) . "</td>";
    $html .= "<td class='center'>" . intval($row['deftotal']) . "</td></tr>\n";
  }

  $html .= "</table>";
  $html .= "<a href='?view=defensestatus&amp;series=" . $seriesinfo['series_id'] . "'>" . _("Defenseboard") . "</a>";
}

if ($seasoninfo['showspiritpoints']) { // TODO total

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
		return $a[$spsort] == $b[$spsort] ? 0 : ($a[$spsort] > $b[$spsort] ? -1 : 1);
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
	  $html .= "<th style='width:150px'><a class='thsort' href='" . $viewUrl . "&amp;Spsort=teamname#spiritboard'>" . _("Team") . "</a></th>";
	}

  //$html .= "<th>" . _("Games") . "</th>";
  foreach ($categories as $cat) {
    if ($cat['index'] > 0 && $cat['index'])
    $html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Spsort=".$cat['category_id']."#spiritboard'>" . _($cat['index']) . "</th>";
	}
	if ($spsort == "total"){
		$html .= "<th class='center'>" . _("Tot.") . "</th>";
	} else {
		$html .= "<th class='center'><a class='thsort' href='" . $viewUrl . "&amp;Spsort=total#spiritboard'>" . _("Tot.") . "</a></th>";
	}
  $html .= "</tr>\n";

  foreach ($spiritAvg as $teamAvg) {
    $html .= "<td>" . utf8entities($teamAvg['teamname']) . "</td>";
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