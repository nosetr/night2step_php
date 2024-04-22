<?php

/**
 * AccessControl.php
 * Description of AccessControl
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 14:54:48
 * 
 */
class N2S_Auth_AccessControl extends Zend_Controller_Plugin_Abstract{
    public function __construct(Zend_Auth $auth, Zend_Acl $acl)
    {
      $this->_auth = $auth;
      $this->_acl  = $acl;
    }
    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if ($this->_auth->hasIdentity() &&is_object($this->_auth->getIdentity())) {
            $role = $this->_auth->getIdentity()->usertype;
        } else {
            $role = 'guest';
        }

        $module     = $request->getModuleName();
        $controller = $request->getControllerName();
        
        if (!$this->_acl->has($module))
            $module = NULL;
        
        if (!$this->_acl->isAllowed($role, $module, $controller)) {
            if ($this->_auth->hasIdentity()) {
                // angemeldet, aber keine Rechte -> Fehler!
                $request->setModuleName('default');
                $request->setControllerName('error');
                $request->setActionName('noAccess');
            } else {
                //nicht angemeldet -> Login
                $request->setModuleName('community');
                $request->setControllerName('index');
                $request->setActionName('index');
            }
        }
      
    }
}