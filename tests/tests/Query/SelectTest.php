<?php

use Openbuildings\Cherry\Query_Select;
use Openbuildings\Cherry\Statement_Part_Expression;

/**
 * @group query.select
 */
class Statement_SelectTest extends Testcase_Extended {

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
			->and_where('test_statement', '=', new Statement_Part_Expression('IF ("test", 1, ?)', array('expression_value')))
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

		$expected_sql = <<<SQL
SELECT col1, col3 AS alias_col FROM bigtable, smalltable AS alias, (SELECT DISTINCT * FROM one JOIN table1 USIGN col1, col2 WHERE name = ?) AS select_alias JOIN table2 ON col1 = col2 WHERE test = ? AND test_statement = IF ("test", 1, ?) AND (type > ? AND type < ? AND base IN (?, ?, ?)) GROUP BY base ASC, type HAVING test = ? AND (type > ? AND base IN (?, ?, ?)) ORDER BY type ASC, base LIMIT 10 OFFSET 8
SQL;

		$this->assertEquals($expected_sql, $select->compile());

		$expected_parameters = array(
			'small',
			'value',
			'expression_value',
			'10',
			'20',
			'1',
			'2',
			'3',
			'value2',
			'20',
			'5',
			'6',
			'7',
		);

		$this->assertEquals($expected_parameters, $select->parameters());

		$humanized = <<<HUMANIZED
SELECT
  col1, col3 AS alias_col
FROM
  bigtable,
  smalltable AS alias,
  (
    SELECT
    DISTINCT
      *
    FROM
      one
    JOIN table1 USIGN col1, col2
    WHERE name = "small"
  ) AS select_alias
JOIN table2 ON col1 = col2
WHERE test = "value" AND test_statement = IF ("test", 1, ?) AND (type > "10" AND type < "20" AND base IN ("1", "2", "3"))
GROUP BY base ASC, type
HAVING test = "value2" AND (type > "20" AND base IN ("5", "6", "7"))
ORDER BY type ASC, base
LIMIT 10
OFFSET 8
HUMANIZED;
		
		$this->assertEquals($humanized, $select->compile(TRUE));
	}
}