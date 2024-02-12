<?php
$html = "";

$html .= "<div data-role='header'>\n";
$html .= "<h1>" . _("Missing spirit") . "</h1>\n";
$html .= "</div><!-- /header -->\n\n";
$html .= "<div data-role='content'>\n";

$series = $_GET['series'];

$results = SeriesMissingSpirit($series);
// Předpokládáme, že máte výstup dotazu uložený v proměnné $results ve formě asociativního pole

$game_ids = [];
$team_ids = [];

// Procházení výsledků a shromažďování unikátních game_id a team_id
$game_ids_grouped = [];
$current_game_id = null;
foreach ($results as $result) {
    $game_id = $result['game_id'];
    if ($game_id === $current_game_id) {
        // Pokud je aktuální game_id stejné jako předchozí, přidejte tento záznam do existujícího pole
        $game_ids_grouped[$game_id]['spirit2'] = $result['team_id'];
    } else {
        // Pokud je game_id jiné než předchozí, vytvořte nové pole pro toto game_id
        $game_ids_grouped[$game_id]['hometeam'] = $result['hometeam'];
        $game_ids_grouped[$game_id]['visitorteam'] = $result['visitorteam'];
        $game_ids_grouped[$game_id]['homescore'] = $result['homescore'];
        $game_ids_grouped[$game_id]['visitorscore'] = $result['visitorscore'];
        $game_ids_grouped[$game_id]['homename'] = $result['home_team_name'];
        $game_ids_grouped[$game_id]['visitname'] = $result['visitor_team_name'];
        $game_ids_grouped[$game_id]['spirit1'] = $result['team_id'];
        $current_game_id = $game_id;
    }
}

$missing_records = [];
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
