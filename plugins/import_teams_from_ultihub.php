<?php
ob_start();
?>
<!--
[CLASSIFICATION]
category=database
type=import
format=api
security=superadmin
customization=all

[DESCRIPTION]
title = "Import Teams from Ultihub"
description = "CSV file format: firstname,lastname,number,team name."
-->
<?php
ob_end_clean();
if (!isSuperAdmin()) {
	die('Insufficient user rights');
}

include_once 'lib/season.functions.php';
include_once 'lib/series.functions.php';
include_once 'lib/player.functions.php';

$html = "";
$title = ("Import Teams from Ultihub");
$seasonId = "";
$SeasonYear = "";
$CAUTournamentId ="";
$domain = "https://api.evidence.czechultimate.cz";

if (!empty($_POST['season'])) {
	$seasonId = $_POST['season'];
}

if (!empty($_POST['year'])) {
    $SeasonYear = $_POST['year'];
}

if (!empty($_POST['cautournamentid'])) {
    $CAUTournamentId = $_POST['cautournamentid'];
}

if (isset($_POST['import'])) {
    $token = GetCAUToken();
    $seriesId = $_POST['seriesid'];
    $teams = SeriesTeams($seriesId);
	$player = PlayersByName();

    $leagueDivisionData = json_decode(file_get_contents("$domain/list/tournament_belongs_to_league_and_division?filter[tournament_id]={$CAUTournamentId}&token=$token"))->data;
    
    $leagueDivisionId = $leagueDivisionData[0]->id;
    $rostersData = json_decode(file_get_contents("$domain/list/roster?filter[tournament_belongs_to_league_and_division_id]=$leagueDivisionId&token=$token"))->data;

    foreach ($rostersData as $roster) {
        $teamData = json_decode(file_get_contents("$domain/list/team?filter[id]=$roster->team_id&token=$token"))->data;
        $teamRostersData = json_decode(file_get_contents("$domain/list/player_at_roster?filter[roster_id]=$roster->id&extend=1&token=$token"))->data;
        foreach ($teamRostersData as $teamRoster) {

            $first = $teamRoster->player->first_name;
            $last = $teamRoster->player->last_name;
            $number = $teamRoster->player->jersey_number;
            if (!empty($roster->name)){
                $team = $teamData[0]->name . " " . $roster->name;
            } else {
                $team = $teamData[0]->name;
            }
            $teamId = -1;
            $playerId = "";
           /* $output = array(
                'team' => $teamData[0]->name,
                'roster_name' => $roster->name,
                'first_name' => $teamRoster->player->first_name,
                'last_name' => $teamRoster->player->last_name,
                'jersey_number' => $teamRoster->player->jersey_number,
            );*/
          // print($teamRoster->player->first_name . " " . $teamRoster->player->last_name . " " . $teamRoster->player->jersey_number  . " " . $teamData[0]->name . " " . $roster->name . "\n" );
           //print_r($output);
           foreach ($teams as $t) {

                if ($t['name'] == $team) {
                    
                    $teamId = $t['team_id'];
                    
                    break;
                }
            }

            foreach ($player as $id => $name) {
                
                if ($name == ($first . ' ' . $last)){
                    $playerId = $id;
                    break;
                }
            }

            if ($teamId != -1) {
                $id = AddPlayer($teamId, $first, $last, $playerId, $number);
            }
        }
    }
}
//season selection
$html .= "<form method='post' enctype='multipart/form-data' action='?view=plugins/import_teams_from_utihub'>\n";

if (empty($seasonId)) {
	$html .= "<p>" . ("Select event") . ": <select class='dropdown' name='season'>\n";

	$seasons = Seasons();

	while ($row = mysqli_fetch_assoc($seasons)) {
		$html .= "<option class='dropdown' value='" . utf8entities($row['season_id']) . "'>" . utf8entities($row['name']) . "</option>";
	}

	$html .= "</select></p>\n";
    $html .= "<p>" . ("Season") . ": <input class='input' maxlength='4' size='4' name='year'/></p>\n";
    $html .= "<p> Season: Year of season</p>";
	$html .= "<p><input class='button' type='submit' name='select' value='" . ("Select") . "'/></p>";
    
    
} else {

    $html .= "<p>" . ("Select tournament from CAU") . ":	<select class='dropdown' name='cautournamentid'>\n";
    $tournamentData = GetCAUTournaments($SeasonYear);

    foreach ($tournamentData as $t) {
		$html .= "<option class='dropdown' value='" . $t->id . "'>" . $t->name . "</option>";
	}

	$html .= "</select></p>\n";

	$html .= "<p>" . ("Select tournament UO") . ":	<select class='dropdown' name='seriesid'>\n";
	$series = SeasonSeries($seasonId);
	foreach ($series as $row) {
		$html .= "<option class='dropdown' value='" . utf8entities($row['series_id']) . "'>" . utf8entities($row['name']) . "</option>";
	}
	$html .= "</select></p>\n";

    
	//$html .= "<p>" . ("Select file to import") . ":<br/>\n";
	//$html .= "<input class='input' type='file' size='100' name='file'/><br/>\n";
	//$html .= "<input class='input' type='checkbox' name='utf8' /> " . ("File in UTF-8 format") . "</p>";
	$html .= "<p><input class='button' type='submit' name='import' value='" . ("Import") . "'/></p>";
	//$html .= "<p> CSV file format: First Name, Last Name, Number, Team</p>";
	$html .= "<div>";
	$html .= "<input type='hidden' name='MAX_FILE_SIZE' value='50000000' />\n";
	$html .= "<input type='hidden' name='season' value='$seasonId' />\n";
	$html .= "</div>\n";
}

$html .= "</form>";

showPage($title, $html);

function GetCAUTournaments($SeasonYear){
    $api = "https://evidence.frisbee.cz/api/competitions";

    $seasonData = json_decode(file_get_contents("$api?season=$SeasonYear"))->data;
    $tournamentData = GetTournamentNames($seasonData);
    print_r($tournamentData);
    return $tournamentData;
}

function GetTournamentNames($apiResponse) {
    $tournamentNames = [];
    foreach ($apiResponse as $competition) {
        foreach ($competition->tournaments as $tournament) {
            $tournamentNames[] = $tournament->name;
        }
    }
    return $tournamentNames;
}

function GetCAUToken(){
    $Username = "ultiorganizer";
    $Password = "A1b2C3d4E5f6G7h8";
   // $SeasonYear = "2024";
    $domain = "https://api.evidence.czechultimate.cz";
    $loginData = array(
        'login' => $Username,
        'password' => $Password,
    );

    $loginResponse = json_decode(
        file_get_contents(
            "$domain/user/login",
            false,
            stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query($loginData),
                ),
            ))
        )
    );

    $token = $loginResponse->token->token;

    return $token;
}
?>