<?php
ob_start();
?>
<!--
[CLASSIFICATION]
category=database
type=import
format=csv
security=superadmin
customization=all

[DESCRIPTION]
title = "Import Spirit from tournament"
description = "CSV file format: gameId,hometeamId,spirit(22324),comment,visitorId,spirit,comment"
-->
<?php

$seasonId = "";

if (!empty($_POST['season'])) {
	$seasonId = $_POST['season'];
}

ob_end_clean();
if (!isSuperAdmin()) {
	die('Insufficient user rights');
}
// schovat vyber separatoru, napsat napovedu tvaru, zaskrtnout vzdy utf8
include_once 'lib/season.functions.php';
include_once 'lib/series.functions.php';

$html = "";
$title = ("Import Spirit from CSV file");

if (isset($_POST['import'])) {
	if (is_uploaded_file($_FILES['file']['tmp_name'])) {
		$row = 1;
		if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
				$gameid = $data[0];
				$hometeamid = $data[1];
				$spiritH = $data[2];
				$commentH = $data[3];
                $visitorteamid = $data[4];
				$spiritV = $data[5];
				$commentV = $data[6];
                
                print($gameid);
                //add hometeam

                //add visitor team

			}
			fclose($handle);
		}
	} else {
		$html .= "<p>" . ("There was an error uploading the file, please try again!") . "</p>";
	}
}



if (empty($seasonId)) {
    $html .= "<form method='post' enctype='multipart/form-data' action='?view=plugins/import_csv_spirit'>\n";
	$html .= "<p>" . ("Select event") . ": <select class='dropdown' name='season'>\n";

	$seasons = Seasons();

	while ($row = mysqli_fetch_assoc($seasons)) {
		$html .= "<option class='dropdown' value='" . utf8entities($row['season_id']) . "'>" . utf8entities($row['name']) . "</option>";
	}

	$html .= "</select></p>\n";
	$html .= "<p><input class='button' type='submit' name='select' value='" . ("Select") . "'/></p>";
    $html .= "</form>";
    
} else { 
    //$html .= "<p>" . ("CSV separator") . ": <input class='input' maxlength='1' size='1' name='separator' value=','/></p>\n";
    $html .= "<form method='post' enctype='multipart/form-data' action='?view=plugins/import_csv_spirit'>\n";
    $html .= "<p>" . ("Select file to import") . ":<br/>\n";
    $html .= "<input class='input' type='file' size='100' name='file'/><br/>\n";
    //$html .= "<input class='input' type='checkbox' name='utf8' /> " . ("File in UTF-8 format") . "</p>";
    $html .= "<p><input class='button' type='submit' name='import' value='" . ("Import") . "'/></p>";
    $html .= "<p> CSV file format: GameId, HometeamId, Spirit, Comment, VisitorteamId, Spirit, Comment</p>";
    $html .= "<div><input type='hidden' name='MAX_FILE_SIZE' value='50000000' /></div>\n";
    $html .= "</form>";

    $html .= "<form method='post' enctype='multipart/form-data' action='?view=ext/gameinfocsv'>\n";
    $html .= "<p>" . _("Select event") . ":	<select class='dropdown' name='series'>\n";
        $series = SeasonSeries($seasonId);
        foreach ($series as $row) {
            $html .= "<option class='dropdown' value='" . utf8entities($row['series_id']) . "'>" . utf8entities($row['name']) . "</option>";
        }
        $html .= "</select></p>\n";
    $html .= "<p><input class='button' type='submit' name='select' value='" . ("Spirit template") . "'/></p>";
}
showPage($title, $html);
?>