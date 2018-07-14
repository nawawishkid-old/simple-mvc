<?php

use PHPUnit\Framework\TestCase;
use Core\Database\Query\Builder;

class BuilderTest extends TestCase
{
    public function testCanSetsTable()
    {
        $builder = new Builder();
        $builder->table('tbl_a');

        $expected = 'tbl_a';
        $actual = $builder->table;

        unset($builder);

        $this->assertEquals($expected, $actual);
    }
}