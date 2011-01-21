<?php

require_once ROOT_PATH . '/FactoryHag/util.php';
require_once '_files/Foos.php';
require_once '_files/Bars.php';

use FactoryHag as Hag;

class UtilTest extends PHPUnit_Framework_TestCase
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
		Hag\Broker::getInstance()->clear();
	}

	/*
	 * tests
	 */

	/*
	 * FactoryHag\factory()
	 */

	public function testFactoryFunctionWithNoArgumentsReturnsTheBroker()
	{
		$this->assertInstanceOf('FactoryHag\Broker', Hag\factory());
	}

	public function testFactoryFunctionWithArgumentsCreatesARowObject()
	{
		Hag\factory()->define('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$this->assertInstanceOf('Zend_Db_Table_Row_Abstract', Hag\factory('foo'));
	}

	/*
	 * FactoryHag\f()
	 */

	public function testFIsAShortcutForFactory()
	{
		$this->assertInstanceOf('FactoryHag\Broker', Hag\f());
	}

	public function testOverridingDefaultAttributesWorksWithF()
	{
		Hag\f()->define('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$foo = Hag\f('foo', array('qux' => 'something else'));
		$this->assertEquals('something else', $foo->qux);
	}

	/*
	 * FactoryHag\define()
	 */

	public function testDefineIsAShortcutForBrokerDefine()
	{
		Hag\define('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$this->assertInstanceOf('FactoryHag\Factory', Hag\Broker::getInstance()->factory('foo'));
	}

	public function testDefineCanBeGivenANullDb()
	{
		Bars::setDefaultAdapter($this->db);

		Hag\define('bar', array(
			'a' => 'one',
			'b' => 'two',
		), null);

		$this->assertInstanceOf('FactoryHag\Factory', Hag\Broker::getInstance()->factory('bar'));
	}

	/*
	 * FactoryHag\flush()
	 */

	public function testFlushIsAShortcutForBrokerFlush()
	{
		Hag\define('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$count = 10;

		for ($i = 0; $i < $count; $i++) {
			Hag\f('foo');
		}

		$this->assertEquals($count, (int) $this->db->fetchOne('SELECT COUNT(*) FROM foos'));

		Hag\flush();

		$this->assertEquals(0, (int) $this->db->fetchOne('SELECT COUNT(*) FROM foos'));
	}

	/*
	 * FactoryHag\pluralize()
	 */

	public function testPluralizeAddsAnSToEndByDefault()
	{
		$this->assertEquals('words', Hag\pluralize('word'));
	}

	public function testPluralizeReturnsEmptyStringWhenGivenEmptyString()
	{
		$this->assertEquals('', Hag\pluralize(''));
	}
}
