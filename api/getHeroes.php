<?php
// we need to change working directory, in order to keep requires 
// working in classes and such :/
chdir('../');
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');

$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);

$stmt = $db->prepare('SELECT id, name, en_name FROM ' . DB_TABLE_PREFIX . 'hero');
$stmt->execute();

$output = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$row['image'] = URL_ROOT . 'images/heroportraits/' . $row['name'] . ".png";
	$output[] = $row;
}

header('Content-Type: application/json');
echo json_encode($output);