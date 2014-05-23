<?php

// get settings
require_once('config.php');

// create connection handle
try {
	$db = new PDO(DB_CONNECTION, DB_USER, DB_PW, array());
}
catch(PDOException $e) {
	die('ERROR: ' . $e->getMessage());
}

// configure connection
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('SET NAMES utf8');

// get new hero data
$json = file_get_contents('https://api.steampowered.com/IEconDOTA2_570/GetHeroes/v0001/?key=' . API_KEY . '&language=en_us');
$heroes = json_decode($json, true);

// create insert data
$data = array();
foreach($heroes['result']['heroes'] as $hero) {
	$data[] = $hero['id'];
	$data[] = $hero['localized_name'];
}

// create place holders
$values = array();
for($i = 0; $i < count($heroes['result']['heroes']); $i++) {
	$values[] = '(?, ?)';
}
$values = implode(', ', $values);

// execute query
$db->beginTransaction();
try {
	$sql = 'INSERT INTO ' . DB_TABLE_PREFIX . 'hero (id, name) VALUES ' . $values . ' ON DUPLICATE KEY UPDATE name = VALUES(name)';
	$stmt = $db->prepare($sql);
	$stmt->execute($data);
	$db->commit();
}
catch(PDOException $e) {
	$db->rollBack();
	echo 'ERROR: ' . $e->getMessage();
}