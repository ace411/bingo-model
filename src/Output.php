<?php

/**
 * Output class for the Bingo Model
 *
 * @package Bingo-Model
 * @author Lochemem Bruno Michael <lochbm@gmail.com>
 */

namespace Chemem\Bingo\Model;

use Chemem\Bingo\Model\Exceptions\DomainException;

class Output implements Common\OutputInterface
{
    /**
     * An array of database data
     *
     * @access private
     * @var $dbData
     */

    private $dbData;

    /**
     * Output class constructor
     *
     * @param array $dbData
     */

    public function __construct(array $dbData)
    {
        $this->dbData = $dbData;
    }

    /**
     * Get the database data
     *
     * @return array $dbData
     */

    public function getData()
    {
        return $this->dbData;
    }

    /**
     * Map a function onto the data
     *
     * @param callable $fn The function to be used
     * @return callable $fn($data) Output of the function mapping
     */

    public function map(callable $fn)
    {
        return (new \ReflectionFunction($fn))->isClosure() === true ?
            $fn($this->dbData) :
            DomainException::invalidCallback($fn, __METHOD__);
    }

    /**
     * Return json values
     *
     * @return string $json
     */

    public function json()
    {
        return json_encode($this->dbData); //return json encoded data
    }
}
