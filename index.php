<?php

require __DIR__ . '/vendor/autoload.php';

use Chemem\Bingo\Model\Query;
use Chemem\Bingo\Model\Common\Config;

$pdo = new PDO(
    "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=utf8",
    Config::DB_USER,
    Config::DB_PASS,
    [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_PERSISTENT => true
    ]
);

$query = (new Query($pdo))->query()('SELECT "Hello World"')()('fetch')();

var_dump($query);
