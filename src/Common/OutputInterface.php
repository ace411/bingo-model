<?php

namespace Chemem\Bingo\Model\Common;

interface OutputInterface
{
    public function getData();

    public function map(callable $fn);

    public function json();
}
