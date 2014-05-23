<?php

// get settings
require_once('config.php');

// create connection handle
try {
	$db = new PDO($dbConnection, $dbUsername, $dbPassword);
}
catch(PDOException $e) {
	die('ERROR: ' . $e->getMessage());
}

// configure connection
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('SET NAMES utf8');

// get match id
if(!isset($_GET['match_id'])) {
	die('ERROR: No match id given');
}
$matchID = preg_replace('/[^0-9]/', '', $_GET['match_id']);

// get detailed match data
$json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/V001/?match_id' . $matchID . '&key=' . $apiKey);
$match = json_decode($json, true);

// execute queries
// TODO: write this (update match data, player data)