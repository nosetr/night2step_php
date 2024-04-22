<?php

/**
 * AdressesAdminConfirm.php
 * Description of AdressesAdminConfirm
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 21.01.2013 16:33:55
 * 
 */
class Default_Model_AdressesAdminConfirm
{
    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Default_Model_DbTable_AdressesAdminConfirm');
        }
        return $this->_dbTable;
    }
    
    public function getConfirm($userID,$objectID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('userid = ?',$userID)
                        ->where('objectid = ?',$objectID)
                );
        return $result;
    }
    
    public function checkConfirm($objectID,$code)
    {
        $userID = N2S_User::curuser();
        
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('userid = ?',$userID)
                        ->where('objectid = ?',$objectID)
                        ->where('code = ?',$code)
                        ->where('send = ?',1)
                );
        if(count($result) > 0)
            $this->_setAdmin ($objectID, $userID);
        return $result;
    }
    
    protected function _setAdmin($objectID,$userID)
    {
        $this->delConfirm($objectID);
        $adresses = new Default_Model_Adresses();
        $adresses->setAdmin($objectID, $userID);
    }

    public function setConfirm($objectID)
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $userID = N2S_User::curuser();
            
            $check = $this->getConfirm($userID, $objectID);
            $send = 0;
            if(count($check)>0){
                foreach ($check as $ch){
                    if($ch->send == 0){
                        $send = 1;
                    }
                }
            }
            if($send == 0){
                $table = $this->getDbTable();
                $created = Zend_Date::now();
                $data = array(
                    'created'=>$created->get(Zend_Date::TIMESTAMP),
                    'userid'=>$userID,
                    'objectid'=>$objectID,
                    'code'=>$this->_generateCode()
                );
                $table->insert($data);
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    public function delConfirm($objectID)
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $userID = N2S_User::curuser();
            $table = $this->getDbTable();
            $where = array();
            $where[] = $table->getAdapter()->quoteInto('userid = ?',$userID);
            $where[] = $table->getAdapter()->quoteInto('objectid = ?',$objectID);
            $table->delete($where);
        }
    }

    protected function _generateCode($pwlen=6)
    {
	mt_srand();
        $salt = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZ";
	
        $pw = '';
	for($i=0;$i<$pwlen;$i++)
	{
		$pw .= $salt[mt_rand(0, strlen($salt)-1)];
	}
	return $pw;
    }
}
