<?php
require_once('Error.php');
require_once('config.php');

/**
 * Presents a single Dota 2 match
 * @todo This should have ALL the information about the match, inc players and so on
 */
class Match {
	public $id;
	public $startTime;
	public $duration;
	public $winner;
	public $mode;
	
	public function __construct() {
		$args = func_get_args();
		switch (func_num_args()):
			case 1:
				$this->id = (int) $args[0];
				break;
		endswitch;
	}
	
	public function fetchFromApi() {
		if (!isset($this->id)) {
			Error::outputError('Can\'t fetch from API: No ID given', 'Match->fetchFromApi');
			return false;
		}
		if ($json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/V001/?match_id=' . $this->id . '&key=' . API_KEY)) {
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
			$stmt->execute(array(':id' => $this->id, ':start_time' => $this->startTime, ':duration' => $this->duration, ':winner' => $this->winner, ':mode' => $this->mode));
			$db->commit();
		}
		catch(PDOException $e) {
			$db->rollBack();
			Error::outputError("Failed to insert match data to database", $e->getMessage(), 1);
		}
	}
}