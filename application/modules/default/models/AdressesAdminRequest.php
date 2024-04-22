<?php

/**
 * AdressesAdminRequest.php
 * Description of AdressesAdminRequest
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 05.02.2013 16:27:00
 * 
 */
class Default_Model_AdressesAdminRequest
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
            $this->setDbTable('Default_Model_DbTable_AdressesAdminRequest');
        }
        return $this->_dbTable;
    }
    
    public function getRequest($userID,$object)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                    $table->select()
                        ->where('request_to = ?',$userID)
                        ->where('objectid = ?',$object)
                );
        return $result;
    }
    
    public function acceptRequest($userID,$objectID,$type = NULL)
    {
        $curuser = N2S_User::curuser();
        if($curuser > 0 && $objectID > 0 && $userID > 0 && $type != NULL){
            $check = $this->getRequest($userID, $objectID);
            if(isset($check) && $check->profiltype == $type && $curuser == $userID){
                $admins = new Community_Model_Admins();
                $access = $admins->getAccess($check->request_from, $objectID);
                $newAccess = $admins->getAccess($curuser, $objectID);
                if(isset($access) && !isset($newAccess)){
                    $del = $this->delRequest($userID, $objectID, $type);
                    if($del == true)
                        $admins->setAdmin($userID, $objectID, $type, $access->password);
                    return $del;
                }
            }
        }
        return FALSE;
    }

    public function setRequest($userID,$object,$type = NULL)
    {
        $curuser = N2S_User::curuser();
        if($curuser > 0 && $object > 0 && $userID > 0 && $type != NULL){
            $check = $this->getRequest($userID, $object);
            if(!isset($check)){
                $admins = new Community_Model_Admins();
                $access = $admins->getAccess($curuser, $object);
                $newAccess = $admins->getAccess($userID, $object);
                if(isset($access) && !isset($newAccess)){
                    $users = new Community_Model_Users();
                    $chUser = $users->getUser($object);
                    if(isset($chUser) && $chUser->type == $type){
                        $table = $this->getDbTable();
                        $data = array(
                            'request_from'=>$curuser,
                            'request_to'=>$userID,
                            'objectid'=>$object,
                            'profiltype'=>$type
                        );
                        $table->insert($data);
                        return TRUE;
                    }
                }
            }
        }
        
        return FALSE;
    }
    
    public function delRequest($userID,$objectID,$type = NULL)
    {
        $curuser = N2S_User::curuser();
        if($curuser > 0 && $objectID > 0 && $userID > 0 && $type != NULL){
            $check = $this->getRequest($userID, $objectID);
            if(isset($check)){
                $admins = new Community_Model_Admins();
                $access = $admins->getAccess($curuser, $objectID);
                $newAccess = $admins->getAccess($userID, $objectID);
                if((isset($access) && !isset($newAccess)) || ($curuser == $userID && !isset($newAccess))){
                    $table = $this->getDbTable();
                    $where = array();
                    $where[] = $table->getAdapter()->quoteInto('request_to = ?',$userID);
                    $where[] = $table->getAdapter()->quoteInto('objectid = ?',$objectID);
                    $table->delete($where);
                    return TRUE;
                }
            }
        }
        
        return FALSE;
    }
}
