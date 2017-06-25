<?php

/**
 * Query class for the Bingo Model
 *
 * @package Bingo-Model
 * @author Lochemem Bruno Michael <lochbm@gmail.com>
 */

namespace Chemem\Bingo\Model;

use \PDO;
use Chemem\Bingo\Model\Common\Config;
use Chemem\Bingo\Model\Exceptions\DomainException;

class Query implements Common\QueryInterface
{
    /**
     * The Database Instance
     *
     * @access private
     * @var $db
     */

    private $db = null;

    /**
     * The statement variable
     *
     * @access private
     * @var $stmt
     */

    private $stmt;

    /**
     * Class constructor
     *
     * @param object $dbInstance
     */

    public function __construct($dbInstance)
    {
        $this->db = $dbInstance;
    }

    /**
     * Query a database and return an array of values
     *
     * @return callable The database rows
     */

    public function query()
    {
        $stmt = $this->stmt; //a statement variable for db interactions
        $db = $this->db; //the PDO instance
        return function ($query) use (&$stmt, &$db) {
            //set the query pattern
            $query = preg_match('/[0-9\a-zA-Z\_\-\:\*\.\s]+/', $query, $matches) ?
                $matches[0] :
                DomainException::invalidQuery($query, __METHOD__);
            $stmt = $db->prepare($query); //prepare the query (Prepared statements)
            return function (array $params = null) use (&$stmt, &$db) {
                //bind values
                $count = count($params); //basic memoization
                for ($x = 0; $x < $count; ++$x) {
                    //bind data to the PDO prepared statement
                    $stmt->bindValue($params[$x]['param'], $params[$x]['value'], $params[$x]['type']);
                }
                $stmt->execute(); //execute the query
                return function ($fn) use (&$stmt, &$db) {
                    //get the PDO return function type
                    $method = lcfirst(str_replace(' ', '', strtolower(str_replace('-', ' ', $fn))));
                    return function (callable $callback = null) use (&$method, &$stmt, &$db) {
                        $rows = []; //the rows to be returned
                        if (Config::DB_CALLBACK_FETCH === true) {
                            $callback = !is_null($callback) || (new \ReflectionFunction($callback))->isClosure() === true ?
                                $callback :
                                DomainException::invalidCallback($callback, __METHOD__); //check the callback
                            //return an associative array based on callback definition
                            $rows = $stmt->$method(PDO::FETCH_FUNC, $callback);
                        }
                        $rows = $stmt->$method(PDO::FETCH_ASSOC); //return an associative array
                        return [
                            'affectedRows' => $stmt->rowCount(),
                            'errCode' => $stmt->errorCode(),
                            'errMsg' => $stmt->errorInfo(),
                            'rows' => $rows
                        ];
                    };
                };
            };
        };
    }

    /**
     * Generate a select query statement
     *
     * @return callable The generated query statement
     */

    public function select()
    {
        return function (array $fields) {
            $commaSeparated = implode(', ', $fields); //get the comma separated array fields
            return function ($table) use (&$commaSeparated) {
                $table = is_string($table) ?
                    strtolower($table) :
                    DomainException::invalidParameter($table, __METHOD__);
                return function ($condition = null) use (&$table, &$commaSeparated) {
                    //set condition to empty string if not specified
                    $condition = !is_null($condition) ? $condition : '';
                    //check the pattern of the condition
                    $validCondition = preg_match('/([A-Z]*) ([:\?\a-zA-Z\0-9]*)/', $condition, $matches);
                    $finalCondition = isset($matches[1]) || $condition === '' ?
                        $condition :
                        DomainException::invalidCondition($condition, __METHOD__);
                    //return a select query statement
                    return "SELECT {$commaSeparated} FROM {$table} {$finalCondition}";
                };
            };
        };
    }

    /**
     * Generate an update query statement
     *
     * @return callable The generated update statement
     */

    public function update()
    {
        return function ($table) {
            $table = is_string($table) ?
                strtolower($table) :
                DomainException::invalidParameter($table, __METHOD__);
            return function ($condition) use (&$table) {
                //check the pattern of the condition
                $validCondition = preg_match('/([a-z\_\-]*) ([:\?\a-zA-Z\0-9]*)/', $condition, $matches);
                $condition = isset($matches[1]) ?
                    $matches[0] :
                    DomainException::invalidParameter($condition, __METHOD__);
                //return an update query statement
                return "UPDATE {$table} SET {$condition}";
            };
        };
    }

    /**
     * Generate an insert query statement
     *
     * @return callable The generated insert statement
     */

