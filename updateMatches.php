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

// get new match data
$json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?min_players=10&key=' . $apiKey);
$matches = json_decode($json, true);

// execute queries
for($i = 0; $i < count($matches['result']['matches']); $i++) {
	$match = $matches['result']['matches'][$i];
	if(isValidMatch($match)) {
		$db->beginTransaction();
		try {
			// insert match
			$sql = 'INSERT INTO `match` (`id`, `start_time`) VALUES (:id, :start_time)';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':id', $match['match_id'], PDO::PARAM_STR);
			$stmt->bindValue(':start_time', $match['start_time'], PDO::PARAM_INT);
			$stmt->execute();

			// insert players
			for($j = 0; $j < 10; $j++) {
				$player = $match['players'][$j];
				$sql = 'INSERT INTO `match_player` (`account_id`, `match_id`, `hero_id`, `position`) VALUES (:account_id, :match_id, :hero_id, :position)';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':account_id', $player['account_id'], PDO::PARAM_INT);
				$stmt->bindValue(':match_id', $match['match_id'], PDO::PARAM_STR);
				$stmt->bindValue(':hero_id', $player['hero_id'], PDO::PARAM_INT);
				$stmt->bindValue(':position', $player['player_slot'], PDO::PARAM_INT);
				$stmt->execute();
			}

			$db->commit();
		}
		catch(PDOException $e) {
			$db->rollBack();
			echo 'ERROR: ' . $e->getMessage() . "<br/>\n";
		}
	}
}

// validate match data before adding to to db
function isValidMatch($match) {
	if($match['lobby_type'] !== 0) return false;
	foreach($match['players'] as $player) {
		if($player['hero_id'] === 0) return false;
	}
	return true;
}