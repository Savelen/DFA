<?php

namespace DFA;

use Exception;
use ZipArchive;

require_once "download_file.php";

interface archiveFile extends downloadFile
{
	public function archive();
}

class Archive extends Files implements archiveFile
{
	private $nameArh = ""; // имя архива
	private $archive;
	private $encryption;
	private $compress;

	public function __construct($urlArr, $conf = ["name" => null, "encryption" => 0, "compress" => 0])
	{
		if (!isset($conf["name"])) $this->nameArh = substr(md5(rand()), 0, 10);
		else	$this->nameArh = $conf["name"];
		parent::__construct($urlArr, $conf);
		$this->archive = new ZipArchive();
		// создание архива
		if ($this->archive->open($this->getArchivePath(), ZipArchive::CREATE) !== true) throw new Exception("Can't open Path");
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
		// сжатие
		switch ($conf['compress']) {
			case 0: {
					$this->compress = ZipArchive::CM_STORE;
					break;
				}
			case 1: {
					$this->compress = ZipArchive::CM_REDUCE_1;
					break;
				}
			case 2: {
					$this->compress = ZipArchive::CM_REDUCE_2;
					break;
				}
			case 3: {
					$this->compress = ZipArchive::CM_REDUCE_3;
					break;
				}
			case 4: {
					$this->compress = ZipArchive::CM_REDUCE_4;
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
			$this->archive->setCompressionName($value, $this->compress);  // сжатие
			$this->archive->setEncryptionName($value, $this->encryption); // Шифрование
		}
		$this->archive->close();

		return $this->getArchivePath();
	}
	public function getArchivePath()
	{
		return $this->root . $this->nameDir . '/' . $this->nameArh . ".zip";
	}
}
