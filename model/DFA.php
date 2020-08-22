<?php

namespace DFA;

use Exception;
use PDO;

interface dataManager extends archiveFile
{
	public function getArchive();
	static public function download(string $id);
}

class DFA extends Archive implements dataManager
{
	public $id;
	private $pdo;
	public function __construct($urlArr, $conf)
	{
		parent::__construct($urlArr, $conf);
		$this->pdo  = new PDO(`mysql:host=${$conf['host']};dbname=${$conf["dbname"]}`, $conf["username"], $conf["passwd"]);
	}
	public function getArchive()
	{
	}
	static public function download($id)
	{
	}
}
