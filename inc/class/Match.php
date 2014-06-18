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
	public $lobbyType;
	public $matchSeqNum;
	public $mmr;
	public $towerStatusRadiant;
	public $towerStatusDire;
	public $cluster;
	public $firstBloodTime;
	public $leagueId;
	
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
			$this->lobbyType = $matchData['result']['lobby_type'];
			$this->players = $matchData['result']['players'];
			$this->matchSeqNum = $matchData['result']['match_seq_num'];
			$this->towerStatusRadiant = $matchData['result']['tower_status_radiant'];
			$this->towerStatusDire = $matchData['result']['tower_status_dire'];
			$this->barracksStatusRadiant = $matchData['result']['barracks_status_radiant'];
			$this->barracksStatusDire = $matchData['result']['barracks_status_dire'];
			$this->cluster = $matchData['result']['cluster'];
			$this->firstBloodTime = $matchData['result']['first_blood_time'];
			$this->leagueId = $matchData['result']['leagueid'];
		}
	}
	
	public function saveToDb() {
		$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
		$db->beginTransaction();
		$sql = 'INSERT INTO `' . DB_TABLE_PREFIX . 'match` SET 
			match_id = :id, start_time = :start_time, duration = :duration, winner = :winner, mode = :mode, lobby_type = :lobby_type, match_seq_num = :match_seq_num, mmr = :mmr, tower_status_radiant = :tower_status_radiant, tower_status_dire = :tower_status_dire, barracks_status_radiant = :barracks_status_radiant, barracks_status_dire = :barracks_status_dire, cluster = :cluster, first_blood_time = :first_blood_time, league_id = :league_id
			ON DUPLICATE KEY UPDATE start_time = :start_time, duration = :duration, winner = :winner, mode = :mode, lobby_type = :lobby_type, match_seq_num = :match_seq_num, mmr = :mmr, tower_status_radiant = :tower_status_radiant, tower_status_dire = :tower_status_dire, barracks_status_radiant = :barracks_status_radiant, barracks_status_dire = :barracks_status_dire, cluster = :cluster, first_blood_time = :first_blood_time, league_id = :league_id';
		try {
			$stmt = $db->prepare($sql);
			$stmt->execute(array(':id' => $this->matchId, ':start_time' => $this->startTime, ':duration' => $this->duration, ':winner' => $this->winner, ':mode' => $this->mode, ':lobby_type' => $this->lobbyType, ':match_seq_num' => $this->matchSeqNum, ':mmr' => $this->mmr, ':tower_status_radiant' => $this->towerStatusRadiant, ':tower_status_dire' => $this->towerStatusDire, ':barracks_status_radiant' => $this->barracksStatusRadiant, ':barracks_status_dire' => $this->barracksStatusDire, ':cluster' => $this->cluster, ':first_blood_time' => $this->firstBloodTime, ':league_id' => $this->leagueId));
			$this->publicId = $db->lastInsertId();
			
			if (is_array($this->players)) {
				foreach ($this->players AS $p) {
					if (isset($p['hero_damage'])) {
						$playerSql = 'INSERT IGNORE INTO `' . DB_TABLE_PREFIX . 'match_player` SET account_id = :account_id, match_id = :match_id, hero_id = :hero_id, position = :position, kills = :kills, deaths = :deaths, assists = :assists, leaver_status = :leaver_status, gold = :gold, last_hits = :last_hits, denies = :denies, gold_per_min = :gold_per_min, xp_per_min = :xp_per_min, gold_spent = :gold_spent, hero_damage = :hero_damage, tower_damage = :tower_damage, hero_healing = :hero_healing, level = :level';
						$stmt = $db->prepare($playerSql);
						$stmt->execute(array(':account_id' => $p['account_id'], ':match_id' => $this->matchId, ':hero_id' => $p['hero_id'], ':position' => $p['player_slot'], ':kills' => $p['kills'], ':deaths' => $p['deaths'], ':assists' => $p['assists'], ':assists' => $p['assists'], ':leaver_status' => $p['leaver_status'], ':gold' => $p['gold'], ':last_hits' => $p['last_hits'], ':denies' => $p['denies'], ':gold_per_min' => $p['gold_per_min'], ':xp_per_min' => $p['xp_per_min'], ':gold_spent' => $p['gold_spent'], ':hero_damage' => $p['hero_damage'], ':tower_damage' => $p['tower_damage'], ':hero_healing' => $p['hero_healing'], ':level' => $p['level']));
					}
					else {
						$playerSql = 'INSERT IGNORE INTO `' . DB_TABLE_PREFIX . 'match_player` SET account_id = :account_id, match_id = :match_id, hero_id = :hero_id, position = :position';
						$stmt = $db->prepare($playerSql);
						$stmt->execute(array(':account_id' => $p['account_id'], ':match_id' => $this->matchId, ':hero_id' => $p['hero_id'], ':position' => $p['player_slot']));
					}
				}
			}			
			$db->commit();
		}
		catch (PDOException $e) {
			Error::outputError('Failed to insert match data to database', $e, 1);
			$db->rollBack();
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
				$matchSql = 'SELECT public_id, match_id, match_seq_num, start_time, duration, winner, mode, lobby_type, mmr, tower_status_radiant, tower_status_dire, barracks_status_radiant, barracks_status_dire, cluster, first_blood_time, league_id FROM `' . DB_TABLE_PREFIX . 'match` WHERE match_id = ?';
				$searchId = $this->matchId;
			} 
			elseif ($this->publicId) {
				$matchSql = 'SELECT public_id, match_id, match_seq_num, start_time, duration, winner, mode, lobby_type, mmr, tower_status_radiant, tower_status_dire, barracks_status_radiant, barracks_status_dire, cluster, first_blood_time, league_id FROM `' . DB_TABLE_PREFIX . 'match` WHERE public_id = ?';
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
			$this->lobbyType = $row['lobby_type'];
			$this->mmr = $row['mmr'];
			$this->matchSeqNum = $row['match_seq_num'];
			$this->towerStatusRadiant = $row['tower_status_radiant'];
			$this->towerStatusDire = $row['tower_status_dire'];
			$this->cluster = $row['cluster'];
			$this->firstBloodTime = $row['first_blood_time'];
			$this->leagueId = $row['league_id'];
			
			$stmt = $db->prepare('SELECT account_id, hero_id, position, kills, deaths, assists, leaver_status, gold, last_hits, denies, gold_per_min, xp_per_min, gold_spent, hero_damage, tower_damage, hero_healing, level FROM `' . DB_TABLE_PREFIX . 'match_player` WHERE match_id = ?');
			$stmt->execute(array($this->matchId));
			$this->players = array();
			
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				do {
					list($team, $position) = $this->parsePlayerPosition($row['position']);
					
					$keys = array_keys($row);
					$playerArray = array();
					foreach ($keys AS $key) {
						$playerArray[$key] = $row[$key];
					}
					$this->players[] = $playerArray;					
				} while ($row = $stmt->fetch(PDO::FETCH_ASSOC));
			}
			else {
				return false;
			}
		}
		catch (Exception $e) {
			Error::outputError('Can\'t load match information from database', $e, 1);
			return false;
		}
	}
	
	function parsePlayerPosition($b) {
		//echo ($pos & 1) . " : ";
		$team = ($b >> 7) == 1 ? 'd' : 'r';
		$position = 1 + ($b & 1) + ($b & 2) + ($b & 4);
		
		return array($team, $position);
	}
	
	function isValid($debug = false) {
		if (is_array($this->players)) {
			if (count($this->players) < 10) {
				if ($debug) { echo "Match no good, no 10 players in game\n"; }
				return false;
			}
			foreach ($this->players AS $p) {
				// For some reason leaver_status isn't always provided (bot match, or something?)
				if (!isset($p['leaver_status']) || $p['leaver_status'] === 1 || $p['hero_id'] === 0) {
					if ($debug) { echo "Match no good, leaver_status {$p['leaver_status']}, hero_id {$p['hero_id']}\n"; }
					return false;
				}
			}
		}
		if ($this->duration < 600) {
			if ($debug) { echo "Match no good, duration {$this->duration}\n"; }
			return false;
		}
		elseif ($this->mode > 5) {
			if ($debug) { echo "Match no good, mode {$this->mode}\n"; }
			return false;
		}
		elseif ($this->lobbyType > 0) {
			if ($debug) { echo "Match no good, lobbyType {$this->mode}\n"; }
			return false;
		}
		else {
			return true;
		}
	}
}