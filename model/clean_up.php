<?php
// Set full path to the directory
chdir(__DIR__);

require_once "../controller/config.php";
require_once "DFA.php";

use DFA\DFA as DFA;

// Path to direcrory and file-log
$pathLog = ".." . DIRECTORY_SEPARATOR . "Log";
$logName = $pathLog . DIRECTORY_SEPARATOR . date("Y-m-d") . ".txt";

// If the directory doesn't exist, create it
if (!is_dir($pathLog)) {
	if (mkdir($pathLog)) infoLog(DFA::remove($dbconf), $logName);
} else infoLog(DFA::remove($dbconf), $logName);

// logging
function infoLog($data, $path)
{
	if (!is_file($path)) {
		$file = fopen($path, 'w');
		fclose($file);
	}
	// Cleaning result
	$info = "Time: [" . date("H:i") . "]" . " | Removed [" . $data["count"] . "] | Size: " . $data['size'] . " Byte" . PHP_EOL;
	// Error record
	if (!empty($data["error"])) {
		// Location a mistake
		foreach ($data["error"] as $err) {
			$info .= PHP_EOL . " Error " . $err['code'] . " - " . $err['message'] . PHP_EOL . " Path: " . $err["path"] . PHP_EOL . " Files: [ ";
			// What file caused the error
			foreach ($err["file"] as $file) $info .= basename($file) . " ";
			$info .=  "]" . PHP_EOL . PHP_EOL;
		}
	}
	file_put_contents($path, $info, FILE_APPEND);
}
