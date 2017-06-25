<?php

namespace Chemem\Bingo\Model\Common;

interface ExceptionInterface
{
    public static function invalidParameter($parameter, $method);

    public static function invalidQuery($query, $method);

    public static function invalidCondition($condition, $method);

    public static function invalidCallback($callback, $method);

    public static function exceededArgumentCount(int $count, int $desired, $method);
}
