<?php

/**
 * AuthAdapter.php
 * Description of AuthAdapter
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 15:55:34
 * 
 */
class N2S_Auth_AuthAdapter extends Zend_Auth_Adapter_DbTable
{
    /**
     * $_identityKey - IdentityColumn value
     *
     * @var string
     */
    protected $_identityKey = 'email';
    protected $_passKey = 'password';
    
    public function __construct($identityKey = null, $passKey = null)
    {
        if (null !== $identityKey) {
            $this->setIdentityKey($identityKey);
        }
        
        if (null !== $passKey) {
            $this->setPassKey($passKey);
        }
        
        $dbAdapter = Zend_Registry::get("dbAdapter");
        parent::__construct($dbAdapter);
        if (Zend_Registry::isRegistered('TABLE_PREFIX')){
            $pref = Zend_Registry::get('TABLE_PREFIX');
            $this->setTableName($pref .'users');
        }else{
            $this->setTableName('users');
        }
        $this->setIdentityColumn($this->_identityKey);
        $this->setCredentialColumn($this->_passKey);
    }
    
    public function setIdentityKey($identityKey)
    {
        $this->_identityKey = $identityKey;
        return $this;
    }
    
    public function setPassKey($passKey)
    {
        $this->_passKey = $passKey;
        return $this;
    }
}