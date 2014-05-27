<?php
require_once('Error.php');
require_once('config.php');

/**
 * Presents a single Dota 2 match
 * @todo This should have ALL the information about the match, inc players and so on
 */
class Match {
	public $publicId;
	public $matchId;
	public $startTime;
	public $duration;
	public $winner;
	public $mode;
	public $players;
	
	public function __construct() {
		$args = func_get_args();
		switch (func_num_args()):
			case 1:
				$this->matchId = (int) $args[0];
				break;
		endswitch;
	}
	
	public function fetchFromApi() {
		if (!isset($this->matchId)) {
			Error::outputError('Can\'t fetch from API: No ID given', 'Match->fetchFromApi');
			return false;
		}
		if ($json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/V001/?match_id=' . $this->matchId . '&key=' . API_KEY)) {
			$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
			$matchData = json_decode($json, true);
			// @todo populate the fields from match_players if available
			$this->startTime = $matchData['result']['start_time'];
			$this->duration = $matchData['result']['duration'];
			$this->winner = $matchData['result']['radiant_win'] == true ? 1 : 0;
			$this->mode = $matchData['result']['game_mode'];
		}
	}
	
	public function saveToDb() {
		$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
		$db->beginTransaction();
		$sql = 'INSERT INTO ' . DB_TABLE_PREFIX . 'match SET id = :id, start_time = :start_time, duration = :duration, winner = :winner, mode = :mode ON DUPLICATE KEY UPDATE start_time = :start_time, duration = :duration, winner = :winner, mode = :mode';
		try {
			$stmt = $db->prepare($sql);
			$stmt->execute(array(':id' => $this->matchId, ':start_time' => $this->startTime, ':duration' => $this->duration, ':winner' => $this->winner, ':mode' => $this->mode));
			$this->publicId = $dbh->lastInsertId();
			$db->commit();
		}
		catch(PDOException $e) {
			$db->rollBack();
			Error::outputError('Failed to insert match data to database', $e->getMessage(), 1);
		}
	}
	
	/**
	 * Loads and populates object with information from DB
	 * If both matchId and publicId are given, matchId is used
	 */
	public function loadFromDb($matchId = false, $publicId = false) {
		if ($matchId) {
			$this->matchId = $matchId;
		} 
		if ($publicId) {
			$this->publicId = $publicId;
		}
		
		try {
			$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
			if ($this->matchId) {
				$matchSql = 'SELECT public_id, match_id, start_time, duration, winner, mode FROM ' . DB_TABLE_PREFIX . 'match WHERE match_id = ?';
				$searchId = $this->matchId;
			} 
			elseif($this->publicId) {
				$matchSql = 'SELECT public_id, match_id, start_time, duration, winner, mode FROM ' . DB_TABLE_PREFIX . 'match WHERE public_id = ?';
				$searchId = $this->publicId;
			}
			else {
				throw New exception('No matchId or publicId given');
			}
			
			$stmt = $db->prepare($matchSql);
			$stmt->execute(array($searchId));
			
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$this->publicId = $row['public_id'];
			$this->matchId = $row['match_id'];
			$this->startTime = $row['start_time'];
			$this->duration = $row['duration'];
			$this->winner = $row['winner'];
			$this->mode = $row['mode'];
			
			$stmt = $db->prepare('SELECT account_id, hero_id, position FROM ' . DB_TABLE_PREFIX . 'match_player WHERE match_id = ?');
			$stmt->execute(array($this->matchId));
			$this->players = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$this->players[] = array('account_id' => $row['account_id'], 'hero_id' => $row['hero_id'], 'position' => $row['position']);
			}
		}
		catch (Exception $e) {
			Error::outputError('Can\'t load match information from database', $e->getMessage(), 1);
			return false;
		}
	}
}