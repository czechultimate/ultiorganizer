<?php
include_once 'lib/pool.functions.php';
include_once 'lib/game.functions.php';
include_once 'lib/common.functions.php';

$html = "";

$gameId = intval(iget("game"));

$game_result = GameResult($gameId);
$seasoninfo = SeasonInfo(GameSeason($gameId));
$homecaptain = GameCaptain($gameId, $game_result['hometeam']);
$awaycaptain = GameCaptain($gameId, $game_result['visitorteam']);

$title = _("Game play") . ": " . utf8entities($game_result['hometeamname']) . " vs. " . utf8entities($game_result['visitorteamname']);

$home_team_score_board = GameTeamScoreBorad($gameId, $game_result['hometeam']);
$guest_team_score_board = GameTeamScoreBorad($gameId, $game_result['visitorteam']);

$poolinfo = PoolInfo($game_result['pool']);

$goals = GameGoals($gameId);
$gameevents = GameEvents($gameId);
$mediaevents = GameMediaEvents($gameId);

if (GameHasStarted($game_result) > 0) {
  $html .= "<h1>" . utf8entities($game_result['hometeamname']);
  $html .= " - ";
  $html .= utf8entities($game_result['visitorteamname']);
  $html .= "&nbsp;&nbsp;&nbsp;&nbsp;";
  $html .= intval($game_result['homescore']);
  $html .= " - ";
  $html .= intval($game_result['visitorscore']);
  if (intval($game_result['isongoing'])) {
    $html .= " (" . _("ongoing") . ")";
  }
  $html .= "</h1>\n";

  if (1==0){//mysqli_num_rows($goals) <= 0) {
    $html .= "<h2>" . _("Not fed in") . "</h2>
			  <p>" . _("Please check the status again later") . "</p>";
  } else {

    //score board
  
    $html .= "<table style='width:100%'><tr><td valign='top' style='width:45%'>\n";

    $html .= "<table width='100%' cellspacing='0' cellpadding='0' border='0'>\n";
    $html .= "<tr style='height=20'><td align='center'><b>";
    $html .= "<a href=?view=teamcard&amp;team=" . $game_result['hometeam'] . ">" . utf8entities($game_result['hometeamname']) . "</a></b></td></tr>\n";
    $html .= "</table><table width='100%' cellspacing='0' cellpadding='3' border='0'>";
    $html .= "<tr><th class='home'>#</th><th class='home'>" . _("Name");

    if($poolinfo["stats"] == 1){
      $html .= "</th><th class='home center'>" . _("Assists") . "</th><th class='home center'>" . _("Goals") . "</th>
      <th class='home center'>" . _("Tot.") . "</th></tr>\n";
    }

    while ($row = mysqli_fetch_assoc($home_team_score_board)) {
      $html .= "<tr>";
      $html .= "<td style='text-align:left'>" . $row['num'] . "</td>";
      $html .= "<td><a href='?view=playercard&amp;series=0&amp;player=" . $row['player_id'];
      $html .= "'>" . utf8entities($row['firstname']) . "&nbsp;";
      $html .= utf8entities($row['lastname']) . "</a>";
      if ($row['player_id'] == $homecaptain) {
        $html .= "&nbsp;" . _("(C)");
      }
      $html .= "</td>";
      if($poolinfo["stats"] == 1){
        $html .= "<td class='center'>" . $row['fedin'] . "</td>";
        $html .= "<td class='center'>" . $row['done'] . "</td>";
        $html .= "<td class='center'>" . $row['total'] . "</td>";
      }
      $html .= "</tr>";
    }


    $html .= "</table></td>\n<td style='width:10%'>&nbsp;</td><td valign='top' style='width:45%'>";

    $html .= "<table width='100%' cellspacing='0' cellpadding='0' border='0'>";
    $html .= "<tr><td><b>";
    $html .= "<a href=?view=teamcard&amp;team=" . $game_result['visitorteam'] . ">" . utf8entities($game_result['visitorteamname']) . "</a></b></td></tr>\n";
    $html .= "</table><table width='100%' cellspacing='0' cellpadding='3' border='0'>";
    $html .= "<tr><th class='guest'>#</th><th class='guest'>" . _("Name");
    if($poolinfo["stats"] == 1){
      $html .= "</th><th class='guest center'>" . _("Assists") . "</th><th class='guest center'>" . _("Goals");
      $html .= "</th><th class='guest center'>" . _("Tot.") . "</th></tr>\n";
    }

    while ($row = mysqli_fetch_assoc($guest_team_score_board)) {
      $html .= "<tr>";
      $html .= "<td style='text-align:left'>" . $row['num'] . "</td>";
      $html .= "<td><a href='?view=playercard&amp;series=0&amp;player=" . $row['player_id'];
      $html .= "'>" . utf8entities($row['firstname']) . "&nbsp;";
      $html .= utf8entities($row['lastname']) . "</a>";
      if ($row['player_id'] == $awaycaptain) {
        $html .= "&nbsp;" . _("(C)");
      }
      $html .= "</td>";

      if($poolinfo["stats"] == 1){
        $html .= "<td class='center'>" . $row['fedin'] . "</td>";
        $html .= "<td class='center'>" . $row['done'] . "</td>";
        $html .= "<td class='center'>" . $row['total'] . "</td>";
      }
      $html .= "</tr>";
    }

    $html .= "</table></td></tr></table>\n";

    //timeline
    //$points[50][7];
    $points = array(array());
    $i = 0;
    $lprev = 0;
    $htAt = intval($poolinfo['winningscore']);
    $htAt = intval(($htAt / 2) + 0.5);
    $bHt = false;
    $total = 0;

    while ($goal = mysqli_fetch_assoc($goals)) {

      if (!$bHt && $goal['time'] > $game_result['halftime']) {
        $points[$i][0] = (intval($game_result['halftime']) - $lprev);
        $points[$i][4] = intval($game_result['halftime']);
        $lprev = intval($game_result['halftime']);
        $points[$i][1] = -2;
        $total += $points[$i][0];
        $bHt = 1;
        $i++;
      }

      if (intval($goal['time']) > 0) {
        $ptLen = intval($goal['time']) - $lprev;
      } else {
        $ptLen = 1;
      }

      $points[$i][0] = $ptLen;
      $points[$i][1] = intval($goal['ishomegoal']);
      $points[$i][2] = utf8entities($goal['scorerlastname'] . " " . $goal['scorerfirstname']);
      $points[$i][3] = utf8entities($goal['assistlastname'] . " " . $goal['assistfirstname']);
      $points[$i][4] = intval($goal['time']);
      $points[$i][5] = $goal['homescore'];
      $points[$i][6] = $goal['visitorscore'];

      $lprev = intval($goal['time']);
      $total += $points[$i][0];


      $i++;
    }

    $html .= "<table border='1' style='height: 15px; color: white; border-width: 1; border-color: white; width: 100%;'><tr>\n";

    $maxlength = 600;
    $latestHomeGoalTime = 0;
    $latestGuestGoalTime = 0;
    $offset = $maxlength / $total;
    for ($i = 0; $i < 50 && !empty($points[$i][0]); $i++) {
      if ($points[$i][1] == 1) {
        $color = "home";
        $latestHomeGoalTime = $points[$i][4];
      } elseif ($points[$i][1] == -2) {
        $color = "halftime";
      } else {
        $color = "guest";
        $latestGuestGoalTime = $points[$i][4];
      }

      $timeSinceLastGuestGoal = $points[$i][4] - $latestGuestGoalTime;
      $timeSinceLastHomeGoal = $points[$i][4] - $latestHomeGoalTime;

      $width_a = $points[$i][0] * $offset;

      if ($points[$i][1] == -2) {
        $title = SecToMin($points[$i][4]) . " halftime";
      } else {
        $title = SecToMin($points[$i][4]) . " " . $points[$i][5] . "-" . $points[$i][6] . " " . $points[$i][3] . " -> " . $points[$i][2];
      }
      $html .= "<td style='width:" . $width_a . "px' class='$color' title='$title'></td>\n";
    }
    $html .= "</tr></table>\n";
    
    //zacatek asi tady
    if($poolinfo["stats"] == 1){
	$html .= "<table border='1' cellpadding='2' width='100%' id='matchstats'>\n";
    $html .= "<tr><th>" . _("Scores") . "</th><th>" . _("Assist") . "</th><th>" . _("Goal") . "</th><th>" . _("Time") . "</th><th>" . _("Dur.") . "</th>";
   // if (count($gameevents) || count($mediaevents)) {
    $html .= "<th>" . _("Game events ") . "</th>";
   // }
    $html .= "</tr>\n";

    $bHt = false;

    $prevgoal = 0;
    mysqli_data_seek($goals, 0);
    while ($goal = mysqli_fetch_assoc($goals)) {
      if (!$bHt && $game_result['halftime'] > 0 && $goal['time'] > $game_result['halftime']) {
        $html .= "<tr><td colspan='6' class='halftime'>" . _("Half-time") . "</td></tr>";
        $bHt = 1;
        $prevgoal = intval($game_result['halftime']);
      }

      $html .= "<tr><td style='width:45px;white-space: nowrap'";
      if (intval($goal['ishomegoal']) == 1) {
        $html .= " class='home'>";
      } else {
        $html .= " class='guest'>";
      }
      $html .= $goal['homescore'] . " - " . $goal['visitorscore'] . "</td>";

      if (intval($goal['iscallahan'])) {
        $html .= "<td class='callahan'>" . _("Callahan-goal") . "&nbsp;</td>";
      } else {
        $html .= "<td>" . utf8entities($goal['assistfirstname']) . " " . utf8entities($goal['assistlastname']) . "&nbsp;</td>";
      }
      $html .= "<td>" . utf8entities($goal['scorerfirstname']) . " " . utf8entities($goal['scorerlastname']) . "&nbsp;</td>";
      $html .= "<td>" . SecToMin($goal['time']) . "</td>";
      $duration = $goal['time'] - $prevgoal;

      $html .= "<td>" . SecToMin($duration) . "</td>";

      //if (count($gameevents) || count($mediaevents)) {
        $html .= "<td>";
        //gameevents
        foreach ($gameevents as $event) {
          if ((intval($event['time']) >= $prevgoal) &&
            (intval($event['time']) < intval($goal['time']))
          ) {
            if ($event['type'] == "timeout") {
              $gameevent = _("Time-out");
            } elseif ($event['type'] == "turnover") {
              $gameevent = _("Turnover");
            } elseif ($event['type'] == "offence") {
              $gameevent = _("Offence");
            }
            //hack to not show timeouts not correctly marked into scoresheet
            if ($event['type'] == "timeout" && ($event['time'] == 0 || $event['time'] == 60)) {
              continue;
            }

            if (intval($event['ishome']) > 0) {
              $html .= "<div class='home'>" . $gameevent . "&nbsp;" . SecToMin($event['time']) . "</div>";
            } else {
              $html .= "<div class='guest'>" . $gameevent . "&nbsp;" . SecToMin($event['time']) . "</div>";
            }
          }
        }
        //mediaevents
        $tmphtml = "";
        foreach ($mediaevents as $event) {
          if ((intval($event['time']) >= $prevgoal) &&
            (intval($event['time']) < intval($goal['time']))
          ) {
            $tmphtml .= "<a style='color: #ffffff;' href='" . $event['url'] . "'>";
            $tmphtml .= "<img width='12' height='12' src='images/linkicons/" . $event['type'] . ".png' alt='" . $event['type'] . "'/></a>";
          }
        }
        if (!empty($tmphtml)) {
          $html .= "<div class='mediaevent'>" . $tmphtml . "</div>\n";
        }
        $html .= "</td>";
     // }
      $html .= "</tr>";
      $prevgoal = intval($goal['time']);
    }
    if (intval($game_result['isongoing'])) {
      $html .= "<tr style='border-style:dashed;border-width:1px;'>";
      $html .= "<td>&nbsp;</td>";
      $html .= "<td>&nbsp;</td>";
      $html .= "<td>&nbsp;</td>";
      $html .= "<td>&nbsp;</td>";
      $html .= "<td>&nbsp;</td>";
      if (count($gameevents) || count($mediaevents)) {
        $html .= "<td>&nbsp;</td>";
      }
      $html .= "</tr>";
    }
    $html .= "</table>\n";

    if (!empty($game_result['official'])) {
      $html .= "<p>" . _("Game official") . ": " . utf8entities($game_result['official']) . "</p>";
    }

    $urls = GetMediaUrlList("game", $gameId);

    if (count($urls) > count($mediaevents)) {
      $html .= "<h2>" . _("Photos and Videos") . "</h2>\n";
      $html .= "<table>";
      foreach ($urls as $url) {
        //if time set those are shown as gameevent
        if (!empty($url['time'])) {
          continue;
        }

        $html .=  "<tr>";
        $html .=  "<td colspan='2'><img width='16' height='16' src='images/linkicons/" . $url['type'] . ".png' alt='" . $url['type'] . "'/> ";
        $html .=  "</td><td>";
        if (!empty($url['name'])) {
          $html .= "<a href='" . $url['url'] . "'>" . $url['name'] . "</a>";
        } else {
          $html .= "<a href='" . $url['url'] . "'>" . $url['url'] . "</a>";
        }
        if (!empty($url['mediaowner'])) {
          $html .= " " . _("from") . " " . $url['mediaowner'];
        }

        $html .= "</td>";
        $html .= "</tr>";
      }
      $html .= "</table>";
    }
    
    if (!intval($game_result['isongoing'])) {
      //statistics
      $html .= "<h2>" . _("Game statistics") . "</h2>\n";

      $allgoals = GameAllGoals($gameId);

      $bHOffence = 0;
      $nHOffencePoint = 0;
      $nVOffencePoint = 0;
      $nHBreaks = 0;
      $nVBreaks = 0;
      $nHTotalTime = 0;
      $nVTotalTime = 0;
      $nHGoals = 0;
      $nVGoals = 0;
      $nClockTime = 0;
      $nDuration = 0;
      $bHStartTheGame = 0;
      $nHTO = 0;
      $nVTO = 0;
      $nHLosesDisc = 0;
      $nVLosesDisc = 0;

      $turnovers = GameTurnovers($gameId);

      $goal = mysqli_fetch_assoc($allgoals);
      $turnover = mysqli_fetch_assoc($turnovers);

      //who start the game?
      $ishome = GameIsFirstOffenceHome($gameId);
      if ($ishome == 1) {
        $bHStartTheGame = true;
      } elseif ($ishome == 0) {
        $bHStartTheGame = false;
      } else {
        //make some wild guess
        if ($turnover) {
          //If turnover before goal
          if (intval($turnover['time']) < intval($goal['time'])) {
            //If home lose disc Then home was starting the game
            if (intval($turnover['ishome'])) {
              $bHStartTheGame = true;
              //visitor starts but loses the disc
            } else {
              $bHStartTheGame = false;
            }
            //no turnovers before goal, the team scored was starting the game
          } else {
            if (intval($goal['ishomegoal'])) {
              $bHStartTheGame = true;
            } else {
              $bHStartTheGame = false;
            }
          }
          //no turnovers in database
        } else {
          //team scored was starting (just wild guess)
          if (intval($goal['ishomegoal'])) {
            $bHStartTheGame = true;
          } else {
            $bHStartTheGame = false;
          }
        }
      }
      //whom start the game, starts offence
      
      $bHOffence = $bHStartTheGame;

      //return internal pointers to first row
      mysqli_data_seek($allgoals, 0);

      //loop all goals
      while ($goal = mysqli_fetch_assoc($allgoals)) {
        //halftime passed
        if (!is_null($game_result['halftime']) && ($nClockTime <= intval($game_result['halftime'])) && (intval($goal['time']) >= intval($game_result['halftime']))) {
          $nClockTime = intval($game_result['halftime']);

          if ($bHStartTheGame) {
            $bHOffence = false;
          } else {
            $bHOffence = true;
          }
        }

        //track offence turns
        if ($bHOffence) {
          $nHOffencePoint++;
        } else {
          $nVOffencePoint++;
        }
        //If turnovers before goal

        if (mysqli_num_rows($turnovers)) {
          $turnovers = GameTurnovers($gameId);
        }
        while ($turnover = mysqli_fetch_assoc($turnovers)) {
          if ((intval($turnover['time']) > $nClockTime) &&
            (intval($turnover['time']) < intval($goal['time']))
          ) {
            if (intval($turnover['ishome'])) {
              $nHLosesDisc++;
              //$nDuration = intval($turnover['time']) - $nClockTime;
              //$nClockTime = intval($turnover['time']);
              //$nHTotalTime += $nDuration;
            } else {
              $nVLosesDisc++;
              //$nDuration = intval($turnover['time']) - $nClockTime;
              //$nClockTime = intval($turnover['time']);
              //$nVTotalTime += $nDuration;
            }
          }
        }

        //If a break goal
        if (intval($goal['ishomegoal']) && $bHOffence == false) {
          $nHBreaks++;
        } elseif (intval($goal['ishomegoal']) == 0 && $bHOffence == true) {
          $nVBreaks++;
        }

        //point duration
        $nDuration = intval($goal['time']) - $nClockTime;
        $nClockTime = intval($goal['time']);

        if ($bHOffence) {
          $nHTotalTime += $nDuration;
        } else {
          $nVTotalTime += $nDuration;
        }
        //If home goal
        if (intval($goal['ishomegoal'])) {
          $nHGoals++;
          $bHOffence = false;
        } else {
          $nVGoals++;
          $bHOffence = true;
        }
      }

      //timeouts
      $timeouts = GameTimeouts($gameId);

      while ($timeout = mysqli_fetch_assoc($timeouts)) {
        if (intval($timeout['ishome'])) {
          $nHTO++;
        } else {
          $nVTO++;
        }
      }
      $dblHAvg = 0.0;
      $dblVAvg = 0.0;

      //Build HTML-table
      $html .= "<table style='width:80%' border='1' cellpadding='2' cellspacing='0'><tr><th></th><th style='width:25%'>" . utf8entities($game_result['hometeamname']) .
        "</th><th style='width:25%'>" . utf8entities($game_result['visitorteamname']) . "</th></tr>";

      $html .= "<tr><td>" . _("Goals") . ":</td> <td class='home'>$nHGoals</td> <td class='guest'>$nVGoals</td></tr>\n";

      $dblHAvg = SafeDivide($nHTotalTime, ($nHTotalTime + $nVTotalTime)) * 100;
      $dblVAvg = SafeDivide($nVTotalTime, ($nHTotalTime + $nVTotalTime)) * 100;

      $html .= "<tr><td>" . _("Time on offence") . ":</td>
			<td class='home'>" . SecToMin($nHTotalTime) . " min (" . number_format($dblHAvg, 1) . " %)</td>
			<td class='guest'>" . SecToMin($nVTotalTime) . " min (" . number_format($dblVAvg, 1) . " %)</td></tr>\n";

      $html .= "<tr><td>" . _("Time on defence") . ":</td>
			<td class='home'>" . SecToMin($nVTotalTime) . " min (" . number_format($dblVAvg, 1) . " %)</td>
			<td class='guest'>" . SecToMin($nHTotalTime) . " min (" . number_format($dblHAvg, 1) . " %)</td></tr>\n";

      $html .= "<tr><td>" . _("Time on offence") . "/" . _("goal") . ":</td>
			<td class='home'>" . SecToMin(SafeDivide($nHTotalTime, $nHGoals)) . " min</td>
			<td class='guest'>" . SecToMin(SafeDivide($nVTotalTime, $nVGoals)) . " min</td></tr>\n";

      $html .= "<tr><td>" . _("Time on defence") . "/" . _("goal") . ":</td>
			<td class='home'>" . SecToMin(SafeDivide($nVTotalTime, $nVGoals)) . " min</td>
			<td class='guest'>" . SecToMin(SafeDivide($nHTotalTime, $nHGoals)) . " min</td></tr>\n";

      $dblHAvg = SafeDivide(abs($nHGoals - $nHBreaks), $nHOffencePoint) * 100;
      $dblVAvg = SafeDivide(abs($nVGoals - $nVBreaks), $nVOffencePoint) * 100;

      $html .= "<tr><td>" . _("Goals from starting on offence") . ":</td>
			<td class='home'>" . abs($nHGoals - $nHBreaks) . "/" . $nHOffencePoint . " (" . number_format($dblHAvg, 1) . " %)</td>
			<td class='guest'>" . abs($nVGoals - $nVBreaks) . "/" . $nVOffencePoint . " (" . number_format($dblVAvg, 1) . " %)</td></tr>";

      $dblHAvg = SafeDivide($nHBreaks, $nVOffencePoint) * 100;
      $dblVAvg = SafeDivide($nVBreaks, $nHOffencePoint) * 100;

      $html .= "<tr><td>" . _("Goals from starting on defence") . ":</td>
			<td class='home'>" . $nHBreaks . "/" . $nVOffencePoint . " (" . number_format($dblHAvg, 1) . " %)</td>
			<td class='guest'>" . $nVBreaks . "/" . $nHOffencePoint . " (" . number_format($dblVAvg, 1) . " %)</td></tr>";

      if ($nHLosesDisc + $nVLosesDisc > 0) {
        $html .= "<tr><td>" . _("Turnovers") . ":</td>
				<td class='home'>" . $nHLosesDisc . "</td>
				<td class='guest'>" . $nVLosesDisc . "</td></tr>";
      }

      $html .= "<tr><td>" . _("Breaks") . ":</td>
			<td class='home'>" . $nHBreaks . "</td>
			<td class='guest'>" . $nVBreaks . "</td></tr>";

      $html .= "<tr><td>" . _("Time-outs") . ":</td>
			<td class='home'>" . $nHTO . "</td>
			<td class='guest'>" . $nVTO . "</td></tr>";

      if ((isset($seasoninfo['spiritmode']) && $seasoninfo['spiritmode'] > 0) && ($seasoninfo['showspiritpoints'] || isSeasonAdmin($seasoninfo['season_id']))) {
        $html .= "<tr><td>" . _("Spirit points") . ":</td>";
        if (isset($game_result['homesotg']) && !is_null($game_result['homesotg'])) {
          $html .= "<td class='home'>" . $game_result['homesotg'] . "</td>";
        } else {
          $html .= "<td class='home'>-</td>";
        }
        if (isset($game_result['visitorsotg']) && !is_null($game_result['visitorsotg'])) {
          $html .= "<td class='guest'>" . $game_result['visitorsotg'] . "</td></tr>";
        } else {
          $html .= "<td class='guest'>-</td></tr>";
        }
      }
      $html .= "</table>";
    }
  }
  }
 // konec asi tady
    // spirit points
    if ((isset($seasoninfo['spiritmode']) && $seasoninfo['spiritmode'] > 0) && !intval($game_result['isongoing'])) {
      $html .= "<h2>" . _("Spirit Points") . "</h2>\n";
      $categories = SpiritCategories($seasoninfo['spiritmode']);
      $homepoints = GameGetSpiritPoints($gameId, $game_result['hometeam']);
      $visitorpoints = GameGetSpiritPoints($gameId, $game_result['visitorteam']);
      $html .= "<table style='width:80%' border='1' cellpadding='2' cellspacing='0'><tr><th></th><th style='width:25%'>" . utf8entities($game_result['hometeamname']) .
        "</th><th  style='width:25%'>" . utf8entities($game_result['visitorteamname']) . "</th></tr>";
      foreach ($categories as $cat) {
        if ($cat['index'] == 0)
          continue;
        $id = $cat['category_id'];
        $html .= "<tr><td>";
        $html .= _($cat['text']);
        $html .= ":</td>";
        $html .= "<td class='home'>";
        if (isset($homepoints[$id]) && !is_null($homepoints[$id]) && isset($visitorpoints[$id]) && !is_null($visitorpoints[$id])) {
          if (is_numeric($homepoints[$id])){
            $html .= intval($homepoints[$id]);
          } else {
            $html .= $homepoints[$id];
          }
        } else {
          $html .= "-";
        }

        $html .= "</td>";
        $html .= "<td class='guest'>";
        if (isset($homepoints[$id]) && !is_null($homepoints[$id]) && isset($visitorpoints[$id]) && !is_null($visitorpoints[$id])) {
          if (is_numeric($visitorpoints[$id])){
          $html .= intval($visitorpoints[$id]);
          } else {
          $html .= $visitorpoints[$id];
          }
          
        } else {
          $html .= "-";
        }
        $html .= "</td></tr>";
      }
      $html .= "</table>";
    }
    $html .= "<p><a href='?view=gamecard&amp;team1=" . utf8entities($game_result['hometeam']) . "&amp;team2=" . utf8entities($game_result['visitorteam']) . "'>";
    $html .=  _("Game history") . "</a></p>\n";
    if ($_SESSION['uid'] != 'anonymous') {
      $html .= "<div style='float:left;'><hr/><a href='?view=user/addmedialink&amp;game=$gameId'>" . _("Add media") . "</a></div>";
    }

    //}
    //defense board
    if (ShowDefenseStats()) {
      $html .= "<br><br>";
      $html .= "<h3>" . _("Defensive plays") . "</h3>\n";
      $home_team_defense_board = GameTeamDefenseBoard($gameId,  $game_result['hometeam']);
      $guest_team_defense_board = GameTeamDefenseBoard($gameId,  $game_result['visitorteam']);
      $defenses = GameDefenses($gameId);
      $html .= "<table style='width:100%'><tr><td valign='top' style='width:45%'>\n";

      $html .= "<table width='100%' cellspacing='0' cellpadding='0' border='0'>\n";
      $html .= "<tr style='height=20'><td align='center'><b>";
      $html .= utf8entities($game_result['hometeamname']) . "</b></td></tr>\n";
      $html .= "</table><table width='100%' cellspacing='0' cellpadding='3' border='0'>";
      $html .= "<tr><th class='home'>#</th><th class='home'>" . _("Name") . "</th><th class='home center'>" . _("Defenses") . "</th></tr>\n";

      while ($row = mysqli_fetch_assoc($home_team_defense_board)) {
        $html .= "<tr>";
        $html .= "<td style='text-align:right'>" . $row['num'] . "</td>";
        $html .= "<td><a href='?view=playercard&amp;series=0&amp;player=" . $row['player_id'];
        $html .= "'>" . utf8entities($row['firstname']) . "&nbsp;";
        $html .= utf8entities($row['lastname']) . "</a>";
        if ($row['player_id'] == $homecaptain) {
          $html .= "&nbsp;" . _("(C)");
        }
        $html .= "</td>";
        //$html .= "<td class='center'>". $row['fedin'] ."</td>";
        $html .= "<td class='center'>" . $row['done'] . "</td>";
        //$html .="<td class='center'>". $row['total'] ."</td>";
        $html .= "</tr>";
      }

      $html .= "</table></td>\n<td style='width:10%'>&nbsp;</td><td valign='top' style='width:45%'>";

      $html .= "<table width='100%' cellspacing='0' cellpadding='0' border='0'>";
      $html .= "<tr><td><b>";
      $html .= utf8entities($game_result['visitorteamname']) . "</b></td></tr>\n";
      $html .= "</table><table width='100%' cellspacing='0' cellpadding='3' border='0'>";
      $html .= "<tr><th class='guest'>#</th><th class='guest'>" . _("Name") . "</th><th class='guest center'>";
      $html .= _("Defenses") . "</th></tr>\n";

      while ($row = mysqli_fetch_assoc($guest_team_defense_board)) {
        $html .= "<tr>";
        $html .= "<td style='text-align:right'>" . $row['num'] . "</td>";
        $html .= "<td><a href='?view=playercard&amp;series=0&amp;player=" . $row['player_id'];
        $html .= "'>" . utf8entities($row['firstname']) . "&nbsp;";
        $html .= utf8entities($row['lastname']) . "</a>";
        if ($row['player_id'] == $awaycaptain) {
          $html .= "&nbsp;" . _("(C)");
        }
        $html .= "</td>";
        //$html .= "<td class='center'>". $row['fedin'] ."</td>";
        $html .= "<td class='center'>" . $row['done'] . "</td>";
        //$html .="<td class='center'>". $row['total'] ."</td>";
        $html .= "</tr>";
      }

      $html .= "</table></td></tr></table>\n";

      $html .= "<table border='1' cellpadding='2' width='100%'>\n";
      $html .= "<tr><th>" . _("Time defense play") . "</th><th>" . _("Player") . "</th><th>" . _("Callahan defense") . "</th>";
      $html .= "</tr>\n";

      //$bHt=false;

      $prevdefense = 0;
      mysqli_data_seek($defenses, 0);
      while ($defense = mysqli_fetch_assoc($defenses)) {
        // 		if (!$bHt && $game_result['halftime']>0 && $goal['time'] > $game_result['halftime']){
        // 			$html .= "<tr><td colspan='6' class='halftime'>"._("Half-time")."</td></tr>";
        // 			$bHt = 1;
        // 			$prevgoal = intval($game_result['halftime']);
        // 		}

        $html .= "<tr><td style='width:120px;white-space: nowrap'";
        if (intval($defense['ishomedefense']) == 1) {
          $html .= " class='home'>";
        } else {
          $html .= " class='guest'>";
        }
        $html .= SecToMin($defense['time']) . "</td>";
        //$html .= $goal['homescore'] ." - ". $goal['visitorscore'] ."</td>";
        $html .= "<td>" . utf8entities($defense['defenderfirstname']) . " " . utf8entities($defense['defenderlastname']) . "&nbsp;</td>";

        if (intval($defense['iscallahan'])) {
          $html .= "<td style='width:100px' class='callahan'>&nbsp;</td>";
        } else {
          $html .= "<td style='width:100px'>&nbsp;</td>";
        }
        //$html .= "<td>". SecToMin($defense['time']) ."</td>";

        $html .= "</tr>";
      }
      $html .= "</table>\n";
    }
  
} else {
  $game_result = GameInfo($gameId);

  if ($game_result['hometeam'] && $game_result['visitorteam']) {
    $html .= "<h1>";
    $html .= utf8entities($game_result['hometeamname']);
    $html .= " - ";
    $html .= utf8entities($game_result['visitorteamname']);
    $html .= "&nbsp;&nbsp;&nbsp;&nbsp;";
    $html .= "? - ?";
    $html .= "</h1>\n";
  } else {
    $html .= "<h1>";
    $html .= utf8entities(U_($game_result['gamename']));
    $html .= "</h1>\n";
    $html .= "<h2>";
    $html .= utf8entities(U_($game_result['phometeamname']));
    $html .= " - ";
    $html .= utf8entities(U_($game_result['pvisitorteamname']));
    $html .= "&nbsp;&nbsp;&nbsp;&nbsp;";
    $html .= "? - ?";
    $html .= "</h2>\n";
  }

  $html .= "<p>";
  $html .= ShortDate($game_result['time']) . " " . DefHourFormat($game_result['time']) . " ";
  if (!empty($game_result['fieldname'])) {
    $html .= _("on field") . " " . utf8entities($game_result['fieldname']);
  }
  $html .= "</p>";
}
showPage($title, $html);

