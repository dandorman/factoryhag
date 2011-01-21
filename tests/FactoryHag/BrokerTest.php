<?php

require_once '_files/Foos.php';
require_once '_files/Bars.php';

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

		$this->db->query('CREATE TABLE IF NOT EXISTS foos (id INTEGER PRIMARY KEY AUTOINCREMENT, bar TEXT, baz TEXT, qux TEXT)');
		$this->db->query('CREATE TABLE IF NOT EXISTS bars (id INTEGER PRIMARY KEY AUTOINCREMENT, a TEXT, b TEXT)');
	}

	public function tearDown()
	{
		$this->db->query('DELETE FROM foos');
		$this->db->query('DELETE FROM bars');
		Broker::getInstance()->clear();
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

	/**
	 * @expectedException FactoryHag\Exception
	 */
	public function testCannotDefineTwoFactoriesWithSameName()
	{
		$broker = Broker::getInstance();
		$broker->define('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$broker->define('foo', array(
			'bar' => 'four',
			'baz' => 'five',
			'qux' => 'six',
		), $this->db);
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

		Bars::setDefaultAdapter($this->db);

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

		$this->assertEquals($count, (int) $this->db->fetchOne('SELECT COUNT(*) FROM foos'));

		$broker->flush();

		$this->assertEquals(0, (int) $this->db->fetchOne('SELECT COUNT(*) FROM foos'));
	}

	/**
	 * @expectedException FactoryHag\Exception
	 */
	public function testClearResetsSingleton()
	{
		$broker = Broker::getInstance();
		$broker->define('foo', array(
			'bar' => 'one',
			'baz' => 'two',
			'qux' => 'three',
		), $this->db);

		$broker->clear();

		$broker->factory('foo');
	}
}
