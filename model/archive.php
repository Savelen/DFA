<?php

namespace DFA;

use Exception;
use ZipArchive;

require_once "download_file.php";

interface archiveFile extends downloadFile
{
	public function archive();
	public function updArc();
	static public function getArchivePath($id);
}

class Archive extends Files implements archiveFile
{
	private $nameArh = ""; // имя архива
	private $archive;
	private $encryption;

	public function __construct($urlArr, $conf = ["maxSize" => 104857600, "root" => "../TempFiles/", "name" => false, "encryption" => 0])
	{
		if ($conf["name"] == false) $this->nameArh = substr(md5(rand()), 0, 10);
		else	$this->nameArh = $conf["name"];
		parent::__construct($urlArr, $conf);
		$this->archive = new ZipArchive();
		if ($this->archive->open($this->root . $this->nameDir . '/' . $this->nameArh . ".zip", ZipArchive::CREATE) !== true) throw new Exception("Can't open Path");
		// Шифрование
		switch ($conf["encryption"]) {
			case 0: {
					$this->encryption = ZipArchive::EM_NONE;
					break;
				}
			case 1: {
					$this->encryption = ZipArchive::EM_AES_128;
					break;
				}
			case 2: {
					$this->encryption = ZipArchive::EM_AES_192;
					break;
				}
			case 3: {
					$this->encryption = ZipArchive::EM_AES_256;
					break;
				}
			default:
				break;
		}
	}
	public function archive()
	{
		foreach ($this->getFilePath() as $value) {
			$this->archive->addFile($value, basename($value));
			$this->archive->setCompressionName($value, ZipArchive::CM_STORE);
			$this->archive->setEncryptionName($value, $this->encryption);
		}
		$this->archive->close();
		$this->removeAllFiles();
	}
	public function updArc()
	{
	}
	static public function getArchivePath($id)
	{
	}
}
