<?php
// устанавливаем полный путь до директории
chdir(__DIR__);

require_once "config.php";
require_once "DFA.php";

use DFA\DFA as DFA;

// путь до папки и файла-лога
$pathLog = ".." . DIRECTORY_SEPARATOR . "Log";
$logName = $pathLog . DIRECTORY_SEPARATOR . date("Y-m-d") . ".txt";

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
	// результат очистки
	$info = "Time: [" . date("H:i") . "]" . " | Removed [" . $data["count"] . "] | Size: " . $data['size'] . " Byte" . PHP_EOL;
	// запись об ошибке
	if (!empty($data["error"])) {
		// в каких папках была ошибка
		foreach ($data["error"] as $err) {
			$info .= PHP_EOL . " Error " . $err['code'] . " - " . $err['message'] . PHP_EOL . " Path: " . $err["path"] . PHP_EOL . " Files: [ ";
			// какие файлы вызвали ошибку
			foreach ($err["file"] as $file) $info .= basename($file) . " ";
			$info .=  "]" . PHP_EOL . PHP_EOL;
		}
	}
	file_put_contents($path, $info, FILE_APPEND);
}
