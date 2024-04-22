<?php

class Community_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAutoload()
    {
	$moduleLoader = new Zend_Application_Module_Autoloader(array(
		'namespace' => 'Community_',
		'basePath' => dirname(__FILE__)));
	return $moduleLoader;
    }

}

