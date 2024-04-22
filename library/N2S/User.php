<?php

/**
 * User.php
 * Description of User
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 28.01.2013 14:42:05
 * 
 */
class N2S_User
{
    protected static $_defaultSess = null;
    protected static $_changedSess = null;
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct()
    {}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}
    
    public function curuser()
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $defaultSess = self::__getDefaultSess();
            $changedSess = self::__getChangedSess();
            if($auth->getStorage()->getNamespace() == $changedSess && Zend_Session::namespaceIsset($defaultSess) == TRUE){
                $defSession = new Zend_Session_Namespace($defaultSess);
                $userID = $defSession->storage->userid;
            } else {
                $userID = $auth->getIdentity()->userid;
            }
        } else {
            $userID = 0;
        }
        
        return $userID;
    }
    
    private function __getDefaultSess()
    {
        if (null === self::$_defaultSess) {
            self::__setDefaultSess();
        }
        return self::$_defaultSess;
    }
    
    private function __getChangedSess()
    {
        if (null === self::$_changedSess) {
            self::__setChangedSess();
        }
        return self::$_changedSess;
    }
    
    private function __setDefaultSess()
    {
        self::$_defaultSess = Zend_Registry::get('config')->authsession->default->key;
    }
    
    private function __setChangedSess()
    {
        self::$_changedSess = Zend_Registry::get('config')->authsession->changed->key;
    }
}
