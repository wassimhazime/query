<?php

use Openbuildings\Cherry\Compiler;
use Openbuildings\Cherry\Query;
use Openbuildings\Cherry\Statement_Expression;
use Openbuildings\Cherry\Query_Select;
use Openbuildings\Cherry\Query_Update;
use Openbuildings\Cherry\Query_Delete;

/**
 * @group compiler
 */
class CompilerTest extends Testcase_Extended {

	public function test_compile()
	{
		$select2 = new Query_Select();
		$select2
			->from('one')
			->distinct()
			->join('table1')
			->using(array('col1', 'col2'))
			->where('name', '=', 'small');

		$select = new Query_Select();
		$select
			->from('bigtable', array('smalltable', 'alias'))
			->from(array($select2, 'select_alias'))
			->select('col1', array('col3', 'alias_col'))
			->and_where('test', '=', 'value')
			->and_where('test_statement', '=', new Statement_Expression('IF ("test", ?, ?)', array('val1', 'val2')))
			->join('table2')
			->on('col1', '=', 'col2')
			->and_where_open()
				->and_where('type', '>', '10')
				->and_where('type', '<', '20')
				->and_where('base', 'IN', array('1', '2', '3'))
			->and_where_close()
			->and_having('test', '=', 'value2')
			->and_having_open()
				->and_having('type', '>', '20')
				->and_having('base', 'IN', array('5', '6', '7'))
			->and_where_close()
			->limit(10)
			->offset(8)
			->order_by('type', 'ASC')
			->order_by('base')
			->group_by('base', 'ASC')
			->group_by('type');

		$compiler = new Compiler();

		$expected_sql = <<<SQL
SELECT col1, col3 AS alias_col FROM bigtable, smalltable AS alias, (SELECT DISTINCT * FROM one JOIN table1 USING (col1, col2) WHERE name = "small") AS select_alias JOIN table2 ON col1 = col2 WHERE test = "value" AND test_statement = IF ("test", "val1", "val2") AND (type > "10" AND type < "20" AND base IN ("1", "2", "3")) GROUP BY base ASC, type HAVING test = "value2" AND (type > "20" AND base IN ("5", "6", "7")) ORDER BY type ASC, base LIMIT 10 OFFSET 8
SQL;

		$this->assertEquals($expected_sql, $compiler->compile($select));
	}

	public function test_update()
	{
		$update = new Query_Update();
		$update
			->table('table1', array('table1', 'alias1'))
			->set(array('post' => 'new value', 'name' => 'new name'))
			->limit(10)
			->where('test', '=', 'value')
			->where('test_statement', '=', new Statement_Expression('IF ("test", ?, ?)', array('val1', 'val2')))
			->where_open()
				->where('type', '>', '10')
				->where('type', '<', '20')
				->or_where('base', 'IN', array('1', '2', '3'))
			->where_close();

		$compiler = new Compiler();

		$expected_sql = <<<SQL
UPDATE table1, table1 AS alias1 SET post = "new value", name = "new name" WHERE test = "value" AND test_statement = IF ("test", "val1", "val2") AND (type > "10" AND type < "20" OR base IN ("1", "2", "3")) LIMIT 10
SQL;

		$this->assertEquals($expected_sql, $compiler->compile($update));
	}

	public function test_delete()
	{
		$update = new Query_Delete();
		$update
			->only('table1', 'alias1')
			->from('table1', array('table1', 'alias1'), 'table3')
			->limit(10)
			->where('test', '=', 'value')
			->where('test_statement', '=', new Statement_Expression('IF ("test", ?, ?)', array('val1', 'val2')))
			->where_open()
				->where('type', '>', '10')
				->where('type', '<', '20')
				->or_where('base', 'IN', array('1', '2', '3'))
			->where_close();

		$compiler = new Compiler();

		$expected_sql = <<<SQL
DELETE table1, alias1 FROM table1, table1 AS alias1, table3 WHERE test = "value" AND test_statement = IF ("test", "val1", "val2") AND (type > "10" AND type < "20" OR base IN ("1", "2", "3")) LIMIT 10
SQL;

		$this->assertEquals($expected_sql, $compiler->compile($update));
	}
}