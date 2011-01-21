<?php

namespace FactoryHag;

class Factory
{
	/**
	 * @var Zend_Db_Table_Abstract
	 */
	protected $_table;

	/**
	 * @var array
	 */
	protected $_defaults;

	/**
	 * @var array
	 */
	protected $_createdPrimaryKeys = array();

	/**
	 * @param  string $name
	 * @param  array $defaults
	 * @param  Zend_Db_Adapter_Abstract|null $db
	 * @return FactoryHag\Factory
	 */
	public function __construct($name, array $defaults, $db)
	{
		$this->_defaults = $defaults;
		$class = ucfirst(preg_replace('/_(.)/e', 'strtoupper(\1)', $name));

		$this->_table = new $class($db);
	}

	/**
	 * Return a table row instance of the factory which has been saved in the database.
	 *
	 * @param  array [$attributes]
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function create(array $attributes = array())
	{
		$data = $this->_defaults;
		foreach ($attributes as $attr => $value) {
			$data[$attr] = $value;
		}

		$row = $this->_table->createRow($data);
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
			$primary = current($this->_table->info('primary'));
			$db = $this->_table->getAdapter();

			$this->_table->delete(
				$db->quoteInto(
					"$primary IN (?)",
					$this->_createdPrimaryKeys
				)
			);
		}
		return $this;
	}
}
