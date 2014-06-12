<?php

class Error {

	// first argument is outputted to user, second is put to PHP error handler
	public static function outputError($userOutput, $exception = false, $fatal = 0) {
		print "Error: " . $userOutput;
		if ($fatal > 0) { $fatal = E_USER_ERROR; } 
		else { $fatal = E_USER_NOTICE; }
		
		trigger_error($userOutput . ", details: " . $exception->getMessage() . ", trace: " . print_r($exception->getTrace(), true), $fatal);
	}

}