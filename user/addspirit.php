<?php
include_once $include_prefix . 'lib/common.functions.php';
include_once $include_prefix . 'lib/game.functions.php';
include_once $include_prefix . 'lib/standings.functions.php';
include_once $include_prefix . 'lib/pool.functions.php';
include_once $include_prefix . 'lib/configuration.functions.php';
$html = "";

$gameId = intval($_GET["game"]);
$teamId = intval($_GET["team"]);
$title = _("Spirit");

$season = SeasonInfo(GameSeason($gameId));
if ($season['spiritmode'] > 0) {
  $game_result = GameResult($gameId);
  $mode = SpiritMode($season['spiritmode']);
  $categories = SpiritCategories($mode['mode']);

  //process itself if save button was pressed
  if (!empty($_POST['save'])) {
    if (isset($_POST['homevalueId'])) {
    $points = array();
    foreach ($_POST['homevalueId'] as $cat) {
      if (isset($_POST['homecat' . $cat]))
        $points[$cat] = $_POST['homecat' . $cat];
      else
        $missing = sprintf(_("Missing score for %s. "), $game_result['hometeamname']);
    }
    GameSetSpiritPoints($gameId, $game_result['hometeam'], 1, $points, $categories);
    }

    if (isset($_POST['homecatcomment'])){
      GameSetSpiritComment($gameId,$game_result['hometeam'],$_POST['homecatcomment']);
    }

    if (isset($_POST['visvalueId'])) {
    $points = array();
    foreach ($_POST['visvalueId'] as $cat) {
      if (isset($_POST['viscat' . $cat]))
        $points[$cat] = $_POST['viscat' . $cat];
      else
        $missing = sprintf(_("Missing score for %s. "), $game_result['visitorteamname']);
    }
    GameSetSpiritPoints($gameId, $game_result['visitorteam'], 0, $points, $categories);
    }

    if (isset($_POST['viscatcomment'])){
      GameSetSpiritComment($gameId,$game_result['visitorteam'],$_POST['viscatcomment']);
    }
    $game_result = GameResult($gameId);
  }

  if(hasEditGameEventsRight($gameId)){
    $menutabs[_("Result")] = "?view=user/addresult&game=$gameId";
    $menutabs[_("Players")] = "?view=user/addplayerlists&game=$gameId";
    $menutabs[_("Score sheet")] = "?view=user/addscoresheet&game=$gameId";
  }
  if($teamId > 0){
    
  }else {
    $menutabs[_("Spirit points")] = "?view=user/addspirit&game=$gameId";
  }
  if (ShowDefenseStats()) {
    $menutabs[_("Defense sheet")] = "?view=user/adddefensesheet&game=$gameId&amp;team=$teamId";
  }
  $html .= pageMenu($menutabs, "", false);

  $html .= "<form  method='post' action='?view=user/addspirit&amp;game=" . $gameId . "&amp;team=$teamId'>";

  if ($teamId > 0) {
    if ($teamId == $game_result['hometeam']) {
      $html .= "<h3>" . _("Spirit points given for") . ": " . utf8entities($game_result['hometeamname']) . "</h3>\n";
      $comment = GameGetSpiritComment($gameId, $game_result['hometeam']);
      $points = GameGetSpiritPoints($gameId, $game_result['hometeam']);
      $html .= SpiritTable($game_result, $points, $categories,true, $comment['note'], true);
    }
    if ($teamId == $game_result['visitorteam']) {
      $html .= "<h3>" . _("Spirit points given for") . ": " . utf8entities($game_result['visitorteamname']) . "</h3>\n";
      $comment = GameGetSpiritComment($gameId, $game_result['visitorteam']);
      $points = GameGetSpiritPoints($gameId, $game_result['visitorteam']);
      $html .= SpiritTable($game_result, $points, $categories, false, $comment['note'], true);
    }
  } else {
    $html .= "<h3>" . _("Spirit points given for") . ": " . utf8entities($game_result['hometeamname']) . "</h3>\n";

    $points = GameGetSpiritPoints($gameId, $game_result['hometeam']);
    $comment = GameGetSpiritComment($gameId, $game_result['hometeam']);
    $html .= SpiritTable($game_result, $points, $categories,true,$comment['note'], true);
    $html .= "<h3>" . _("Spirit points given for") . ": " . utf8entities($game_result['visitorteamname']) . "</h3>\n";
    $points = GameGetSpiritPoints($gameId, $game_result['visitorteam']);
    $comment = GameGetSpiritComment($gameId, $game_result['visitorteam']);
    $html .= SpiritTable($game_result, $points, $categories,false,$comment['note'], true);
  }
  $html .= "<p>";
  $html .= "<input class='button' type='submit' name='save' value='" . _("Save") . "'/>";
  if (isset($missing))
    $html .= " $missing";
  $html .= "</p>";
  $html .= "</form>\n";
} else {
  $html .= "<p>" . sprintf(_("Spirit points not given for %s."), utf8entities($season['name'])) . "</p>";
}
showPage($title, $html);
