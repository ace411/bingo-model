<?php

/**
 * TransactionTest class
 * Contains tests for the Bingo-Model Transaction class
 *
 * @package Bingo-Model
 * @author Lochemem Bruno Michael <lochbm@gmail.com>
 */

use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * A PDO object
     *
     * @access private
     * @var $pdo
     */

    private static $pdo = null;

    /**
     * A Bingo-Model Transaction object
     *
     * @access private
     * @var $conn
     */

    private $conn = null;

    /**
     * Set up the database connection
     *
     * @return object $conn The connection
     */

    public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
            }
            $this->conn = new Chemem\Bingo\Model\Transaction(self::$pdo);
        }
        return $this->conn;
    }

    /**
     * Tests whether transaction was successful or not
     */

    public function testTransactionSuccess()
    {
        $this->getConnection()->begin();
        $hello = $this->getConnection()->query()('SELECT "Hello"')()('fetch-all')();
        $world = $this->getConnection()->query()('SELECT "Hello"')()('fetch-all')();
        $valid = $this->getConnection()->validate();
        $this->getConnection()->commit();
        $this->assertTrue($valid);
    }

    /**
     * Tests whether transaction was rolled back or not
     */

    public function testTransactionRollback()
    {
        $this->getConnection()->begin();
        $hello = $this->getConnection()->query()('SELECT "Hello"')()('fetch-all')();
        $world = false;
        $result = $world === false ? $this->getConnection()->cancel() : $hello;
        $this->assertTrue($result);
    }
}
