<?php

/**
 * Configuration class for the Bingo Model
 *
 * @package Bingo-Model
 * @author Lochemem Bruno Michael <lochbm@gmail.com>
 */

namespace Chemem\Bingo\Model\Common;

class Config
{
    /**
     * Use callback variable
     * False returns an associative array
     * True returns a user generated associative array derived from callback (PDO::FETCH_FUNC)
     *
     * @see http://php.net/manual/en/pdostatement.fetchall.php
     * @var bool DB_CALLBACK_FETCH
     */

    const DB_CALLBACK_FETCH = false;

    /**
     * Database username parameter
     *
     * @var string DB_USER
     */

    const DB_USER = 'root';

    /**
     * Database name parameter
     *
     * @var string DB_NAME
     */

    const DB_NAME = '';

    /**
     * Database password parameter
     *
     * @var string DB_PASS
     */

    const DB_PASS = '';

    /**
     * Database host parameter
     *
     * @var string DB_HOST
     */

    const DB_HOST = 'localhost';
}
