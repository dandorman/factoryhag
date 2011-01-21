<?php

require_once '_files/Foo.php';

use FactoryHag\Factory as Factory;

class FactoryTest extends PHPUnit_Framework_TestCase
{
	/*
	 * setup/teardown
	 */

	public function setUp()
	{
		$this->db = Zend_Db::factory('Pdo_Sqlite', array(
			'dbname' => ':memory:',
		));

		$this->db->query('CREATE TABLE IF NOT EXISTS foo (id INTEGER PRIMARY KEY AUTOINCREMENT, bar TEXT, baz TEXT, qux TEXT)');
	}

	public function tearDown()
	{
		$this->db->query('DELETE FROM foo');
	}

	/*
	 * tests
	 */

	public function testCreateReturnsATableRowObject()
	{
		$factory = new Factory('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$this->assertInstanceOf('Zend_Db_Table_Row_Abstract', $factory->create());
	}

	public function testCreateSetsInitialValuesForTableRowObject()
	{
		$factory = new Factory('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$foo = $factory->create();

		$this->assertEquals('one', $foo->bar);
		$this->assertEquals('two', $foo->baz);
		$this->assertEquals('three', $foo->qux);
	}

	public function testInvokeIsAShortcutForCreate()
	{
		$factory = new Factory('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$this->assertInstanceOf('Zend_Db_Table_Row_Abstract', $factory());
	}

	public function testPassingSpecificAttributesToCreateOverridesDefaults()
	{
		$factory = new Factory('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$foo = $factory(array('bar' => 'sup', 'baz' => 'holmes'));

		$this->assertEquals('sup', $foo->bar);
		$this->assertEquals('holmes', $foo->baz);
		$this->assertEquals('three', $foo->qux);
	}
}
