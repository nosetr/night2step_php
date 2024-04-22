<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * ModuleNavigation.php
 * Description of ModuleNavigation
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 14:37:00
 * 
 */
class N2S_Navigation_ModuleNavigation extends Zend_Controller_Plugin_Abstract {
    public function preDispatch (Zend_Controller_Request_Abstract $request) {
        $module = $request->getModuleName();
        if(empty($module)) $module = "default";
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->initView();
        $view = $viewRenderer->view;

        if(file_exists(APPLICATION_PATH . '/navigation/' . strtolower($module) . '_navigation.xml')) {
                $config = new Zend_Config_Xml(APPLICATION_PATH . '/navigation/' . strtolower($module) . '_navigation.xml', 'nav');
        } else {
                $config = new Zend_Config_Xml(APPLICATION_PATH . '/navigation/default_navigation.xml', 'nav');                        
        }
        $acl = new N2S_Auth_Acl();
        if (Zend_Auth::getInstance()->hasIdentity() &&is_object(Zend_Auth::getInstance()->getIdentity())) {
            $role = Zend_Auth::getInstance()->getIdentity()->usertype;
        } else {
            $role = 'guest';
        }
        $navigation = new Zend_Navigation($config);
        
        if(Zend_Auth::getInstance()->hasIdentity()
                && Zend_Auth::getInstance()->getIdentity()->type != 'profil')//Remove Friends if not profil
        {
            $navFr = $navigation->findOneByLabel('Friends');
            $navigation->findOneByLabel('Community')->removePage($navFr);
        }
        
        $view->navigation($navigation)
                ->setAcl($acl)
                ->setRole($role);
    }

}
