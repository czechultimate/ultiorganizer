<?php
include_once 'localization.php';
include_once '../lib/game.functions.php';

$series = $_POST['series'];
$encoding = 'UTF-8';
$separator = ',';

$data = GetGameInfoCSV($series, $separator);
$data = mb_convert_encoding($data, $encoding, 'UTF-8');
CloseConnection();
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Length: " . strlen($data));
header("Content-type: text/x-csv");
header("Content-Disposition: attachment; filename=spirit.csv");
echo $data;