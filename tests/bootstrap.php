<?php

defined('ROOT_PATH') || define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

set_include_path(implode(PATH_SEPARATOR, array(
	ROOT_PATH,
	ROOT_PATH . '/vendor',
	get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('FactoryHag\\');
