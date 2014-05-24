<?php

// get settings
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');
require_once(INC_PATH . 'class/Match.php');

// get connection handle
$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);

// get new match data
$json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?min_players=10&matches_requested=20&key=' . API_KEY);

$matches = json_decode($json, true);
//print_r($matches);
// execute queries
for($i = 0; $i < count($matches['result']['matches']); $i++) {
	$match = $matches['result']['matches'][$i];
	if(isValidMatch($match)) {
		$db->beginTransaction();
		try {
			// insert match
			$sql = 'INSERT INTO `' . DB_TABLE_PREFIX . 'match` (`id`, `start_time`) VALUES (:id, :start_time)';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':id', $match['match_id'], PDO::PARAM_INT);
			$stmt->bindValue(':start_time', $match['start_time'], PDO::PARAM_INT);
			$stmt->execute();

			// insert players
			for($j = 0; $j < 10; $j++) {
				$player = $match['players'][$j];
				$sql = 'INSERT INTO `' . DB_TABLE_PREFIX . 'match_player` (`account_id`, `match_id`, `hero_id`, `position`) VALUES (:account_id, :match_id, :hero_id, :position)';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':account_id', $player['account_id'], PDO::PARAM_INT);
				$stmt->bindValue(':match_id', $match['match_id'], PDO::PARAM_INT);
				$stmt->bindValue(':hero_id', $player['hero_id'], PDO::PARAM_INT);
				$stmt->bindValue(':position', $player['player_slot'], PDO::PARAM_INT);
				$stmt->execute();
			}
			$db->commit();
			
			// get detailed match data
			$matchObject = new Match($match['match_id']);
			$matchObject->fetchFromApi();
			$matchObject->saveToDb();
			
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