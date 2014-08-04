<?php
// we need to change working directory, in order to keep requires 
// working in classes and such :/
chdir('../');
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');
require_once(INC_PATH . 'class/Match.php');
require_once(INC_PATH . 'class/MatchManager.php');

$count = isset($_GET['count']) ? (int) $_GET['count'] : 10;
$matchType = $_GET['type'] === 'c' || $_GET['type'] === 'b' || $_GET['type'] === 'p' ? $_GET['type'] : 'b';

$matchManager = new MatchManager();
$matches = $matchManager->getRandomMatches($count, $matchType);

$output = array();
if (is_array($matches)) {
	foreach ($matches AS $m) {
		$players = array();
		foreach ($m->players AS $p) {
			// lets put players in order by position, 
			// easier to show them in correct order in frontend
			$index = $p['position'];
			if ($p['team'] === "r") {
				$index += 5;
			}
			$players[$index] = array('hero' => $p['hero_id'], 'team' => $p['team'], 'position' => $p['position']);			
		}
		$output[] = array('publicId' => $m->publicId, 'mmr' => $m->mmr, 'mode' => $m->mode, 'version' => $m->version, 'leagueId' => $m->leagueId, 'players' => $players);
	}
}

shuffle($output);

header('Content-Type: application/json');
echo json_encode($output);