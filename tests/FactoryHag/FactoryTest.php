<?php

require_once '_files/Foos.php';
require_once '_files/Bars.php';

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

		$this->db->query('CREATE TABLE IF NOT EXISTS foos (id INTEGER PRIMARY KEY AUTOINCREMENT, bar TEXT, baz TEXT, qux TEXT)');
		$this->db->query('CREATE TABLE IF NOT EXISTS bars (id INTEGER PRIMARY KEY AUTOINCREMENT, a TEXT, b TEXT)');
	}

	public function tearDown()
	{
		$this->db->query('DELETE FROM foos');
		$this->db->query('DELETE FROM bars');
	}

	/*
	 * tests
	 */

	public function testNullCanBeGivenForDb()
	{
		Bars::setDefaultAdapter($this->db);

		$factory = new Factory('bar', array(
			'a' => 'one',
			'b' => 'two',
		), null);

		$bar = $factory->create();
		$this->assertInstanceOf('Zend_Db_Table_Row_Abstract', $bar);
	}

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

	public function testTableNameIsByDefaultPluralized()
	{
		$factory = new Factory('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$foo = $factory->create();

		$this->assertEquals('foos', $foo->getTable()->info('name'));
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

	public function testFlushClearsTheCreatedRows()
	{
		$factory = new Factory('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$count = 10;

		for ($i = 0; $i < $count; $i++) {
			$factory->create();
		}

		$this->assertEquals($count, (int) $this->db->fetchOne('SELECT COUNT(*) FROM foos'));

		$factory->flush();

		$this->assertEquals(0, (int) $this->db->fetchOne('SELECT COUNT(*) FROM foos'));
	}

	public function testFlushDoesNotRemoveRowsCreatedOutsideOfFactoryHag()
	{
		$factory = new Factory('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$count = 10;

		for ($i = 0; $i < $count; $i++) {
			$this->db->query("INSERT INTO foos VALUES (NULL, 'four', 'five', 'six')");
			$factory->create();
		}

		$this->assertEquals($count * 2, (int) $this->db->fetchOne('SELECT COUNT(*) FROM foos'));

		$factory->flush();

		$this->assertEquals($count, (int) $this->db->fetchOne('SELECT COUNT(*) FROM foos'));
	}

	public function testFlushWorksWhenNullHasBeenGivenForDb()
	{
		Bars::setDefaultAdapter($this->db);

		$factory = new Factory('bar', array(
			'a' => 'one',
			'b' => 'two',
		), null);

		$count = 10;

		for ($i = 0; $i < $count; $i++) {
			$factory->create();
		}

		$this->assertEquals($count, (int) $this->db->fetchOne('SELECT COUNT(*) FROM bars'));

		$factory->flush();

		$this->assertEquals(0, (int) $this->db->fetchOne('SELECT COUNT(*) FROM bars'));
	}
}
