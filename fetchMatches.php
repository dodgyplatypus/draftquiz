<?php
/**
 * Shows examples how to fetch matches from API
 */
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');
require_once(INC_PATH . 'class/Match.php');
require_once(INC_PATH . 'class/MatchManager.php');

$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);

$mm = new MatchManager();

// example fetches 5 games from the internation 3 and saves them to DB
// league id's can be get from http://www.cyborgmatt.com/league-ids/
$matches = $mm->fetchFromApiByLeagueId(65006, 5);
print_r($matches);

// example fetches 5 games for Jake, and marks them as MMR 3200 + saves to db
$matches = $mm->fetchFromApiByPlayerId(34269412, 5, 3200);
print_r($matches);