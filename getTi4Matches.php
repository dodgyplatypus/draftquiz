<?php
/**
 * For some pervert reason we can't get TI4 main event games (starting from playoffs) from getMatchHistory API
 * But we get them from GetTournamentPlayerStats -api
 * Thanks to http://dota2statistic.com/index.php/blog/7 for solution, and team ids!
 */
require_once('config.php');
require_once(INC_PATH . 'class/PdoFactory.php');
require_once(INC_PATH . 'class/Match.php');
require_once(INC_PATH . 'class/MatchManager.php');

echo '<pre>';

$db = PdoFactory::getInstance(DB_CONNECTION, DB_USER, DB_PW);

$teams = array(
'101495620', // Alliance
'94362277', // Titan
'87276347', // EG
'100317750', // Fnatic
'100883708', // Newbee
'91698091', // Vici
'70388657', // Na'Vi
'90892734', // DK
'88553213', // iG
'19757254', // Cloud 9
'89269794', // Empire
'1185644', // Na'Vi US
'131380551', // Arrow
'123854991', // LGD
'87285329', // mouz
'86738694', // Liquid
'88933594', // MVP
'21289303', // CIS
'36547811' // VP
);

foreach ($teams AS $teamId) {
	echo "Starting team $teamId\n";
	$json = file_get_contents('http://api.steampowered.com/IDOTA2Match_570/GetTournamentPlayerStats/v1/?account_id=' . $teamId . '&league_id=600&key=' . API_KEY);
	$json = json_decode($json, true);
	foreach ($json['result']['matches'] AS $m) {
		$match = new Match($m['match_id']);
		$match->fetchFromApi();
		echo "Saved match {$m['match_id']}\n";
	}
}