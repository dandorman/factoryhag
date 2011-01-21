<?php

namespace FactoryHag;

require_once 'Broker.php';

/**
 * A globally available shortcut to the @see FactoryHag\Broker and 
 * @see FactoryHag\Factory classes, depending on how it's invoked.
 *
 * @return FactoryHag\Factory|Zend_Db_Table_Row_Abstract
 */
function factory(/* mixed */)
{
	$broker = Broker::getInstance();

	$args = func_get_args();
	if ($args) {
		list($factory, $attrs) = $args;
		return $broker->factory($factory)->create($attrs ?: array());
	} else {
		return $broker;
	}
}

/**
 * Why not? I'm lazy, and this has to be namespaced to be useful.
 *
 * @return mixed
 */
function f()
{
	return call_user_func_array('FactoryHag\factory', func_get_args());
}

/**
 * A shortcut to @see FactoryHag\Broker#define.
 *
 * @param  string $name
 * @param  array $defaults
 * @param  Zend_Db_Adapter_Abstract|null $db
 * @return FactoryHag\Factory  The newly created factory.
 */
function define($name, array $defaults, $db)
{
	$broker = Broker::getInstance();
	$broker->define($name, $defaults, $db);
	return $broker->factory($name);
}

/**
 * Shortcut for @see FactoryHag\Broker#flush.
 *
 * @return FactoryHag\Broker
 */
function flush()
{
	return Broker::getInstance()->flush();
}
