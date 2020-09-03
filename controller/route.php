<?php

/**
 * answer by PHP (readfile)
 * $file - path to file
 */
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
/**
 * Answer by X-SendFile (Apache)
 * $file - path to file
 */
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
require_once "config.php"; // Here $dbconf and $conf
require_once "../model/DFA.php";

use DFA\DFA as DFA;

try {
	// Get json
	$data = json_decode(file_get_contents('php://input'), true);

	// Data from config.php + user data
	$conf = array_merge($conf, ["name" => ((isset($data["name"])) ? $data["name"] : null)]);

	// check existense of url or request for download file (priority)
	if (empty($data['url']) && empty($_GET['id'])) throw new Exception("Not Found Link or id", 11);
	// Sending file
	else if (!empty($_GET['id'])) {
		switch (DOWNLOAD_METHOD) {
			case 0:
				responseApache(DFA::download($_GET['id'], $dbconf));
				break;
			case 1:
				responsePHP(DFA::download($_GET['id'], $dbconf));
				break;
			default:
				break;
		}
	}
	// Archiveing and return data with the result of download and archive id
	else if (!empty($data['url'])) {
		$archive = new DFA($data["url"], array_merge($dbconf, $conf));
		$archive->downloadFile();
		// Archiveing and prepare answer
		$response = ["id" => $archive->prepareArchive(), "reject" => $archive->reject(), "ready" => $archive->ready()];
		// Remove downloaded files
		$archive->removeAllFiles();
		echo json_encode($response);
	}
} catch (Exception $e) {
	echo json_encode(["error" => true, "code" => $e->getCode(), "message" => $e->getMessage()]);
}
