<?php

namespace Chemem\Bingo\Model\Exceptions;

use Chemem\Bingo\Model\Common\ExceptionInterface;

class DomainException extends \DomainException implements ExceptionInterface
{
    public static function invalidParameter($parameter, $method)
    {
        return new static("The parameter {$parameter} in method {$method} is invalid", 1);
    }

    public static function invalidQuery($query, $method)
    {
        return new static("The query {$query} in method {$method} is invalid", 1);
    }

    public static function invalidCondition($condition, $method)
    {
        return new static("The condition {$condition} in method {$method} is invalid", 1);
    }

    public static function invalidCallback($callback, $method)
    {
        return new static("The callback {$callback} in class {$method} is invalid", 1);
    }

    public static function exceededArgumentCount(int $count, int $desired, $method)
    {
        return new static("Supplied {$count} arguments instead of {$desired} in method {$method}", 1);
    }
}
