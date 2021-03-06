<?php

class Bars extends Zend_Db_Table_Abstract
{
	static public function setDefaultAdapter(Zend_Db_Adapter_Abstract $adapter)
	{
		self::$_defaultDb = $adapter;
	}

	protected $_name = 'bars';
	protected $_primary = 'id';

	public function __construct()
	{
		parent::__construct(array('db' => self::$_defaultDb));
	}
}
