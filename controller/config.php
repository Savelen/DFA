<?php
// DB connect
$dbconf = [
	"host" => "localhost",
	"dbname" => "dfa",
	"tableName" => "archive",
	"username" => "root",
	"passwd" => ""
];
// Work configuration
$conf = [
	"memory" => null,
	"root" => null,
	"log" => ".." . DIRECTORY_SEPARATOR . "Log",
	"encryption" => 3,
	"compress" => 4,
	"live" => 1
];

/**
 *  0 - APACHE
 *  1 - PHP
 */
define("DOWNLOAD_METHOD", 0);