?>

<script>
  const gameId = <?php echo $gameId; ?>;
  const ongoing = <?php echo $game_result['isongoing']; ?>;
if(ongoing == 1){
  const eventSource = new EventSource(`sse.php?game=${gameId}`);

  eventSource.onmessage = function(event) {
      const data = JSON.parse(event.data);
      if (data.event_type === "init") {
          console.log('Received message:', event.data);
      } else if (data.event_type === "goal") {
        addNewRow(data);
        console.log('Received message:', event.data);
      } /* else if (data.event_type === "timeout") {
        addTimeOut(data);
        console.log('Received message:', event.data);
      } */ else if (data.event_type === "halftime"){
        addHalftime(data);
        console.log('Received message:', event.data);
      } else if (data.event_type === "close"){
        eventSource.close();
        console.log('Received message:', event.data);
      } else {
        console.log('Received message:', event.data);
      }
  };
}

function addNewRow(data) {
        const num = data.num;
        const time = data.time;
        const ishomegoal = data.ishomegoal;
        const homescore = data.homescore;
        const iscallahan = data.iscallahan;
        const visitorscore = data.visitorscore;
        const assistFirstName = data.assistfirstname;
        const assistLastName = data.assistlastname;
        const scorerFirstName = data.scorerfirstname;
        const scorerLastName = data.scorerlastname;

        var table = document.getElementById("matchstats").getElementsByTagName('tbody')[0];
        var newRow = table.insertRow(table.rows.length - 1);

        if(table.rows[table.rows.length - 3].cells[0].classList.contains("halftime")){
          var prePreLastRow = table.rows[table.rows.length - 4];
        }else {
          var prePreLastRow = table.rows[table.rows.length - 3];
        }

        var prevTime = prePreLastRow.cells[3].innerHTML;

        var cell1 = newRow.insertCell(0);
        var scoreClass = (ishomegoal == 1) ? "home" : "guest";
        cell1.innerHTML = homescore + " - " + visitorscore;
        cell1.setAttribute("style", "width:45px;white-space: nowrap");
        cell1.setAttribute("class", scoreClass);

        var cell2 = newRow.insertCell(1);
        if(iscallahan == 1){
        cell2.innerHTML = "Callahan-goal";
        cell2.setAttribute("class", "callahan");
        } else {
        cell2.innerHTML = assistFirstName + " " + assistLastName ;
        }

        var cell3 = newRow.insertCell(2);
        cell3.innerHTML = scorerFirstName + " " + scorerLastName ;

        var cell4 = newRow.insertCell(3);
        cell4.innerHTML = secToMin(time);


        var cell5 = newRow.insertCell(4);
        if(num == 0){
          cell5.innerHTML = secToMin(time);
        }else {
          cell5.innerHTML = secToMin(time -  minToSec(prevTime));
        }

        var cell6 = newRow.insertCell(5);
    }

