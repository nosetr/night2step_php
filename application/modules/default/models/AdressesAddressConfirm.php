<?php

/**
 * AdressesAddressConfirm.php
 * Description of AdressesAddressConfirm
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.02.2013 10:20:09
 * 
 */
class Default_Model_AdressesAddressConfirm {
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
            $this->setDbTable('Default_Model_DbTable_AdressesAddressConfirm');
        }
        return $this->_dbTable;
    }
    
    public function getConfirm($userID,$objectID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('creator = ?',$userID)
                        ->where('objectid = ?',$objectID)
                );
        return $result;
    }
    
    public function setConfirm($userID,$objectID,$address,$country)
    {
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
                'creator'=>$userID,
                'objectid'=>$objectID,
                'code'=>$this->_generateCode(),
                'address'=>$address,
                'country'=>$country
            );
            $table->insert($data);
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function checkConfirm($userID,$objectID,$code)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                    $table->select()
                        ->where('creator = ?',$userID)
                        ->where('objectid = ?',$objectID)
                        ->where('code = ?',$code)
                        ->where('send = ?',1)
                );
        if(isset($result)){
            $adresses = new Default_Model_Adresses();
            $data = array('address'=>$result['address'],'country'=>$result['country']);
            $adresses->updateAdress($objectID, $data);
            $this->delConfirm($userID, $objectID);
        }
        return $result;
    }
    
    public function delConfirm($userID,$objectID)
    {
        $table = $this->getDbTable();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('creator = ?',$userID);
        $where[] = $table->getAdapter()->quoteInto('objectid = ?',$objectID);
        $table->delete($where);
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