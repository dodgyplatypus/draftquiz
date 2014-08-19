<?php
require_once('config.php');
require_once('Error.php');
require_once('Match.php');

/**
 * To add guesses to db, and fetch statistics about them
 */
class GuessManager {
	
	/**
	 * Make a new guess
	 **/
	function add($matchId, $guess) {
		try {
			$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
			$stmt = $db->prepare('INSERT INTO `' . DB_TABLE_PREFIX . 'guess` SET match_id = :match_id, guess = :guess, ip = :ip, added = NOW()');
			
			$stmt->execute(array(':match_id' => $matchId, ':guess' => $guess, ':ip' => $_SERVER['REMOTE_ADDR']));
			return true;
		}
		catch (Exception $e) {
			//  duplicate result, it's ok, let's just move on
			if ($e->getCode() === '23000') {
				return false;
			}
			Error::outputError('Failed to add a guess', $e, 1);
		}
	}
	
	/**
	 * Gets stats on given match
	 */
	function getStats($matchId) {
		try {
			$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
			$stmt = $db->prepare('SELECT COUNT(*) AS count, guess FROM `' . DB_TABLE_PREFIX . 'guess` WHERE match_id = :match_id GROUP BY guess');
			$stmt->execute(array(':match_id' => $matchId));
			
			$result = array(0, 0);
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$result[$row['guess']] = (int) $row['count'];
			}
			return $result;
		}
		catch (Exception $e) {
			Error::outputError('Failed to get guess statistics', $e, 1);
		}
	}
}