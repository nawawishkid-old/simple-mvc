<?php

use PHPUnit\Framework\TestCase;
use Core\Database\Query\Builder;

class UpdateQueryTest extends TestCase
{
    const SELECT_ALL = "SELECT * FROM tbl_a";
    const SELECT_SINGLE_COLS = "SELECT col_a FROM tbl_a";
    const SELECT_SINGLE_COL_AS = "SELECT col_a AS a FROM tbl_a";
    const SELECT_MULTI_COLS = "SELECT col_a, col_b FROM tbl_a";
    const SELECT_MULTI_COLS_AS = "SELECT col_a AS a, col_b AS b FROM tbl_a";
    const SELECT_WHERE = "SELECT col_a, col_b FROM tbl_a WHERE col_a > 20";
    const SELECT_WHERE_AND = "SELECT col_a, col_b FROM tbl_a WHERE col_a > 20 AND col_b > 100";
    const SELECT_WHERE_OR = "SELECT col_a, col_b FROM tbl_a WHERE col_a > 20 OR col_b > 100";

    public function testCanBuildsSelectAllQuery() : void
    {
        $expected = self::SELECT_ALL;

        $builder = new Builder();
        $builder->table('tbl_a')
                ->select('*');

        $actual = $builder->get();

        unset($builder);

        $this->assertEquals($expected, $actual);
    }

    public function testCanBuildsSelectSingleColumnQuery() : void
    {
        $expected = self::SELECT_SINGLE_COLS;

        $builder = new Builder();
        $builder->table('tbl_a')
                ->select('col_a');

        $actual = $builder->get();

        unset($builder);

        $this->assertEquals($expected, $actual);
    }

    public function testCanBuildsSelectSingleColumnASQuery() : void
    {
        $expected = self::SELECT_SINGLE_COL_AS;

        $builder = new Builder();
        $builder->table('tbl_a')
                ->select('col_a AS a');

        $actual = $builder->get();

        unset($builder);

        $this->assertEquals($expected, $actual);
    }

    public function testCanBuildsSelectMultipleColumnsQuery() : void
    {
        $expected = self::SELECT_MULTI_COLS;

        $builder = new Builder();
        $builder->table('tbl_a')
                ->select('col_a', 'col_b');

        $actual = $builder->get();

        unset($builder);

        $this->assertEquals($expected, $actual);
    }

    public function testCanBuildsSelectMultipleColumnsASQuery() : void
    {
        $expected = self::SELECT_MULTI_COLS_AS;

        $builder = new Builder();
        $builder->table('tbl_a')
                ->select('col_a AS a', 'col_b AS b');

        $actual = $builder->get();

        unset($builder);

        $this->assertEquals($expected, $actual);
    }

    public function testCanBuildsWithConjunctionWHERE() : void
    {
        $expected = self::SELECT_WHERE;

        $builder = new Builder();
        $builder->table('tbl_a')
                ->select('col_a', 'col_b')
                ->where('col_a', '>', 20);

        $actual = $builder->get();

        unset($builder);

        $this->assertEquals($expected, $actual);
    }

    public function testCanBuildsWithConjunctionWHEREAndConditionAND() : void
    {
        $expected = self::SELECT_WHERE_AND;

        $builder = new Builder();
        $builder->table('tbl_a')
                ->select('col_a', 'col_b')
                ->where('col_a', '>', 20)
                ->andWhere('col_b', '>', 100);

        $actual = $builder->get();

        unset($builder);

        $this->assertEquals($expected, $actual);
    }

    public function testCanBuildsWithConjunctionWHEREAndConditionOR() : void
    {
        $expected = self::SELECT_WHERE_OR;

        $builder = new Builder();
        $builder->table('tbl_a')
                ->select('col_a', 'col_b')
                ->where('col_a', '>', 20)
                ->orWhere('col_b', '>', 100);

        $actual = $builder->get();

        unset($builder);

        $this->assertEquals($expected, $actual);
    }
}