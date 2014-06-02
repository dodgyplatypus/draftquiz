<?php

// get settings
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');
require_once(INC_PATH . 'class/MatchManager.php');

// work in progress, will change a lot
$matchManager = new MatchManager;
$matchManager->fetchFromApi(50);