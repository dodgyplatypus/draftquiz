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
	public function fetchFromApi() {		
		$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
		
		$maxMatchSeqNum = $this->getMaxMatchSeqNum();
		if ($maxMatchSeqNum == false) {
			// fallback, so we dont get the very first matches
			// please change this before populating the database much
			// @todo better solution
			$maxMatchSeqNum = 629049370;
		}
		
		// get new match data
		$json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistoryBySequenceNum/V001/?start_at_match_seq_num=' . $maxMatchSeqNum . '&key=' . API_KEY);

		$matches = json_decode($json, true);
		
		$matchList = array();
		// execute queries
		for($i = 0; $i < count($matches['result']['matches']); $i++) {
			$match = $matches['result']['matches'][$i];
				try {								
				// get detailed match data
				$matchObject = new Match($match['match_id']);
				$matchObject->startTime = $match['start_time'];
				$matchObject->duration = $match['duration'];
				$matchObject->winner = $match['radiant_win'] == true ? 1 : 0;
				$matchObject->mode = $match['game_mode'];
				$matchObject->players = $match['players'];
				$matchObject->lobbyType = $match['lobby_type'];
				$matchObject->matchSeqNum = $match['match_seq_num'];
				if ($matchObject->isValid(true) === true) {
					// test if match is already on the database, 
					// if it is, just go to the next one
					$testMatch = new Match($match['match_id']);
					if ($testMatch->loadFromDb() !== false) {
						continue;
					}
					$matchObject->saveToDb();
					$matchList[] = $matchObject;
				}
			}
			catch (PDOException $e) {
				Error::outputError('Failed to insert match/players data' . $e, $e, 1);
			}
		}
		return $matchList;
	}
	
	public function fetchFromApiByPlayerId($playerId, $matchLimit = 25, $mmr = false, $debug = true) {
		$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
		
		$playerId = (int) $playerId;
		$matchLimit = (int) $matchLimit;
		
		if ($playerId === 0) {
			throw new Exception("No playerId given, can't fetch matches");
		}
		
		// list of matches
		$json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?account_id=' . $playerId . '&matches_requested=' . $matchLimit . '&key=' . API_KEY);
		
		$matchesData = json_decode($json, true);
		
		$matches = array();
		for($i = 0; $i < count($matchesData['result']['matches']); $i++) {
			$match = new Match($matchesData['result']['matches'][$i]['match_id']);
			$match->fetchFromApi();
			$match->mmr = $mmr;
			if ($match->isValid($debug)) {
				$match->saveToDb();
				if ($debug === true) {
					echo "This match is a keeper! {$match->matchId}\n";
				}
				$matches[] = $match;
			}
		}
		
		return $matches;
	}
	
	public function fetchFromApiByLeagueId($leagueId, $matchLimit = 25, $mmr = false, $debug = true) {
		$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
		
		$leagueId = (int) $leagueId;
		$matchLimit = (int) $matchLimit;
		
		if ($leagueId === 0) {
			throw new Exception("No leagueId given, can't fetch matches");
		}
		
		// list of matches
		$json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?league_id=' . $leagueId . '&matches_requested=' . $matchLimit . '&key=' . API_KEY);
		
		$matchesData = json_decode($json, true);
		
		$matches = array();
		for($i = 0; $i < count($matchesData['result']['matches']); $i++) {
			$match = new Match($matchesData['result']['matches'][$i]['match_id']);
			$match->fetchFromApi();
			$match->mmr = $mmr;
			if ($match->isValid($debug)) {
				$match->saveToDb();
				if ($debug === true) {
					echo "This match is a keeper! {$match->matchId}\n";
				}
				$matches[] = $match;
			}
		}
		
		return $matches;
	}
	
	public function getRandomMatches($count = 10, $competitive = false) {
		$count = (int) $count;
		$matches = array();
		
		$competitiveSql = '';
		if ($competitive) {
			$competitiveSql = 'league_id > 0';
		}
		else {
			$competitiveSql = 'league_id = 0';
		}
		
		try {
			$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
			
			// get 10 random IDs from matches-table
			// modified from http://stackoverflow.com/questions/18943417/how-to-quickly-select-3-random-records-from-a-30k-mysql-table-with-a-where-filte
			$stmt = $db->prepare('
			SELECT m.public_id FROM `' . DB_TABLE_PREFIX . 'match` AS m
			WHERE ((' . $competitiveSql . ') AND RAND() < 6 * ' . $count . '/(SELECT COUNT(*) FROM `' . DB_TABLE_PREFIX . 'match`)) LIMIT ' . $count);
			
			$stmt->execute(array($count));
			while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				$match = new Match();
				$match->loadFromDb(false, $row[0]);
				// sql returns 10 sequental matches from random position, all games are not necessarily competitive even thou first is
				if ($competitive && $match->leagueId > 0) {
					$matches[] = $match;
				}
				elseif ($competitive == false && $match->leagueId == 0) {
					$matches[] = $match;
				}
			}
			return $matches;
		}
		catch (Exception $e) {
			Error::outputError('Failed to get random matches', $e, 1);
		}
	}
	
	public function getMaxMatchSeqNum() {
		try {
			$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
			$stmt = $db->prepare('SELECT MAX(match_seq_num) AS match_seq_num FROM `' . DB_TABLE_PREFIX . 'match`');
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				return $row[0];
			}
			else {
				return false;
			}
		}
		catch (Exception $e) {
			Error::outputError('Failed to get max match_seq_number', $e, 1);
		}
	}
}