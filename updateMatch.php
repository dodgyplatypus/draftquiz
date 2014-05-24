<?php

// get settings
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');

// get connection handle
$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);

// get match id
if(!isset($_GET['match_id'])) {
	die('ERROR: No match id given');
}
$matchID = preg_replace('/[^0-9]/', '', $_GET['match_id']);

// get detailed match data
$json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/V001/?match_id' . $matchID . '&key=' . API_KEY);
$match = json_decode($json, true);

print_r($match);

// execute queries
// TODO: write this (update match data, player data)