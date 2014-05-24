<?php

// get settings
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');

// get connection handle
$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);

// get new hero data
$json = file_get_contents('https://api.steampowered.com/IEconDOTA2_570/GetHeroes/v0001/?key=' . API_KEY . '&language=en_us');
$heroes = json_decode($json, true);

// create insert data
$data = array();
foreach($heroes['result']['heroes'] as $hero) {
	$data[] = $hero['id'];
	$data[] = str_replace('npc_dota_hero_', '', $hero['name']);
	$data[] = $hero['localized_name'];
}

// create place holders
$values = array();
for($i = 0; $i < count($heroes['result']['heroes']); $i++) {
	$values[] = '(?, ?, ?)';
}
$values = implode(', ', $values);

// execute query
$db->beginTransaction();
try {
	$sql = 'INSERT INTO ' . DB_TABLE_PREFIX . 'hero (id, name, en_name) VALUES ' . $values . ' ON DUPLICATE KEY UPDATE name = VALUES(name), en_name = VALUES(en_name)';
	$stmt = $db->prepare($sql);
	$stmt->execute($data);
	$db->commit();
}
catch(PDOException $e) {
	$db->rollBack();
	Error::outputError("Failed to insert hero data to database", $e->getMessage(), 1);
}