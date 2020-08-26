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
require_once "../model/DFA.php";

use DFA\DFA as DFA;

try {
	// получаем json
	$data = json_decode(file_get_contents('php://input'), true);
	// подключение к бд
	$dbconf = [
		"host" => "project.php",
		"dbname" => "dfa",
		"tableName" => "archive",
		"username" => "root",
		"passwd" => ""
	];
	// конфигурачия работы
	$conf = [
		"name" => ((isset($data["name"])) ? $data["name"] : null),
		"memory" => ((isset($data["memory"])) ? $data["memory"] : null),
		"root" => ((isset($data["root"])) ? $data["root"] : null),
		"encryption" => ((isset($data["encryption"])) ? $data["encryption"] : 3),
		"compress" => ((isset($data["compress"])) ? $data["compress"] : 4),
		"live" => 1200
	];
	// проверяем наличие ссылок или запросса скачать файл (приоритет)
	if (empty($data['url']) && (empty($data["download"]) && empty($_GET['id']))) throw new Exception("Not Found Link", 11);
	else if (isset($data["download"]) || !empty($_GET['id'])) {
		if (!empty($data["id"]) || !empty($_GET['id'])) responseApache(DFA::download((!empty($data["id"]) ? $data["id"] : $_GET['id']), $dbconf));
		else throw new Exception("Empty id", 45);
	} else {
		// конфигурация
		$archive = new DFA($data["url"], array_merge($dbconf, $conf));
		$archive->downloadFile();
		$response = ["id" => $archive->prepareArchive(), "reject" => $archive->reject(), "ready" => $archive->ready()];
		$archive->removeAllFiles();
		echo json_encode($response);
	}
} catch (Exception $e) {
	echo json_encode(["error" => true, "code" => $e->getCode(), "message" => $e->getMessage()]);
}
