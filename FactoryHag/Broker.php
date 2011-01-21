<?php

namespace FactoryHag;

require_once 'Factory.php';
require_once 'Exception.php';

class Broker
{
	/**
	 * @static FactoryHag\Broker
	 */
	static protected $_instance;

	/**
	 * @return FactoryHag\Broker
	 */
	static public function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * @return FactoryHag\Broker
	 */
	protected function __construct()
	{
	}

	/**
	 * @var array
	 */
	protected $_factories = array();

	/**
	 * Define one or more factories.
	 *
	 * @param  string $name
	 * @param  array $defaults
	 * @param  Zend_Db_Adapter_Abstract|null $db
	 * @return FactoryHag $this
	 */
	public function define($name, array $defaults, $db)
	{
		if (array_key_exists($name, $this->_factories)) {
			throw new Exception("There is already a factory defined with name '$name'");
		}

		$this->_factories[$name] = new Factory($name, $defaults, $db);
		return $this;
	}

	/**
	 * Fetch the corresponding factory.
	 *
	 * @param  string $factory
	 * @return FactoryHag\Factory
	 */
	public function factory($factory)
	{
		if (!array_key_exists($factory, $this->_factories)) {
			throw new Exception("There is no factory defined for '$factory'");
		}

		return $this->_factories[$factory];
	}

	/**
	 * Shortcut for @see FactoryHag\Broker#factory.
	 *
	 * @param  string $factory
	 * @return FactoryHag\Factory
	 */
	public function __invoke($factory)
	{
		return $this->factory($factory);
	}

	/**
	 * Clear all factory-created items from the database.
	 *
	 * @return Broker $this
	 */
	public function flush()
	{
		foreach ($this->_factories as $factory) {
			$factory->flush();
		}
		return $this;
	}

	/**
	 * Remove all factories, resetting the instance.
	 *
	 * @return Broker $this
	 */
	public function clear()
	{
		$this->_factories = array();
		return $this;
	}
}
