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
require_once "../model/download_file.php";

use DFA\Files as Files;

try {
	// получаем json
	$data = json_decode(file_get_contents('php://input'), true);
	if (empty($data['url'])) throw new Exception("Not Found Link", 101);
	else {
		$files = new Files($data["url"], ["maxSize" => 2147483648]);
		$files->downloadFile();
		echo json_encode(["url" => $files->getFilePath()[0]]);
		// $files->getp();
	}
} catch (Exception $e) {
	echo json_encode(["error" => true, "code" => $e->getCode(), "message" => $e->getMessage()]);
}