    public function insert()
    {
        return function ($table) {
            $table = is_string($table) ?
                strtolower($table) :
                DomainException::invalidParameter($table, __METHOD__);
            return function (array $fields) use (&$table) {
                $commaFields = implode(', ', $fields);
                return function (array $placeholders) use (&$table, &$commaFields) {
                    $validPlaceholders = array_filter($placeholders, function ($value) {
                        return preg_match('/([:\?]*)([a-zA-Z]*)/', $value);
                    });
                    $commaPlaceholders = implode(', ', $validPlaceholders);
                    return "INSERT INTO {$table}({$commaFields}) VALUES ({$commaPlaceholders})";
                };
            };
        };
    }

    /**
     * Generate a delete query statement
     *
     * @return callable The generated delete statement
     */

    public function delete()
    {
        return function ($table) {
            $table = is_string($table) ?
                strtolower($table) :
                DomainException::invalidParameter($table, __METHOD__);
            return function ($condition) use (&$table) {
                $validCondition = preg_match('/([a-z\_\-]*) ([:\?\a-zA-Z\0-9]*)/', $condition, $matches);
                $condition = isset($matches[1]) ?
                    $matches[0] :
                    DomainException::invalidParameter($condition, __METHOD__);
                return "DELETE FROM {$table} WHERE {$condition}";
            };
        };
    }

    /**
     * Generate a condition: Conditions usually proceed an action
     *
     * @return callable The generated condition
     */

    public function condition()
    {
        return function ($condition) {
            $valid = preg_match('/([A-Z]*) ([:\?\a-zA-Z\0-9\_\-]*)/', $condition, $matches); //valid condition
            return isset($matches[1]) ?
                $matches[0] :
                DomainException::invalidCondition($condition, __METHOD__);
        };
    }

    /**
     * Generate a join query statement: This joins two tables
     *
     * @return callable The generated join query statement
     */

    public function join()
    {
        return function ($firstTable) {
            $firstTable = is_string($firstTable) ?
                strtolower($firstTable) :
                DomainException::invalidParameter($firstTable, __METHOD__);
            return function ($secondTable) use (&$firstTable) {
                $secondTable = is_string($secondTable) ?
                    strtolower($secondTable) :
                    DomainException::invalidParameter($secondTable, __METHOD__);
                return function (array $fields) use (&$secondTable, &$firstTable) {
                    //function to map table fields onto table names
                    $mapper = function ($table, array $fields) {
                        return array_map(function ($value) use (&$table) {
                            return $table . '.' . $value; //table bindings for JOIN statement
                        }, $fields);
                    };
                    $fieldCount = count($fields); //number of field arrays; cannot exceed two
                    $firstJoin = $fieldCount === 2 ?
                        $mapper($firstTable, $fields[0]) :
                        DomainException::exceededArgumentCount($fieldCount, 2, __METHOD__); //first set of fields
                    $secondJoin = $fieldCount === 2 ?
                        $mapper($secondTable, $fields[1]) :
                        DomainException::exceededArgumentCount($fieldCount, 2, __METHOD__); //second set of fields
                    $intersect = array_intersect($fields[0], $fields[1]); //get the intersecting field
                    $intersectCount = count($intersect); //get the number of intersecting fields; cannot exceed one
                    $firstIntersect = $intersectCount === 1 ?
                        $mapper($firstTable, $intersect) :
                        DomainException::exceededArgumentCount($intersectCount, 1, __METHOD__); //tableOne.intersect value
                    $secondIntersect = $intersectCount === 1 ?
                        $mapper($secondTable, $intersect) :
                        DomainException::exceededArgumentCount($intersectCount, 1, __METHOD__); //tableTwo.intersect value
                    return function ($joinType = null) use (
                        &$firstTable,
                        &$secondTable,
                        &$firstIntersect,
                        &$secondIntersect,
                        &$firstJoin,
                        &$secondJoin) {
                        $joinTypes = ['LEFT', 'RIGHT'];
                        $joinType = !is_null($joinType) && in_array($joinType, $joinTypes) ?
                            $joinType . ' JOIN' :
                            'JOIN';
                            $firstList = implode(', ', $firstJoin);
                            $secondList = implode(', ', $secondJoin);
                            $firstIntersect = implode('', $firstIntersect);
                            $secondIntersect = implode('', $secondIntersect);
                            //return a simple JOIN statement
                            return "SELECT {$firstList}, {$secondList} FROM {$firstTable} {$joinType} {$secondTable} ON {$firstIntersect} = {$secondIntersect}";
                    };
                };
            };
        };
    }

    /**
     * Get the database instance
     *
     * @return object $db The database instance
     */

    public function get()
    {
        return $this->db;
    }

    /**
     * Generate a new database instance
     *
     * @see Value Objects: https://en.wikipedia.org/wiki/Value_object
     * @return object The new DB instance
     */

    public function new($dbInstance)
    {
        $new = clone $this;
        $new->db = $dbInstance;
        return $new; //return a new instance of the db class for another connection
    }

    /**
     * The class destructor: terminate the database connection
     */

    public function __destruct()
    {
        $this->stmt = null;
        $this->db = null;
    }
}
