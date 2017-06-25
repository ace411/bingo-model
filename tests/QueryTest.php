<?php

/**
 * QueryTest class
 * Contains tests for the Bingo-Model Query class
 *
 * @package Bingo-Model
 * @author Lochemem Bruno Michael <lochbm@gmail.com>
 */

use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
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
     * Template for the test join statements
     *
     * @return callable $query->join() The join statement
     */

    private function joinTemplate()
    {
        return $this->getConnection()
            ->join()
            ('blog')
            ('tokens')
            ([
                ['blog_text', 'token_id'],
                ['token_id', 'token_string']
            ]);
    }

    /**
     * Tests whether the Query database instance is the same as the PDO instance
     */

    public function testInstanceValidity()
    {
        return $this->assertEquals($this->getConnection()->get(), self::$pdo);
    }

    /**
     * Tests whether the WHERE condition is valid
     */

    public function testConditionClause()
    {
        $condition = $this->getConnection()
            ->condition()
            ("WHERE blog_id = :id");
        return $this->assertEquals($condition, "WHERE blog_id = :id");
    }

    /**
     * Tests whether the SELECT statement is valid
     */

    public function testSelectStmt()
    {
        $select = $this->getConnection()
            ->select()
            (['blog_title', 'blog_id'])
            ('dummy_posts')
            ('WHERE blog_title = :title');
        return $this->assertEquals($select, "SELECT blog_title, blog_id FROM dummy_posts WHERE blog_title = :title");
    }

    /**
     * Tests whether the INSERT statement is valid
     */

    public function testInsertStmt()
    {
        $insert = $this->getConnection()
            ->insert()
            ('dummy_posts')
            (['blog_id', 'blog_title'])
            ([':id', ':title']);
        return $this->assertEquals($insert, "INSERT INTO dummy_posts(blog_id, blog_title) VALUES (:id, :title)");
    }

    /**
     * Tests whether the UPDATE statement is valid
     */

    public function testUpdateStmt()
    {
        $update = $this->getConnection()
            ->update()
            ('dummy_posts')
            ('blog_title = :title');
        return $this->assertEquals($update, "UPDATE dummy_posts SET blog_title = :title");
    }

    /**
     * Tests whether the DELETE statement is valid
     */

    public function testDeleteStmt()
    {
        $delete = $this->getConnection()
            ->delete()
            ('dummy_posts')
            ('blog_id = :id');
        return $this->assertEquals($delete, "DELETE FROM dummy_posts WHERE blog_id = :id");
    }

    /**
     * Tests whether the JOIN query is valid
     */

    public function testJoinStmt()
    {
        $join = $this->joinTemplate()();
        return $this->assertEquals($join, "SELECT blog.blog_text, blog.token_id, tokens.token_id, tokens.token_string FROM blog JOIN tokens ON blog.token_id = tokens.token_id");
    }

    /**
     * Tests whether the LEFT JOIN statement is valid
     */

    public function testLeftJoinStmt()
    {
        $leftJoin = $this->joinTemplate()('LEFT');
        return $this->assertEquals($leftJoin, "SELECT blog.blog_text, blog.token_id, tokens.token_id, tokens.token_string FROM blog LEFT JOIN tokens ON blog.token_id = tokens.token_id");
    }

    /**
     * Tests whether the RIGHT JOIN statement is valid
     */

    public function testRightJoinStmt()
    {
        $rightJoin = $this->joinTemplate()("RIGHT");
        return $this->assertEquals($rightJoin, "SELECT blog.blog_text, blog.token_id, tokens.token_id, tokens.token_string FROM blog RIGHT JOIN tokens ON blog.token_id = tokens.token_id");
    }

    /**
     * Tests whether the fetchAll() method returns a valid set of rows
     */

    public function testQueryFetchAll()
    {
        $rows = $this->getConnection()->query()('SELECT "Hello World"')()('fetch-all')();
        return $this->assertTrue(count($rows) > 1);
    }

    /**
     * Tests whether the fetch() method returns a single row
     */

    public function testQueryFetchRow()
    {
        $row = $this->getConnection()->query()('SELECT "Hello World"')()('fetch')();
        return $this->assertTrue(count($row['rows']) === 1);
    }

    /**
     * Tests whether the fetchColumn() method returns a single column
     */

    public function testQueryFetchColumn()
    {
        $column = $this->getConnection()->query()('SELECT "Hello World"')()('fetch-column')();
        return $this->assertTrue(count($column['rows']) < 1);
    }

    /**
     * Tests whether the array values are bound to a query to generate a result
     */

    public function testParamBindQueryFeature()
    {
        $condition = $this->getConnection()
            ->condition()
            ("WHERE blog_title LIKE :title");
        $select = $this->getConnection()
            ->select()
            (['blog_id', 'blog_title'])
            ('dummy_posts')
            ($condition);
        $rows = $this->getConnection()
            ->query()
            ($select)
            ([
                ['param' => ':title', 'value' => '%o%', 'type' => PDO::PARAM_INT]
            ])
            ('fetch-all')
            ();
        return $this->assertTrue(count($rows['rows']) > 0);
    }

    /**
     * Tests whether the new() method returns a new instance of the Query class
     */

    public function testNewInstanceCreation()
    {
        $new = new PDO($GLOBALS['NEW_DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
        $newConn = $this->getConnection()->new($new);
        return $this->assertEquals($newConn->get(), $new);
    }
}
