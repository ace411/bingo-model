<?php

/**
 * Transaction class for the Bingo Model
 *
 * @package Bingo-Model
 * @author Lochemem Bruno Michael <lochbm@gmail.com>
 */

namespace Chemem\Bingo\Model;

final class Transaction extends Query implements Common\TransactionInterface
{
    /**
     * Begin the transaction
     *
     * @see http://php.net/manual/en/pdo.begintransaction.php
     * @return bool $db->beginTransaction()
     */

    public function begin()
    {
        return self::get()->beginTransaction();
    }

    /**
     * Rollback a transaction
     *
     * @see http://php.net/manual/en/pdo.rollback.php
     * @return bool $db->rollBack()
     */

    public function cancel()
    {
        return self::get()->rollBack();
    }

    /**
     * Commit the transaction
     *
     * @see http://php.net/manual/en/pdo.commit.php
     * @return bool $db->commit()
     */

    public function commit()
    {
        return self::get()->commit();
    }

    /**
     * Check if transaction is active
     *
     * @see http://php.net/manual/en/pdo.intransaction.php
     * @return bool $db->inTransaction()
     */

    public function validate()
    {
        return self::get()->inTransaction();
    }
}
