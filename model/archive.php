<?php

namespace DFA;

use Exception;
use ZipArchive;

require_once "download_file.php";

interface archiveFiles extends downloadFile
{
	public function archive();
}

class Archive extends Files implements archiveFiles
{
	private $nameArch = ""; // Archive name
	private $archive;
	private $encryption;
	private $compress;
	protected $path; // Full path to the archive

	public function __construct($urlArr, $conf = ["name" => null, "encryption" => 0, "compress" => 0])
	{
		// Archive name
		if (empty($conf["name"])) $this->nameArch = substr(md5(rand()), 0, 10) . ".zip";
		else {
			// first symbol sould match  regexp /\w/
			preg_match('/\w/', $conf["name"][0], $chr);
			// If first character doesn't match  regexp "\w" then add  "_" to the beginning
			$this->nameArch = (empty($chr) ? "_" : "") . preg_replace("~[\s!@#$%^&*()[\]{}`\~+№;:?|/\\\]~", "_", $conf["name"]) . ".zip";
		}
		parent::__construct($urlArr, $conf);
		$this->archive = new ZipArchive();
		// Archive creation
		$this->path =  $this->root . $this->dirName . '/' . $this->nameArch;
		if ($this->archive->open($this->path, ZipArchive::CREATE) !== true) throw new Exception("Can't open Path", 13);
		// encrypt
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
		// compress
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
		try {
			foreach ($this->getFilePath() as $value) {
				$this->archive->addFile($value, basename($value));
				$this->archive->setCompressionName($value, $this->compress);  // compress
				$this->archive->setEncryptionName($value, $this->encryption); // encryption
			}
			$this->archive->close();
			return ["result" => true];
		} catch (Exception $e) {
			return ["result" => false, "massage" => $e->getMessage(), "code" => 14];
		}
	}
}
