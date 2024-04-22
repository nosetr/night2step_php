<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initConfig()
    {
        $config = new Zend_Config($this->getOptions());
        Zend_Registry::set('config', $config);
        return $config;
    }
    /*
    public function _initMB(){
        mb_internal_encoding("UTF-8");
    }
    
    public static function _initView()
    {
        $view = new Zend_View;
        $view->setEncoding('UTF-8');
    }
     * 
     */

    protected function _initLayoutHelper()
    {
        $this->bootstrap('frontController');
        Zend_Controller_Action_HelperBroker::addHelper(
            new N2S_Layout_LayoutLoader());
    }

    protected function _initDbAdapter()
    {
        $r = $this->getPluginResource('db');
        $p = $r->getParams();
        if (isset($p['prefix']))
        {
            Zend_Registry::set('TABLE_PREFIX', $p['prefix']);
        }

        $db = $r->getDbAdapter();
        
        Zend_Registry::set("dbAdapter", $db);
    }

    protected function _initAuth()
    {
      $this->bootstrap('frontController');
      $auth = Zend_Auth::getInstance();
      $acl = new N2S_Auth_Acl();
      $this->getResource('frontController')
              ->registerPlugin(new N2S_Auth_AccessControl($auth, $acl))
              ->setParam('auth', $auth);
    }
    
    protected function _initLucene()
    {
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8());
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
    }
    
    protected function _initLogger() 
    {
	$columnMapping = array('priority' => 'priority', 'message' => 'message',
			'timestamp' => 'timestamp', 'priorityName' => 'priorityName');
		
		
	$db = Zend_Registry::get("dbAdapter");
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        if (Zend_Registry::isRegistered('TABLE_PREFIX')) {
            $pref = Zend_Registry::get('TABLE_PREFIX');
        } else {
            $pref = '';
        }
		
	$writer = new Zend_Log_Writer_Db($db, $pref.'logger', $columnMapping);
	$logger = new Zend_Log($writer);
 
	Zend_Registry::set('logger', $logger);
    }
    
    protected function _initSession() {
        $db = Zend_Registry::get("dbAdapter");
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        if (Zend_Registry::isRegistered('TABLE_PREFIX')) {
            $pref = Zend_Registry::get('TABLE_PREFIX');
        } else {
            $pref = '';
        }
        Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_DbTable(
                                array( 
                                        'name' => $pref.'session',
                                        'primary' => 'id',
                                        'modifiedColumn'  => 'modified', 
                                        'dataColumn'   => 'data', 
                                        'lifetimeColumn'  => 'lifetime' , 
                                        'lifetime'	=> Zend_Registry::get('config')->authsession->lifetime->sec,
                                        'overrideLifetime' => false						
                                )));
        Zend_Session::start();
    }
    
    protected function _initSessionAfterDb()
    {
            $this->bootstrap('db');
            $this->bootstrap('session');
    }

    protected function _initViewHelpers()
    {
        $session = new Zend_Session_Namespace('userlanguage');
        if(isset($session->language)){
            $lang = $session->language;
        } else {
            $lang = Zend_Registry::get('config')->language->default->key;
        }
	$this->bootstrap('layout');
	$layout = $this->getResource('layout');
	$view = $layout->getView();
        
        //Minify
        $view->registerHelper(new N2S_View_Helper_MinifyHeadScript(), 'headScript');
        $view->registerHelper(new N2S_View_Helper_MinifyHeadLink(), 'headLink');
        
        $view->doctype('XHTML1_RDFA');
	$view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8')
                         ->appendHttpEquiv('Content-Language', $lang)
                         //->setName('robots','noimageindex')
                         ->setProperty('og:type','website')
                         ->appendProperty('og:site_name','night2step');
        $view->headTitle('night2step')->setSeparator(' | ');
        $view->headLink()->setStylesheet('/js/jquery/css/jquery-ui-1.8.17.custom.css');
        
        //GoogleAPI
        //$view->headScript()->appendFile('http://maps.googleapis.com/maps/api/js?v=3&sensor=true&language='.$lang);
        /*
        $view->headScript()->appendFile('/js/n2s.javascript.js');
        $view->headScript()->appendFile('/js/n2s.fullscreen.js');
        $view->headScript()->appendFile('/js/jquery/addons/jquery.elastic.source.js');
        $view->headScript()->appendFile('/js/jquery/jquery.viewport.mini.js');
        $view->headScript()->appendFile('/js/jquery/addons/jquery.tipTip.js');
        $view->headScript()->appendFile('/js/jquery/addons/lightbox/jquery.mousewheel-3.0.6.pack.js');
        $view->headScript()->appendFile('/js/jquery/addons/lightbox/source/jquery.n2lbox.pack.js');
        $view->headScript()->appendFile('/js/jquery/addons/lightbox/source/helpers/jquery.n2lbox-buttons.js');
        $view->headScript()->appendFile('/js/jquery/addons/lightbox/source/helpers/jquery.n2lbox-thumbs.js');
         * 
         */
    }
    
    protected function _initSetDefaultSchema() {
        $this->bootstrap('layout');
	$layout = $this->getResource('layout');
	$view = $layout->getView();
        $view->schema = '';
    }

    protected function _initJquery() 
    {
        $this->bootstrap('layout');
	$layout = $this->getResource('layout');
	$view = $layout->getView(); //get the view object

        //add the jquery view helper path into your project
        //ZendX_JQuery_View_Helper_JQuery::enableNoConflictMode();
        $view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        //$view->addHelperPath('ZendX/JQuery/View/Helper/JQuery', 'ZendX_JQuery_View_Helper_JQuery');
        $view->addHelperPath('N2S/View/Helper/', 'N2S_View_Helper');
		Zend_Controller_Action_HelperBroker::addHelper(new ZendX_JQuery_Controller_Action_Helper_AutoComplete());
		//Zend_Controller_Action_HelperBroker::addHelper(new N2S_View_Helper_DateTimePicker());

        //jquery lib includes here (default loads from google CDN)
        $view->jQuery()->enable()//enable jquery ; ->setCdnSsl(true) if need to load from ssl location
             ->setVersion('1.7.1')//jQuery version, automatically 1.5 = 1.5.latest
             ->setUiVersion('1.8')//jQuery UI version, automatically 1.8 = 1.8.latest
             //->addStylesheet('/js/jquery/css/jquery-ui-1.8.17.custom.css')//add the css
             ->uiEnable();//enable ui
        
        //For Offline
        //$view->jQuery()->addJavascriptFile('/js/jquery/jquery-1.7.1.min.js');
        //$view->jQuery()->addJavascriptFile('/js/jquery/jquery-ui-1.8.17.custom.min.js');
        
        //GoogleAPI
        /*
        $session = new Zend_Session_Namespace('userlanguage');
        if(isset($session->language)){
            $lang = $session->language;
        } else {
            $lang = Zend_Registry::get('config')->language->default->key;
        }
        $view->jQuery()->addJavascriptFile('http://maps.googleapis.com/maps/api/js?v=3&sensor=true&language='.$lang);
         * 
         */
        $view->jQuery()->addJavascriptFile('/js/n2s.fullscreen.js');

        $view->jQuery()->addJavascriptFile('/js/n2s.javascript.js');
        $view->jQuery()->addJavascriptFile('/js/n2s.notification.js');
        //Text->heigt auto
        $view->jQuery()->addJavascriptFile('/js/jquery/addons/jquery.elastic.source.js');
        //View in window
        $view->jQuery()->addJavascriptFile('/js/jquery/jquery.viewport.mini.js');
        //ToolTip
        $view->jQuery()->addJavascriptFile('/js/jquery/addons/jquery.tipTip.js');
        //LightBox
        $view->jQuery()->addJavascriptFile('/js/jquery/addons/lightbox/jquery.mousewheel-3.0.6.pack.js');
        $view->jQuery()->addJavascriptFile('/js/jquery/addons/lightbox/source/jquery.n2lbox.pack.js');
        $view->jQuery()->addJavascriptFile('/js/jquery/addons/lightbox/source/helpers/jquery.n2lbox-buttons.js');
        $view->jQuery()->addJavascriptFile('/js/jquery/addons/lightbox/source/helpers/jquery.n2lbox-thumbs.js');
    }
}

