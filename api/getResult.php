<?php
// we need to change working directory, in order to keep requires 
// working in classes and such :/
chdir('../');
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');
require_once(INC_PATH . 'class/Error.php');

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
		$stmt = $db->prepare('SELECT match_id, duration, winner, mode FROM `' . DB_TABLE_PREFIX . 'match` WHERE public_id = ?');
		$stmt->execute(array($publicId));

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$output = $row;
		}
	}
	catch (Exception $e) {
		Error::outputError("Failed to fetch result", $e->getMessage(), 1);
	}
}

if (count($output) === 0) {
	$output['error'][] = "No games found with publicId " . $publicId;
}

header('Content-Type: application/json');
echo json_encode($output);