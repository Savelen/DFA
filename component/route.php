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
		header("X-SendFile: " . $file);
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . basename($file));
		header("Content-Transfer-Encoding: binary");
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		exit;
	} else http_response_code(404);
}

// Начало -------------------
require_once "../model/archive.php";

use DFA\Archive as Archive;

try {
	// получаем json
	$data = json_decode(file_get_contents('php://input'), true);
	// проверяем наличие ссылок или запросса скачать файл (приоритет)
	if (empty($data['url'])) throw new Exception("Not Found Link", 101);
	else {
		// конфигурация
		$archive = new Archive($data["url"], array(
			"name" => ((isset($data["name"])) ? $data["name"] : null),
			"memory" => ((isset($data["memory"])) ? $data["memory"] : null),
			"root" => ((isset($data["root"])) ? $data["root"] : null),
			"encryption" => ((isset($data["encryption"])) ? $data["encryption"] : 3),
			"compress" => ((isset($data["compress"])) ? $data["compress"] : 4)
		));
		$archive->downloadFile();
		$response = ["url" => $archive->archive(), "reject" => $archive->reject(), "ready" => $archive->ready()];
		$archive->removeAllFiles();
		echo json_encode($response);
	}
} catch (Exception $e) {
	echo json_encode(["error" => true, "code" => $e->getCode(), "message" => $e->getMessage()]);
}