function addTimeOut(data){

        var table = document.getElementById("matchstats").getElementsByTagName('tbody')[0];

          var newRow = table.insertRow(table.rows.length - 1);
          var cell1 = newRow.insertCell(0);
          var cell2 = newRow.insertCell(1);
          var cell3 = newRow.insertCell(2);
          var cell4 = newRow.insertCell(3);
          var cell5 = newRow.insertCell(4);
          var cell6 = newRow.insertCell(5);

        var timeoutClass = (data.timeout_ishome == 1) ? "home" : "guest";

          var divElement = document.createElement("div");

          divElement.setAttribute("class", timeoutClass);

          divElement.innerHTML = "Time-out " + secToMin(data.time);

          cell6.appendChild(divElement);
    }

    function addHalftime(data){
      var table = document.getElementById("matchstats").getElementsByTagName('tbody')[0];
        var newRow = table.insertRow(table.rows.length - 1);

        var cell1 = newRow.insertCell(0);
          cell1.innerHTML = "Half-time";
          cell1.setAttribute("colspan", 6);
          cell1.setAttribute("class", "halftime");
    }

    function secToMin(sec) {
    var s = parseInt(sec);
    var str = s % 60;

    if (str.toString().length === 1)
        str = "0" + str;

    s = s / 60;
    return parseInt(s) + "." + str;
  }

  function minToSec(min) {
    var parts = min.toString().split('.'); 
    var wholeMinutes = parseInt(parts[0]); 
    var seconds = parseInt(parts[1]);

    var totalSeconds = (wholeMinutes * 60) + seconds; 
    return totalSeconds;
}


</script>