<?php

namespace Chemem\Bingo\Model\Common;

interface TransactionInterface
{
    public function begin();

    public function cancel();

    public function validate();

    public function commit();
}
