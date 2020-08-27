<?php

chdir(__DIR__);

require_once "DFA.php";

use DFA\DFA as DFA;

// поть до папки и файла-лога
$pathLog = ".." . DIRECTORY_SEPARATOR . "Log";
$logName = $pathLog . DIRECTORY_SEPARATOR . date("Y-m-d") . ".txt";
// для подключения к бд
$dbconf = [
	"host" => "project.php",
	"dbname" => "dfa",
	"tableName" => "archive",
	"username" => "root",
	"passwd" => ""
];
// создаём папку если её нет
if (!is_dir($pathLog)) {
	if (mkdir($pathLog)) infoLog(DFA::remove($dbconf), $logName);
} else infoLog(DFA::remove($dbconf), $logName);

// логируем
function infoLog($data, $path)
{
	if (!is_file($path)) {
		$file = fopen($path, 'w');
		fclose($file);
	}
	$info = "Time: [" . date("H:i") . "]" . " - Removed [" . $data["count"] . "] files - Size: " . $data['size'] . " Byte". PHP_EOL;
	file_put_contents($path, $info, FILE_APPEND);
}
