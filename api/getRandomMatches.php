<?php
// we need to change working directory, in order to keep requires 
// working in classes and such :/
chdir('../');
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');
require_once(INC_PATH . 'class/Match.php');
require_once(INC_PATH . 'class/MatchManager.php');

$matchManager = new MatchManager();
$matches = $matchManager->getRandomMatches(10);

header('Content-Type: application/json');
echo json_encode($matches);