<?php namespace Openbuildings\Cherry;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class DB extends \PDO
{
	protected static $default_name = 'default';
	protected static $configurations;
	protected static $instances;

	public static $defaults = array(
		'dsn' => 'mysql:dbname=test;host=127.0.0.1',
		'username' => '',
		'password' => '',
		'driver_options' => array(
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		),
	);

	public static function configuration($name, array $parameters = NULL)
	{
		if ($parameters === NULL)
		{
			return isset(static::$configurations[$name]) ? static::$configurations[$name] : array();
		}

		static::$configurations[$name] = $parameters;
	}

	public static function default_name($default_name = NULL)
	{
		if ($default_name !== NULL)
		{
			static::$default_name = $default_name;
		}
		return static::$default_name;
	}

	public static function instance($name = NULL)
	{
		$name = $name ?: static::default_name();

		if ( ! isset(static::$instances[$name]))
		{
			$config = static::configuration($name);

			static::$instances[$name] = new DB($config);
		}

		return static::$instances[$name];
	}

	public function __construct($options)
	{
		$options = array_replace_recursive(static::$defaults, $options);

		parent::__construct($options['dsn'], $options['username'], $options['password'], $options['driver_options']);
	}

	public function execute($sql, $parameters)
	{
		$statement = $this->prepare($sql);
		$statement->execute($parameters);

		return $statement;
	}

	public function select()
	{
		return new Query_Select(NULL, $this);
	}

	public function update()
	{
		return new Query_Update(NULL, $this);
	}

	public function delete()
	{
		return new Query_Delete(NULL, $this);
	}

	public function insert()
	{
		return new Query_Insert(NULL, $this);
	}
}
