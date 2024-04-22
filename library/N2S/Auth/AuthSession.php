<?php

/**
 * AuthSession.php
 * Description of AuthSession
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 25.01.2013 11:36:12
 * 
 */
class N2S_Auth_AuthSession extends Zend_Controller_Plugin_Abstract
{
    public function  preDispatch(Zend_Controller_Request_Abstract $request) {
        
        $auth = Zend_Auth::getInstance();
        $defaultSess = Zend_Registry::get('config')->authsession->default->key;
        $changedSess = Zend_Registry::get('config')->authsession->changed->key;
        
        $auth->setStorage(new Zend_Auth_Storage_Session($defaultSess));
        
        if($auth->hasIdentity()){
            $reset = (bool)$request->getParam('resetprofil',FALSE);
            if($reset == TRUE){
                Zend_Session::namespaceUnset($changedSess);
            } else {
                $change = (int)$request->getParam('changeprofil',0);
                if($change > 0){
                    $check = new Community_Model_Admins();
                    $access = $check->checkAccess($change);
                    if($access == TRUE){
                        $userID = $auth->getIdentity()->userid;
                        $auth->setStorage(new Zend_Auth_Storage_Session($changedSess));
                        $check->changeProfil($userID,$change);
                    }
                } elseif(Zend_Session::namespaceIsset($changedSess) == TRUE) {
                    if(isset($_SESSION['__ZF'][$changedSess]['ENT']) &&
                            $_SESSION['__ZF'][$changedSess]['ENT'] - time() <= 0){
                        Zend_Session::namespaceUnset($changedSess);
                    } else {
                        $auth->setStorage(new Zend_Auth_Storage_Session($changedSess));
                    }
                }
            }
        }
    }
}
