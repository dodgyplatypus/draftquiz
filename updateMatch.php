<?php

// get settings
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');
require_once(INC_PATH . 'class/Match.php');

// get connection handle
$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);

// get match id
if(!isset($_GET['match_id'])) {
	die('ERROR: No match id given');
}
$matchID = preg_replace('/[^0-9]/', '', $_GET['match_id']);

// get detailed match data
$match = new Match($matchID);
$match->fetchFromApi();
$match->saveToDb();

print_r($match);
// execute queries
// TODO: write this (update match data, player data)