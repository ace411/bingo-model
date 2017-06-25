<?php

namespace Chemem\Bingo\Model\Common;

interface QueryInterface
{
    public function query();

    public function select();

    public function update();

    public function insert();

    public function delete();

    public function join();

    public function condition(); //condition
}
