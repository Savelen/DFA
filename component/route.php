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
	if (empty($data['url']) && !isset($data['donwload'])) throw new Exception("Not Found Link", 101);
	else if (isset($data['donwload']) ? ($data['download'] == true) : false) {
		echo json_encode(["url" => Archive::getArchivePath($data["id"])]);
	}
	else {
		$archive = new Archive($data["url"], ["name"=>(isset($data["name"]))?$data["name"]: false]);
		$archive->downloadFile();
		$archive->archive();
	}
} catch (Exception $e) {
	echo json_encode(["error" => true, "code" => $e->getCode(), "message" => $e->getMessage()]);
}
