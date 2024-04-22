<?php

/**
 * Admins.php
 * Description of Admins
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 24.01.2013 18:48:23
 * 
 */
class Community_Model_Admins
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
            $this->setDbTable('Community_Model_DbTable_Admins');
        }
        return $this->_dbTable;
    }
    
    public function checkAccess($id)
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity() && $auth->getIdentity()->type == 'profil'){
            $userID = $auth->getIdentity()->userid;
            $table = $this->getDbTable();
            $result = $table->fetchRow(
                    $table->select()
                        ->where('userid = ?', $userID)
                        ->where('objectid = ?', $id)
                    );
            
            if(isset($result)){
                return TRUE;
            }
        }
        return FALSE;
    }

    public function checkObject($objectID,$type)
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity() && $auth->getIdentity()->type == 'profil'){
            $userID = $auth->getIdentity()->userid;
            $check = $this->getAdmin($userID, $objectID, $type);
            if(isset($check))
                return TRUE;            
        }
        
        return FALSE;
    }
    
    public function changeProfil($userID,$id)
    {
        $check = $this->getAccess($userID,$id);
        if(isset($check)){
            $accessModel = new Community_Model_Access();
            $defaultSess = Zend_Registry::get('config')->authsession->default->key;
            if(isset($_SESSION['__ZF'][$defaultSess]['ENT'])){
                $rememb = '';
                $expsec = $_SESSION['__ZF'][$defaultSess]['ENT'] - time();
            } else {
                $rememb = 'Checked';
                $expsec = NULL;
            }
            $access = $accessModel->setAccess($id, $check->password,$rememb,'','userid',$expsec);
            return $access;
        }
        
        return FALSE;
    }
    
    public function getAccess($userID,$id)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                $table->select()
                    ->where('userid = ?', $userID)
                    ->where('objectid = ?', $id)
                );
        return $result;
    }

    public function getCuruser($objectID,$type,$profil=true)
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $userID = N2S_User::curuser();
            $check = $this->getAdmin($userID, $objectID, $type);
            if(isset($check)){
                if($auth->getIdentity()->userid == $check->objectid
                        || $auth->getIdentity()->type == 'profil' || $profil == FALSE){
                    return $check->objectid;
                }
            }
            return $userID;
        }
        
        return 0;
    }

    public function getAdmin($userID,$objectID,$type)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                $table->select()
                    ->where('userid = ?', $userID)
                    ->where('objectid = ?', $objectID)
                    ->where('profiltype = ?', $type)
                );
        return $result;
    }
    
    public function findAllAccess($userID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                $table->select()
                    ->where('userid = ?', $userID)
                );
        return $result;
    }
    
    public function findSoleAdmin($userID)// muss geändert werden, wenn nicht nur venue
    {
        $result = array();
        $all = $this->findAllAccess($userID);
        if(count($all) > 0){
            foreach ($all as $a){
                $f = $this->findAdmins($a->objectid, 'venue', null, $userID);
                if(count($f) == 0){
                    $result[] = $a->objectid;
                }
            }
        }
        return $result;
    }

    public function findAdmins($objectID,$type,$notInArray = null,$notUser = null)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('objectid = ?', $objectID);
        $select->where('profiltype = ?', $type);
        if($notUser != NULL)
            $select->where('userid != ?',$notUser);
        if($notInArray != NULL)
            $select->where('userid NOT IN ?',$notInArray);
        $result = $table->fetchAll($select);
        return $result;
    }

    public function setAdmin($userID,$objectID,$type,$pass)
    {
        $check = $this->getAdmin($userID, $objectID, $type);
        if(!isset($check)){
            $table = $this->getDbTable();
            $registerDate = new Zend_Date(date('Y-m-d H:i:s'));
            $data = array(
                'created' => $registerDate->get(Zend_Date::TIMESTAMP),
                'userid' => $userID,
                'objectid'=>$objectID,
                'profiltype'=>$type,
                'password'=>$pass
            );
            $table->insert($data);
        }
    }
    
    public function delAdmin($userID,$objectID,$type)
    {
        $curuser = N2S_User::curuser();
        $check = $this->getAdmin($userID, $objectID, $type);
        if(isset($check) && $curuser == $userID){
            $table = $this->getDbTable();
            $where = array();
            $where[] = $table->getAdapter()->quoteInto('userid = ?',$userID);
            $where[] = $table->getAdapter()->quoteInto('objectid = ?',$objectID);
            $where[] = $table->getAdapter()->quoteInto('profiltype = ?',$type);
            $table->delete($where);
            return TRUE;
        }
        return FALSE;
    }
}
