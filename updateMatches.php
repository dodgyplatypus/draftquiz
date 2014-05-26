<?php

// get settings
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');
require_once(INC_PATH . 'class/MatchList.php');

// work in progress, will change a lot
$matchList = new MatchList;
$matchList->fetchFromApi(25);