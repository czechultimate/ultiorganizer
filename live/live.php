<?php

$html = "";
$html .= "<div data-role='header'>\n";
$html .= "<h1>" . _("Live") . "</h1>\n";
$html .= "</div><!-- /header -->\n\n";

$html .= "<div data-role='content'>\n";

if (isset($_GET['scope'])) {
    $scope = urldecode($_GET['scope']);
}

if (isset($_GET['serie'])) {
    $serie = urldecode($_GET['serie']);
}
if ($scope == "today"){
    $id = CurrentSeason();
    $gamefilter = "season";
    $timefilter = "today";
    $order = "time";
} else if ($scope == "series"){
    $id = $serie;
    $gamefilter = "series";
    $timefilter = "all";
    $order = "time";
} else {
    $id = 1149;//CurrentSeason();
    $gamefilter = "series";
    $timefilter = "all";
    $order = "time";
}

$games = TimetableGames($id, $gamefilter, $timefilter, $order);

$prevseries;
$prevdate = "";
$prevrg = "";
$prevloc = "";
$html .= "<div class='ui-grid-solo'>";
$html .= "<ul data-role='listview'>\n";
while ($game = mysqli_fetch_assoc($games)) { 
    
          $html .= "<div>" . utf8entities($game['name']) . "</div>";
          $prevrg = $game['name'];
  

            $html .= "<li>";
  
  
            if ($game['hometeam'] && $game['visitorteam']) {
              $html .= "<div>";
              $html .= "<table>";
              $html .= "<tbody>";
              $html .= "<tr>";
              $html .= "<td style='padding-left:10px'>";
              $html .= DefHourFormat($game['time']) . _(" - ") . utf8entities($game['poolname']) . _(" - ") . "<a href='?view=live&amp;scope=series&amp;serie=" . $game['series_id'] . "' data-ajax='false'>" . utf8entities($game['seriesname']) . "</a>";
              $html .= "</td></tr>";
              $html .= "<tr><td style='padding-left:10px'>";
              $html .= utf8entities($game['hometeamname']) . " - " . utf8entities($game['visitorteamname']);
              $html .= "</td></tr>";
              $html .= "<tr><td style='padding-left:10px; white-space:nowrap;'>";
              if (GameHasStarted($game)) {
                $html .= intval($game['homescore']) . " - " . intval($game['visitorscore']);
              } else {
                $html .= "? - ?";
              }
              $html .= "</td></tr>";
              $html .= "</tbody>";
              $html .= "</table>";
              
              $html .= "</div>\n";
            } else {
              $html .= utf8entities($game['phometeamname']) . " - " . utf8entities($game['pvisitorteamname']) . " ";
            }
            $html .= "</li>\n";
          
        
        $prevseries = $game['series'];
      
    }
    $html .= "<div data-role='controlgroup' data-type='horizontal'>\n";
    $html .= "<a href='?view=live&amp;scope=upcoming' data-role='button' data-ajax='false'>" . _("Upcoming") . "</a>";
    $html .= "<a href='?view=live&amp;scope=today' data-role='button' data-ajax='false'>" . _("Today") . "</a>";

    $html .= "</div>";
  $html .= "</ul>";
//$html .= LiveUpcomingView($games, $groupheader);
$html .= "</div>";
$html .= "</div><!-- /content -->\n\n";
echo $html;