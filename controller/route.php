<?php

function responsePhp($file)
{
	if (ob_get_level()) {
		ob_end_clean();
	}
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=' . basename($file));
	header("Content-Transfer-Encoding: binary");
	header("Expires: 0");
	header("Cache-Control: must-revalidate");
	header('Pragma: public');
	header('Content-Length: ' . filesize($file));
	exit(readfile($file));
}
function responseApache($file)
{
	if (file_exists($file)) {
		header("X-SendFile: " . realpath($file));
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . basename($file));
		header("Content-Transfer-Encoding: binary");
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		exit;
	} else http_response_code(404);
}

// Начало -------------------
require_once "config.php"; // Здесь $dbconf и $conf
require_once "../model/DFA.php";

use DFA\DFA as DFA;

try {
	// получаем json
	$data = json_decode(file_get_contents('php://input'), true);

	// данные из config.php
	$conf = array_merge($conf, [
		"name" => ((isset($data["name"])) ? $data["name"] : null),
		"encryption" => ((isset($data["encryption"])) ? $data["encryption"] : 3),
		"compress" => ((isset($data["compress"])) ? $data["compress"] : 4)
	]);

	// проверяем наличие ссылок или запросса скачать файл (приоритет)
	if (empty($data['url']) && empty($_GET['id'])) throw new Exception("Not Found Link or id", 11);
	// отдача на скачивание
	else if (!empty($_GET['id']))  responseApache(DFA::download($_GET['id'], $dbconf));
	// архивирование и отдача id архива с данными о результате скачивания
	else if (!empty($data['url'])) {
		$archive = new DFA($data["url"], array_merge($dbconf, $conf));
		$archive->downloadFile();
		// архивируем и готовим ответ
		$response = ["id" => $archive->prepareArchive(), "reject" => $archive->reject(), "ready" => $archive->ready()];
		// удаляем скаченные файлы
		$archive->removeAllFiles();
		echo json_encode($response);
	}
} catch (Exception $e) {
	echo json_encode(["error" => true, "code" => $e->getCode(), "message" => $e->getMessage()]);
}
