<?php

namespace DFA;

use Exception;

interface downloadFile
{
	public function downloadFile();
}

class Files implements downloadFile
{
	protected $listUrl = [];
	protected $files = [];
	protected $dirName = "";  // Dir name which storage files
	protected $root = "../TempFiles/"; // Root dir for storage dir with files
	private $reject = [];
	private $maxMemory = 104857600; // Max size for storage files
	private $sizef = 0;

	public function __construct($urlArr, $conf = ["memory" => null, "root" => "../TempFiles/"])
	{
		foreach ($urlArr as $url) {
			if ($this->validate($url)) array_push($this->listUrl, $url);
			else array_push($this->reject, ["url" => $url, "error" => ["code" => 23, "message" => "The link doesn't meet the requirements"]]);
		}
		if (isset($conf["memory"])) $this->maxMemory = $conf["memory"];
		if (isset($conf["root"])) $this->root = $conf["root"];
		$this->dirName = substr(md5(rand()), 0, 16);

		if (!is_dir($this->root)) {
			if (!mkdir($this->root)) throw new Exception("Root path error", 12);
		}
		mkdir($this->root . $this->dirName);
	}

	public function downloadFile()
	{
		if ($curl = curl_init()) {
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => $_SERVER['HTTP_USER_AGENT'],
			));
			foreach ($this->listUrl as $url) {
				if (in_array($url, $this->files) || in_array($url, $this->reject)) continue;
				$data = $this->dataFile($url);
				try {
					if ($data["status"] == 200 || ($data["status"] >= 300 && $data["status"] <= 308)) {
						curl_setopt($curl, CURLOPT_URL, $url); // Next file
						// Checking the file fits into the memory limit
						$this->sizef += $data['size'];
						if ($this->sizef <= $this->maxMemory) {
							if (!$file = curl_exec($curl)) throw new Exception("Error while get file",21);
							// Save file
							if (file_put_contents($this->root . $this->dirName . "/" . $data["name"], $file) === false) throw new Exception("Failure to writing of file", 22);
							// Add file to the list downloaded
							array_push($this->files, array_merge(["url" => $url], $data));
						} else throw new Exception("Out of memory.", 31);
					} else throw new Exception("Error while get file", 21);
				} catch (Exception $e) {
					// Add to the list of problem urls
					array_push($this->reject, array_merge(["url" => $url, "error" => ["code" => $e->getCode(), "message" => $e->getMessage()]], $data));
				}
			}
			curl_close($curl);
		}
	}

	public function getFilePath()
	{
		$result = [];
		foreach ($this->files as $value) {
			array_push($result, $this->root . $this->dirName . "/" . $value['name']);
		}
		return $result;
	}
	public function removeAllFiles()
	{
		foreach ($this->getFilePath() as $value) {
			if (unlink($value)) array_shift($this->files);
		}
	}
	protected function dataFile($url)
	{
		$status = false;
		$size = false;
		$name = false;
		$type = false;
		// Geting headers and parse stasus of response and file size
		if ($curl = curl_init($url)) {
			curl_setopt($curl, CURLOPT_NOBODY, true);
			curl_setopt($curl, CURLOPT_HEADER, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $_SERVER['HTTP_USER_AGENT']);

			$data = curl_exec($curl);
			curl_close($curl);

			// Status
			if (preg_match("~(?:HTTP\/.+?)(\d{3})~i", $data, $match)) $status = (int)$match[1];
			// Size file
			if (preg_match("~(?:content-length:\s)(\d+)~i", $data, $match)) $size = (int)$match[1];
			// Type of file
			if (preg_match("~(?:content-type: .*?/)(.*?)(?=\s)~i", $data, $match)) $type = $match[1];
			// File name
			if (preg_match("~(?:filename=)(.*?)(?=\s)~i", $data, $match)) $name = preg_replace("~[^\w\d\s\.]~", '', $match[1]);
			else $name = explode("?", basename($url))[0];
			// Check if the type is specified
			if (!preg_match("~(?:.*?\.)(\w+)$~", $name)) $name .= "." . $type;
			return ["name" => $name, "type" => $type, "size" => $size, "status" => $status];
		} else return ["name" => $name, "type" => $type, "size" => $size, "status" => $status];
	}
	// Check string for URL match
	private function validate($url)
	{
		return boolval(preg_match("~https?:(//|\{2}).*~", $url));
	}

	// Return rejected strings
	public function reject()
	{
		return $this->reject;
	}
	// Return processed of urls
	public function ready()
	{
		return $this->files;
	}
}
