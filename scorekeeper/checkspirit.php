<?php
$html = "";

$html .= "<div data-role='header'>\n";
$html .= "<h1>" . _("Missing spirit") . "</h1>\n";
$html .= "</div><!-- /header -->\n\n";
$html .= "<div data-role='content'>\n";

$series = $_GET['series'];

$results = SeriesMissingSpirit($series);

$game_ids_grouped = GroupSpiritResult($results);

foreach($game_ids_grouped as $game){
    if(is_null($game['spirit1'])){
        $html .= "<p><b>" . $game['homename'] . " - " . $game['visitname'] . "</b></p>";
    } else if($game['spirit1'] == $game['hometeam'] && is_null($game['spirit2'])) {
        $html .="<p>" . $game['homename'] . " - <b>" . $game['visitname'] . "</b></p>";
    } else if($game['spirit1'] == $game['visitorteam'] && is_null($game['spirit2'])) {
        $html .= "<p><b>" . $game['homename'] . "</b> - " . $game['visitname'] . "</p>";
    } 
}

$html .= "<a href='?view=respgames' data-role='button' data-ajax='false'>" . _("Back to game responsibilities") . "</a>";
$html .= "</div><!-- /content -->\n\n";

echo $html;

?>
