<?php

use PHPUnit\Framework\TestCase;
use Core\Database\Query\Builder;

class SelectMethodTest extends TestCase
{
    public function testCanAcceptsSingleArrayArgument() : void
    {
        $columns = [
            'col_a',
            'col_b'
        ];

        $builder = new Builder();
        $builder->table('tbl_a')
                ->select($columns);

        $args = $builder->getQueryVerbs('select')[0]['arguments'];

        $this->assertContains($columns[0], $args);
        $this->assertContains($columns[1], $args);
    }

    public function testCanAcceptsMultipleArgumentsAsASingleArray() : void
    {
        $columns = [
            'col_a',
            'col_b'
        ];

        $builder = new Builder();
        $builder->table('tbl_a')
                ->select($columns[0], $columns[1]);

        $args = $builder->getQueryVerbs('select')[0]['arguments'];

        $this->assertContains($columns[0], $args);
        $this->assertContains($columns[1], $args);
    }

    public function testCanSetPossibleConjunctions() : void
    {
        $builder = new Builder();
        $builder->table('tbl_a')
                ->select('*');

        $this->assertEquals(['where'], $builder->possibleConjunctions);
    }
}