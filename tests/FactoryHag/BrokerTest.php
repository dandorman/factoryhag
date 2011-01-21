<?php

require_once '_files/Foo.php';

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
	}

	public function tearDown()
	{
		$this->db->query('DELETE FROM foo');
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

	/**
	 * @expectedException FactoryHag\Exception
	 */
	public function testRetrievingANonDefinedFactoryThrowsAnException()
	{
		$broker = Broker::getInstance();
		$bar = $broker('bar');
	}
}
