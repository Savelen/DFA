<?php
// DB connect
$dbconf = [
	"host" => "project.php",
	"dbname" => "dfa",
	"tableName" => "archive",
	"username" => "root",
	"passwd" => ""
];
// Work configuration
$conf = [
	"memory" => (null),
	"root" => (null),
	"encryption" => (3),
	"compress" => (4),
	"live" => 1200
];

/**
 *  0 - APACHE
 *  1 - PHP
 */
define("DOWNLOAD_METHOD",0);