<?php
require_once('config.php');
require_once('Error.php');

class PdoFactory {
	static private $pdoInstance;
		
	public static function getInstance($dsn = false, $user = false, $password = false, $driver_options = array()) {
		if (isset(self::$pdoInstance)) {
			return self::$pdoInstance;
		} else {
			try {
				self::$pdoInstance = new PDO($dsn, $user, $password, $driver_options);
				self::$pdoInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$pdoInstance->exec('SET NAMES utf8');
				return self::$pdoInstance;
			}
			catch(PDOException $e) {
				Error::outputError("Can't connect to database", $e->getMessage(), 1);
			}
		}	
	}
}