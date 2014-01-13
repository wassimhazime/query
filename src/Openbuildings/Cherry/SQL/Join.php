<?php namespace Openbuildings\Cherry;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class SQL_Join extends SQL
{
	protected $condition;
	protected $type;

	function __construct($table, $condition, $type = NULL)
	{
		if (is_array($condition)) 
		{
			$statements = array();

			foreach ($condition as $column => $foreign_column)
			{
				$statements [] = "$column = $foreign_column";
			}

			$this->condition = 'ON '.join(' AND ', $statements);
		}
		else
		{
			$this->condition = $condition;
		}

		$this->type = $type;

		parent::__construct($table);
	}

	public function table()
	{
		return $this->content;
	}

	public function condition()
	{
		return $this->condition;
	}

	public function parameters()
	{
		if ($this->condition instanceof SQL) 
		{
			return $this->condition->parameters();
		}

		return parent::parameters();
	}

	public function type()
	{
		return $this->type;
	}
}
