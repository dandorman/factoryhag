<?php

namespace FactoryHag;

class Factory
{
	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db;

	/**
	 * @var string
	 */
	protected $_tableClass;

	/**
	 * @var array
	 */
	protected $_defaults;

	/**
	 * @var array
	 */
	protected $_createdPrimaryKeys = array();

	/**
	 * @return FactoryHag\Factory
	 */
	public function __construct($name, array $defaults, \Zend_Db_Adapter_Abstract $db)
	{
		$this->_db = $db;
		$this->_defaults = $defaults;
		$this->_tableClass = ucfirst(preg_replace('/_(.)/e', 'strtoupper(\1)', $name));
	}

	/**
	 * Return a table row instance of the factory which has been saved in the database.
	 *
	 * @param  array [$attributes]
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function create(array $attributes = array())
	{
		$table = new $this->_tableClass($this->_db);

		$data = $this->_defaults;
		foreach ($attributes as $attr => $value) {
			$data[$attr] = $value;
		}

		$row = $table->createRow($data);
		$this->_createdPrimaryKeys []= $row->save();
		return $row;
	}

	/**
	 * Shortcut for @see FactoryHag\FactoryHag#create.
	 *
	 * @param  array [$attributes]
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function __invoke(array $attributes = array())
	{
		return $this->create($attributes);
	}

	/**
	 * Clear created records from the database.
	 *
	 * @return FactoryHag\Factory $this
	 */
	public function flush()
	{
		if ($this->_createdPrimaryKeys) {
			$table = new $this->_tableClass($this->_db);
			$primary = current($table->info('primary'));
			$table->delete(
				$this->_db->quoteInto(
					"$primary IN (?)",
					$this->_createdPrimaryKeys
				)
			);
		}
		return $this;
	}
}
