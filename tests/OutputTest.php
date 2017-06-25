<?php

/**
 * OutputTest class
 * Contains tests for the Bingo-Model Output class
 *
 * @package Bingo-Model
 * @author Lochemem Bruno Michael <lochbm@gmail.com>
 */

use PHPUnit\Framework\TestCase;

class OutputTest extends TestCase
{
    /**
     * A PDO object
     *
     * @access private
     * @var $pdo
     */

    private static $pdo = null;

    /**
     * A Bingo-Model Query object
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
            $this->conn = new Chemem\Bingo\Model\Query(self::$pdo);
        }
        return $this->conn;
    }

    /**
     * Tests whether the Output class data is the same as the database data returned
     */

    public function testOutputInstanceValidity()
    {
        $condition = $this->getConnection()
            ->condition()
            ("WHERE blog_title LIKE :title");
        $queryString = $this->getConnection()
            ->select()
            (['blog_title, blog_id'])
            ('dummy_posts')
            ($condition);
        $query = $this->getConnection()
            ->query()
            ($queryString)
            ([
                ['param' => ':title', 'value' => '%cool%', 'type' => PDO::PARAM_STR]
            ])
            ('fetch-all')
            ();
        $output = new Chemem\Bingo\Model\Output($query);
        return $this->assertEquals($output->getData(), $query);
    }

    /**
     * Tests whether the json() method returns a json-encoded string
     */

    public function testJsonEncode()
    {
        $query = $this->getConnection()->query()('SELECT "Hello World"')()('fetch-all')();
        $jsonData = (new Chemem\Bingo\Model\Output($query))->json();
        return $this->assertEquals($jsonData, json_encode($query));
    }

    /**
     * Tests whether the map function returns a certain value once used
     */

    public function testOutputMap()
    {
        $query = $this->getConnection()->query()('SELECT "Hello World"')()('fetch-all')();
        $output = (new Chemem\Bingo\Model\Output($query))->map(function (array $values) {
            return array_keys($values);
        });
        return $this->assertTrue(count($output) > 0);
    }
}
