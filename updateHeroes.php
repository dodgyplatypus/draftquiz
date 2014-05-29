<?php
$heroDetails["antimage"]["attr"] = "agi";
$heroDetails["axe"]["attr"] = "str";
$heroDetails["bane"]["attr"] = "int";
$heroDetails["bloodseeker"]["attr"] = "agi";
$heroDetails["crystal_maiden"]["attr"] = "int";
$heroDetails["drow_ranger"]["attr"] = "agi";
$heroDetails["earthshaker"]["attr"] = "str";
$heroDetails["juggernaut"]["attr"] = "agi";
$heroDetails["mirana"]["attr"] = "agi";
$heroDetails["morphling"]["attr"] = "agi";
$heroDetails["nevermore"]["attr"] = "agi";
$heroDetails["phantom_lancer"]["attr"] = "agi";
$heroDetails["puck"]["attr"] = "int";
$heroDetails["pudge"]["attr"] = "str";
$heroDetails["razor"]["attr"] = "agi";
$heroDetails["sand_king"]["attr"] = "int";
$heroDetails["storm_spirit"]["attr"] = "int";
$heroDetails["sven"]["attr"] = "atr";
$heroDetails["tiny"]["attr"] = "str";
$heroDetails["vengefulspirit"]["attr"] = "int";
$heroDetails["windrunner"]["attr"] = "int";
$heroDetails["zuus"]["attr"] = "int";
$heroDetails["kunkka"]["attr"] = "str";
$heroDetails["lina"]["attr"] = "int";
$heroDetails["lion"]["attr"] = "int";
$heroDetails["shadow_shaman"]["attr"] = "int";
$heroDetails["slardar"]["attr"] = "str";
$heroDetails["tidehunter"]["attr"] = "str";
$heroDetails["witch_doctor"]["attr"] = "int";
$heroDetails["lich"]["attr"] = "int";
$heroDetails["riki"]["attr"] = "agi";
$heroDetails["enigma"]["attr"] = "int";
$heroDetails["tinker"]["attr"] = "int";
$heroDetails["sniper"]["attr"] = "agi";
$heroDetails["necrolyte"]["attr"] = "int";
$heroDetails["warlock"]["attr"] = "int";
$heroDetails["beastmaster"]["attr"] = "str";
$heroDetails["queenofpain"]["attr"] = "int";
$heroDetails["venomancer"]["attr"] = "agi";
$heroDetails["faceless_void"]["attr"] = "agi";
$heroDetails["skeleton_king"]["attr"] = "str";
$heroDetails["death_prophet"]["attr"] = "int";
$heroDetails["phantom_assassin"]["attr"] = "agi";
$heroDetails["pugna"]["attr"] = "int";
$heroDetails["templar_assassin"]["attr"] = "agi";
$heroDetails["viper"]["attr"] = "agi";
$heroDetails["luna"]["attr"] = "agi";
$heroDetails["dragon_knight"]["attr"] = "str";
$heroDetails["dazzle"]["attr"] = "int";
$heroDetails["rattletrap"]["attr"] = "str";
$heroDetails["leshrac"]["attr"] = "int";
$heroDetails["furion"]["attr"] = "int";
$heroDetails["life_stealer"]["attr"] = "str";
$heroDetails["dark_seer"]["attr"] = "int";
$heroDetails["clinkz"]["attr"] = "agi";
$heroDetails["omniknight"]["attr"] = "str";
$heroDetails["enchantress"]["attr"] = "int";
$heroDetails["huskar"]["attr"] = "str";
$heroDetails["night_stalker"]["attr"] = "str";
$heroDetails["broodmother"]["attr"] = "agi";
$heroDetails["bounty_hunter"]["attr"] = "agi";
$heroDetails["weaver"]["attr"] = "agi";
$heroDetails["jakiro"]["attr"] = "int";
$heroDetails["batrider"]["attr"] = "int";
$heroDetails["chen"]["attr"] = "int";
$heroDetails["spectre"]["attr"] = "agi";
$heroDetails["ancient_apparition"]["attr"] = "int";
$heroDetails["doom_bringer"]["attr"] = "str";
$heroDetails["ursa"]["attr"] = "agi";
$heroDetails["spirit_breaker"]["attr"] = "str";
$heroDetails["gyrocopter"]["attr"] = "agi";
$heroDetails["alchemist"]["attr"] = "str";
$heroDetails["invoker"]["attr"] = "int";
$heroDetails["silencer"]["attr"] = "int";
$heroDetails["obsidian_destroyer"]["attr"] = "int";
$heroDetails["lycan"]["attr"] = "str";
$heroDetails["brewmaster"]["attr"] = "str";
$heroDetails["shadow_demon"]["attr"] = "int";
$heroDetails["lone_druid"]["attr"] = "str";
$heroDetails["chaos_knight"]["attr"] = "str";
$heroDetails["meepo"]["attr"] = "agi";
$heroDetails["treant"]["attr"] = "str";
$heroDetails["ogre_magi"]["attr"] = "int";
$heroDetails["undying"]["attr"] = "str";
$heroDetails["rubick"]["attr"] = "int";
$heroDetails["disruptor"]["attr"] = "int";
$heroDetails["nyx_assassin"]["attr"] = "int";
$heroDetails["naga_siren"]["attr"] = "agi";
$heroDetails["keeper_of_the_light"]["attr"] = "int";
$heroDetails["wisp"]["attr"] = "int";
$heroDetails["visage"]["attr"] = "int";
$heroDetails["slark"]["attr"] = "agi";
$heroDetails["medusa"]["attr"] = "agi";
$heroDetails["troll_warlord"]["attr"] = "agi";
$heroDetails["centaur"]["attr"] = "str";
$heroDetails["magnataur"]["attr"] = "str";
$heroDetails["shredder"]["attr"] = "str";
$heroDetails["bristleback"]["attr"] = "str";
$heroDetails["tusk"]["attr"] = "str";
$heroDetails["skywrath_mage"]["attr"] = "int";
$heroDetails["abaddon"]["attr"] = "str";
$heroDetails["elder_titan"]["attr"] = "str";
$heroDetails["legion_commander"]["attr"] = "agi";
$heroDetails["ember_spirit"]["attr"] = "agi";
$heroDetails["earth_spirit"]["attr"] = "int";
$heroDetails["terrorblade"]["attr"] = "agi";
$heroDetails["phoenix"]["attr"] = "str";

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
	$data[] = $heroDetails[str_replace('npc_dota_hero_', '', $hero['name'])]['attr'];
}

// create place holders
$values = array();
for($i = 0; $i < count($heroes['result']['heroes']); $i++) {
	$values[] = '(?, ?, ?, ?)';
}
$values = implode(', ', $values);

// execute query
$db->beginTransaction();
try {
	$sql = 'INSERT INTO ' . DB_TABLE_PREFIX . 'hero (id, name, en_name, attr) VALUES ' . $values . ' ON DUPLICATE KEY UPDATE name = VALUES(name), en_name = VALUES(en_name), attr = VALUES(attr)';
	$stmt = $db->prepare($sql);
	$stmt->execute($data);
	$db->commit();
}
catch(PDOException $e) {
	$db->rollBack();
	Error::outputError("Failed to insert hero data to database", $e->getMessage(), 1);
}