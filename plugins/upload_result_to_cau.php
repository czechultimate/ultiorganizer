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
title = "Upload Result to CAU"
description = "CSV file format: firstname,lastname,number,team name."
-->
<?php
ob_end_clean();
if (!isSuperAdmin()) {
	die('Insufficient user rights');
}

include_once 'lib/season.functions.php';
include_once 'lib/club.functions.php';
include_once 'lib/series.functions.php';
include_once 'lib/player.functions.php';

$html = "";
$title = ("Upload Result to CAU");
$seasonId = "";
$SeasonYear = "";
$CAUTournamentId ="";

if (!empty($_POST['season'])) {
	$seasonId = $_POST['season'];
}

if (!empty($_POST['year'])) {
    $SeasonYear = $_POST['year'];
}

if (!empty($_POST['cautournamentid'])) {
    $CAUTournamentId = $_POST['cautournamentid'];
}

if (isset($_POST['upload'])) {
    $seriesId = $_POST['seriesid'];
    $teams = GetTeamsAtTournament($CAUTournamentId);
    $spiritAvg = SeriesSpiritBoardOnlyFilled($seriesId);
    $rankedteams = SeriesRanking($seriesId);
    $rank = 1;
    foreach ($rankedteams as $team) {
        UploadResultForTeam($team['cau_team_id'], $rank, $teams, $spiritAvg, $team['team_id']);
        $rank++;
    }
    
}
//season selection
$html .= "<form method='post' enctype='multipart/form-data' action='?view=plugins/upload_result_to_cau'>\n";

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
    
    
} else if (!empty($seasonId) and empty($CAUTournamentId)) {

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

	$html .= "<p><input class='button' type='submit' name='upload' value='" . ("Upload") . "'/></p>";
	$html .= "<div>";
	$html .= "<input type='hidden' name='MAX_FILE_SIZE' value='50000000' />\n";
	$html .= "<input type='hidden' name='season' value='$seasonId' />\n";
	$html .= "</div>\n";
}

$html .= "</form>";

showPage($title, $html);

function GetCAUTournaments($SeasonYear){
    $api = "https://evidence.frisbee.cz/api/competitions";

    $response = file_get_contents("$api?season=$SeasonYear");

    $seasonData = json_decode($response);

    $tournamentData = GetTournamentNames($seasonData);
    return $tournamentData;
}

function UploadResultForTeam($cau_team_id, $rank, $cau_teams, $spiritAvg, $team_uo_id) {
    $api = "https://evidence.frisbee.cz/api/team-at-tournament/";
    $ApiToken="0001931a9e8c6ccabbffe501f4a37ba266de7e06";

    $team_id = findTeamIdByCauTeamId($cau_teams, $cau_team_id);
    print($team_id);
    $team_spirit = findSpiritByTeamId($spiritAvg, $team_uo_id);
    $team_spirit = number_format($team_spirit, 3);
    $data = json_encode(['final_placement' => $rank, 'spirit_avg' => $team_spirit]);
    $options = [
        'http' => [
            'method' => 'PATCH',
            'header' => "Content-Type: application/json\r\n" .
                        "Authorization: Token $ApiToken\r\n",
            'content' => $data
        ]
    ];

    $context = stream_context_create($options);
    $api = $api . $team_id;

    $response = file_get_contents($api, false, $context);
}

function findSpiritByTeamId($spiritAvg, $team_id) {

    foreach ($spiritAvg as $spirit) {
        if ($spirit["team_id"] == $team_id) {
            return $spirit["total"] ;
        }
    }
    return null;
}

function findTeamIdByCauTeamId($cau_teams, $cau_team_id) {
    foreach ($cau_teams as $team) {
        if ($team->application_id == $cau_team_id) {
            return $team->id;
        }
    }
    return null;
}

function GetTeamsAtTournament($TournamentId){
    $api = "https://evidence.frisbee.cz/api/teams-at-tournament";
    

    $response = file_get_contents("$api?tournament_id=$TournamentId");


    $tournamentData = json_decode($response);


    $teamData = [];
    foreach ($tournamentData as $team) {
        $teamData[] = (object)[
            'id' => $team->id,
            'application_id' => $team->application_id,
            'club_id' => $team->club_id,
            'team_name' => $team->team_name,
            'seeding' => $team->seeding
        ];
    }

    // Sort the teamData array by the 'id' field
    usort($teamData, function($a, $b) {
        return $a->id <=> $b->id;
    });

    return $teamData;
}

function GetTournamentNames($apiResponse) {
    $tournamentNames = [];
    foreach ($apiResponse as $competition) {
        foreach ($competition->tournaments as $tournament) {
            $tournamentNames[] = (object)[
                'id' => $tournament->id,
                'name' => $competition->name . ' - ' . $competition->division . ' - ' . $tournament->name
            ];
        }
    }
    return $tournamentNames;
}


function GetClubById($club_id){
    $api = "https://evidence.frisbee.cz/api/clubs";

    $response = file_get_contents($api);

    $clubData = json_decode($response);

    foreach ($clubData as $club) {
        if ($club->id == $club_id) {
            return $club->name;
        }
    }

    return "";
}

?>