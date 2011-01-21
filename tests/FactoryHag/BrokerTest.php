<?php

require_once '_files/Foo.php';
require_once '_files/Bar.php';

use FactoryHag\Broker as Broker;

class BrokerTest extends PHPUnit_Framework_TestCase
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
		$this->db->query('CREATE TABLE IF NOT EXISTS bar (id INTEGER PRIMARY KEY AUTOINCREMENT, a TEXT, b TEXT)');
	}

	public function tearDown()
	{
		$this->db->query('DELETE FROM foo');
		$this->db->query('DELETE FROM bar');
	}

	/*
	 * tests
	 */

	public function testDefineCreatesFactory()
	{
		$broker = Broker::getInstance();
		$broker->define('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$this->assertInstanceOf('FactoryHag\Factory', $broker->factory('foo'));
	}

	public function testInvokeIsAShortcutForFactory()
	{
		$broker = Broker::getInstance();
		$broker->define('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$this->assertInstanceOf('FactoryHag\Factory', $broker('foo'));
	}

	public function testNullCanBePassedForDb()
	{
		$broker = Broker::getInstance();

		Bar::setDefaultAdapter($this->db);

		$broker->define('bar', array(
			'a' => 'one',
			'b' => 'two',
		), null);

		$this->assertInstanceOf('FactoryHag\Factory', $broker('bar'));
	}

	/**
	 * @expectedException FactoryHag\Exception
	 */
	public function testRetrievingANonDefinedFactoryThrowsAnException()
	{
		$broker = Broker::getInstance();
		$broker('unknown');
	}

	public function testFlushClearsCreatedObjectsFromDatabase()
	{
		$broker = Broker::getInstance();
		$broker->define('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$count = 10;

		for ($i = 0; $i < $count; $i++) {
			$broker->factory('foo')->create();
		}

		$this->assertEquals($count, (int) $this->db->fetchOne('SELECT COUNT(*) FROM foo'));

		$broker->flush();

		$this->assertEquals(0, (int) $this->db->fetchOne('SELECT COUNT(*) FROM foo'));
	}
}
