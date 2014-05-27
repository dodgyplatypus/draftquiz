<?php
require_once('config.php');
require_once('Error.php');
require_once('Match.php');

/**
 * Retrieves Matches from database and from API
 * And manages the matches :|
 */
class MatchManager {
	/**
	 * Fetches matches from api, and saves them to the database
	 * @todo This really should return a list of matches
	 */
	public function fetchFromApi($count = 25) {
		$count = (int) $count;
		
		$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);

		// get new match data
		$json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?min_players=10&skill=3&matches_requested=' . $count . '&key=' . API_KEY);

		$matches = json_decode($json, true);
		
		$matchList = array();
		// execute queries
		for($i = 0; $i < count($matches['result']['matches']); $i++) {
			$match = $matches['result']['matches'][$i];
			if($this->isValidMatch($match)) {
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
					$matchList[] = $matchObject;
					echo "Match " . $match['match_id'] . " added!\n";
				}
				catch (PDOException $e) {
					$db->rollBack();
					Error::outputError('Failed to insert match/players data', $e->getMessage(), 1);
				}
			}
		}
		return $matchList;
	}
	
	public function getRandomMatches($count = 10) {
		$count = (int) $count;
		$matches = array();
		
		try {
			$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
			// get 10 random IDs from matches-table
			// http://jan.kneschke.de/projects/mysql/order-by-rand/
			$stmt = $db->prepare('
			SELECT r1.public_id
				FROM ' . DB_TABLE_PREFIX . 'match AS r1 JOIN
						 (SELECT (RAND() * (SELECT MAX(public_id) FROM ' . DB_TABLE_PREFIX . 'match)) AS public_id) AS r2
				WHERE r1.public_id >= r2.public_id
				ORDER BY r1.public_id ASC
				LIMIT ' . $count);
			
			$stmt->execute(array($count));
			while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				$match = new Match();
				$match->loadFromDb(false, $row[0]);
				$matches[] = $match;
			}
			return $matches;
		}
		catch (Exception $e) {
			Error::outputError('Failed to get random matches', $e->getMessage(), 1);
		}
	}
	
	private function isValidMatch($match) {
		if($match['lobby_type'] !== 0) return false;
		foreach($match['players'] as $player) {
			if($player['hero_id'] === 0) return false;
		}
		return true;
	}
}