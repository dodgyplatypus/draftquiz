<?php
// we need to change working directory, in order to keep requires 
// working in classes and such :/
chdir('../');
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');
require_once(INC_PATH . 'class/Error.php');
require_once(INC_PATH . 'class/GuessManager.php');

$publicId = (int) $_GET['publicId'];
$guess = $_GET['guess'];
$output = array();

if ($publicId < 1) {
	$output['error'][] = "You must specify publicId";
}
elseif ($guess !== "0" && $guess !== "1") {
	$output['error'][] = "You must specify guess";
}
else {
	try {
		$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);
		$stmt = $db->prepare('SELECT m.match_id, m.duration, m.winner, m.mode, m.league_id, m.radiant_name, m.dire_name, h.name, h.en_name, p.position, p.kills, p.deaths, p.assists, p.level'
				. ', IF(p.deaths=0, (p.kills + p.assists), ((p.kills + p.assists) / p.deaths)) AS kda'
				. ' FROM ' . DB_TABLE_PREFIX . 'match AS m, ' . DB_TABLE_PREFIX . 'match_player AS p, ' . DB_TABLE_PREFIX . 'hero AS h'
				. ' WHERE m.public_id = ? AND m.match_id = p.match_id AND p.hero_id = h.id'
				. ' GROUP BY m.match_id, p.hero_id'
				. ' ORDER BY kda DESC');
		$stmt->bindParam(1, $publicId, PDO::PARAM_INT);
		$stmt->execute();

		$output = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if (!isset($output['match_id'])) {
				$output['match_id'] = $row['match_id'];
				$output['duration'] = $row['duration'];
				$output['winner'] = $row['winner'];
				$output['mode'] = $row['mode'];
				$output['league_id'] = $row['league_id'];
				$output['radiant_name'] = $row['radiant_name'];
				$output['dire_name'] = $row['dire_name'];
			}
			$output[] = array('name' => $row['name'],
												'en_name' => $row['en_name'],
												'position' => $row['position'],
												'kills' => $row['kills'],
												'deaths' => $row['deaths'],
												'assists' => $row['assists'],
												'level' => $row['level'],
												'kda' => $row['kda']);
		}
		$guessManager = new GuessManager();
		$guessManager->add($output['match_id'], $guess);
		$stats = $guessManager->getStats($output['match_id']);
		$output['stats'] = $stats;
	}
	catch (Exception $e) {
		die($e);
		Error::outputError("Failed to fetch result", $e->getMessage(), 1);
	}
}

if (count($output) === 0) {
	$output['error'][] = "No games found with publicId " . $publicId;
}


header('Content-Type: application/json');
echo json_encode($output);