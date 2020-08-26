<?php

namespace DFA;

use Exception;
use PDO;
use PDOException;
use FilesystemIterator;

require_once "archive.php";

interface dataManager extends archiveFiles
{
	public function prepareArchive();
	static public function dbconnect($host, $dbname, $username, $passwd);
	static public function download(string $id, array $conf = []);
	static public function remove(array $conf = [], $removeAll = false);
}

class DFA extends Archive implements dataManager
{
	public $id;
	private $pdo;
	private $tableName;
	public $minLive;

	public function __construct($urlArr, $conf = ["host" => 'localhost', "dbname" => "dfa", "tableName" => "archihve", "username" => "", "passwd" => "", "live" => 1])
	{
		parent::__construct($urlArr, $conf);
		$this->pdo = self::dbconnect($conf['host'], $conf["dbname"], $conf["username"], $conf["passwd"]);
		$this->minLive = $conf["live"];
		$this->tableName = $conf["tableName"];
	}
	public function prepareArchive()
	{
		// акхивируем файлы
		$result = $this->archive();
		if ($result["result"] === true) {
			// добавляем запись в бд. (id_archive,date,path)
			$id_archive = substr(md5(rand()), 0, 16);
			try {
				$prepare = $this->pdo->prepare("INSERT INTO " . $this->tableName . " (`id_archive`,`date`,`path`) VALUES (:id_archive,:date,:path)");
				if (!$prepare->execute(["id_archive" => $id_archive, "date" => (time() + $this->minLive), "path" => $this->path])) {
					throw new Exception("Error add record", 43);
				}
			} catch (PDOException $e) {
				throw new Exception("Error while preparing request", 42);
			} catch (Exception $e) {
				throw new Exception($e->getMessage(), $e->getCode());
			}
			return $id_archive;
		} else throw new Exception("Arhive files is failed: " . $result['massage'], $result['code']);
	}
	static public function dbconnect($host, $dbname, $username, $passwd)
	{
		try {
			// подключение к бд
			$pdo  = new PDO(
				"mysql:host=" . $host . ";dbname=" . $dbname,
				$username,
				$passwd
			);
		} catch (PDOException $e) {
			throw new Exception('Connection failed: ' . $e->getMessage(), 401);
		}
		return $pdo;
	}
	static public function download($id, $conf = ["host" => 'localhost', "dbname" => "dfa", "tableName" => "archihve", "username" => "", "passwd" => ""])
	{
		$pdo = self::dbconnect($conf['host'], $conf["dbname"], $conf["username"], $conf["passwd"]);
		try {
			// запрос к бд. Ищем по id путь к архиву
			$prepare = $pdo->prepare("SELECT `path` FROM " . $conf["tableName"] . " WHERE id_archive = :id_archive");
			if (!$prepare->execute(["id_archive" => $id])) {
				throw new Exception("Error while geting data", 404);
			}
			// возвращаем путь
			return $prepare->fetch(PDO::FETCH_ASSOC)["path"];
		} catch (PDOException $e) {
			throw new Exception("Error while preparing request: " . $e->getMessage(), 402);
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), $e->getCode());
		}
	}
	static public function remove($conf = ["host" => 'localhost', "dbname" => "dfa", "tableName" => "archihve", "username" => "", "passwd" => ""], $removeAll = false)
	{
		$pdo = self::dbconnect($conf['host'], $conf["dbname"], $conf["username"], $conf["passwd"]);
		try {
			$time = time();
			// получаем данные о файлах с истёкшим сроком жизни
			$prepare = $pdo->prepare("SELECT `path` FROM " . $conf["tableName"] . " WHERE `date` < " . $time );
			if (!$prepare->execute()) {
				throw new Exception("Error while geting data", 44);
			}
			$arch_list = $prepare->fetchAll(PDO::FETCH_ASSOC);
			// удаляем их
			foreach ($arch_list as $file) {
				// получаем путь до папки с файлом
				$dir = dirname($file['path']); // !------------------ add function realpath();
				if (file_exists($dir)) {
					// удаляем папку и всё содержимое
					DFA::removeDir($dir);
				}
			}
			// удаляем из базы данных записи о файлах
			$prepare = $pdo->prepare("DELETE FROM " . $conf["tableName"] . " WHERE `date` < " . $time);
			if (!$prepare->execute()) {
				throw new Exception("Error while geting data", 44);
			}
			// возвращаем массив удалённых файлов
			return $arch_list;
		} catch (PDOException $e) {
			throw new Exception("Error while preparing request: " . $e->getMessage(), 42);
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), $e->getCode());
		}
	}
	// удаляет папку с сожержимым
	static private function removeDir($dir)
	{
		$includes = new FilesystemIterator($dir);
		foreach ($includes as $include) {
			if (is_dir($include) && !is_link($include)) {
				self::removeDir($include);
			} else {
				unlink($include);
			}
		}
		rmdir($dir);
	}
}
