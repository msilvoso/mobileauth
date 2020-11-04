<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initLogging()
	{
		$this->bootstrap('frontController');
		$logger = new Zend_Log();
		$writer = 'production' == $this->getEnvironment() ? new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../data/logs/app.log') :new Zend_Log_Writer_Firebug();
		$logger->addWriter($writer);
		if ('production' == $this->getEnvironment()) 
		{
			$filter = new Zend_Log_Filter_Priority(Zend_Log::INFO);
			$logger->addFilter($filter);
		}
		$this->_logger = $logger;
		Zend_Registry::set('log', $logger);
	}

	protected function _initConfig()
	{
		$this->_logger->debug('Bootstrap ' . __METHOD__);
		Zend_Registry::set('config', $this->getOptions());
	}

	protected function _initLocale()
	{
		$this->_logger->debug('Bootstrap ' . __METHOD__);
		$locale = new Zend_Locale('en_GB');
		Zend_Registry::set('Zend_Locale', $locale);
	}

	protected function _initViewSettings()
	{
		$this->_logger->debug('Bootstrap ' . __METHOD__);
		$this->bootstrap('view');
		$this->_view = $this->getResource('view');

		// set encoding and doctype
		$this->_view->setEncoding('UTF-8');
		$this->_view->doctype('XHTML1_STRICT');

		// set the content type and language
		$this->_view
		->headMeta()
		->appendHttpEquiv(
		'Content-Type', 'text/html; charset=UTF-8'
		);
		$this->_view
		->headMeta()
		->appendHttpEquiv('Content-Language', 'en-GB');
		
		// set css links
		$this->_view
		->headLink()
		->appendStylesheet('/css/style.css');

		// setting the site in the title
		$this->_view->headTitle('Mobile Authentication');

		// setting a separator string for segments:
		$this->_view->headTitle()->setSeparator(' - ');
	}

	protected function _initDbProfiler()
	{
		$this->_logger->debug('Bootstrap ' . __METHOD__);
		if ('production' !== $this->getEnvironment()) 
		{
			$this->bootstrap('db');
			$profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
			$profiler->setEnabled(true);
			$this->getPluginResource('db')
			->getDbAdapter()
			->setProfiler($profiler);
		}
	}

        protected function _initDefaultAutoloader()
        {
		$this->_logger->debug('Bootstrap ' . __METHOD__);

		$this->_resourceLoader = new Zend_Application_Module_Autoloader(array(
				'namespace' => '',
				'basePath' => APPLICATION_PATH,
			));
		$this->_resourceLoader->addResourceType('service', 'services','Service');
		$this->_resourceLoader->addResourceType('plugin', 'plugins','Plugin');
		$this->_resourceLoader->addResourceType('helper', 'helpers','Helper');
        }

	protected function _initPlugins()
	{
		$this->_logger->debug(__METHOD__);

		$this->_front=$this->getResource('frontController');
		$this->_front->registerPlugin(new Plugin_Authentication());

		Zend_Controller_Action_HelperBroker::addHelper(new Helper_Authentication());
		Zend_Controller_Action_HelperBroker::addHelper(new Helper_Logger());
		Zend_Controller_Action_HelperBroker::addHelper(new Helper_FlashMessages());
	}
}