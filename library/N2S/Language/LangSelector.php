<?php

/**
 * LangSelector.php
 * Description: Select language after request from url-params.
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 13:28:05
 * 
 */
class N2S_Language_LangSelector extends Zend_Controller_Plugin_Abstract
{
    public function  preDispatch(Zend_Controller_Request_Abstract $request) {
        $auth = Zend_Auth::getInstance();
        
        $session = new Zend_Session_Namespace('userlanguage');
        $lang = $request->getParam('lang','');
        $translate = new Zend_Translate(
            array(
                'adapter' => 'csv',
                'content' => APPLICATION_PATH . '/languages',
                'scan'    => Zend_Translate::LOCALE_DIRECTORY,
                'delimiter' => '=',
                'disableNotices' => true
            )
        );
        
        //Untranslate Logger:
        /*
        $logger = Zend_Registry::get('logger');
        $translate->setOptions(array(
            'log' => $logger,
            'logUntranslated' => true));
         * 
         */
        
        if ($auth->hasIdentity()) {
            $param = new Community_Model_UserParams();
            $langParam = $param->getParam($auth->getIdentity()->userid, 'lang');
        } else {
            $langParam = FALSE;
        }
        
        if ((isset($lang) && $lang != '[a-z]{2}') && ($translate->isAvailable($lang))){
            $locale = new Zend_Locale($lang);
            $translate->setLocale($lang);
            $session->language = $lang;
            $newLang = $lang;
        } else {
            if (!isset($session->language) && $langParam == FALSE){
                $locale = new Zend_Locale();
                if ($translate->isAvailable($locale->getLanguage())) {
                    $translate->setLocale($locale->getLanguage());
                    $session->language = $locale->getLanguage();
                    $newLang = $locale->getLanguage();
                } else {
                    $defaultLang = Zend_Registry::get('config')->language->default->key;
                    $locale = new Zend_Locale($defaultLang);
                    $translate->setLocale($defaultLang);
                    $session->language = $defaultLang;
                    $newLang = $defaultLang;
                }
            } elseif (isset($session->language) && $langParam == FALSE){
                $locale = new Zend_Locale($session->language);
                $translate->setLocale($session->language);
                $newLang = $session->language;
            } elseif (!isset($session->language) && $langParam != FALSE){
                $locale = new Zend_Locale($langParam);
                $translate->setLocale($langParam);
                $session->language = $langParam;
                $newLang = $langParam;
            } else {
                $locale = new Zend_Locale($langParam);
                $translate->setLocale($langParam);
                $session->language = $langParam;
                $newLang = $langParam;
            }
        }

        Zend_Registry::set('Zend_Translate', $translate);
        Zend_Registry::set('Zend_Locale', $locale);
        
        if ($auth->hasIdentity() && $newLang != $langParam) {
            $param->setParam($auth->getIdentity()->userid,'lang',$newLang,$langParam);
        }
    }
}
