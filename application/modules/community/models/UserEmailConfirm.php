<?php

/**
 * UserEmailConfirm.php
 * Description of UserEmailConfirm
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 01.03.2013 09:53:18
 * 
 */
class Community_Model_UserEmailConfirm
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
            $this->setDbTable('Community_Model_DbTable_UserEmailConfirm');
        }
        return $this->_dbTable;
    }
    
    public function getConfirm($userID)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('userid = ?',$userID);
        $result = $table->fetchRow($select);
        return $result;
    }

    public function setConfirm($userID,$email)
    {
        $result = FALSE;
        $check = $this->getConfirm($userID);
        if(isset($check) && $check->email != trim($email)){
            $result = $this->updateConfirm($userID,$email);
        } elseif (!isset($check)) {
            $table = $this->getDbTable();
            $activ = $this->_setActivation();
            $data = array(
                'userid'=>$userID,
                'email'=>trim($email),
                'code'=>$activ['cryp']
            );
            $table->insert($data);
            $result = $activ['key'];
        }
        
        return $result;
    }
    
    public function updateConfirm($userID,$email)
    {
        $table = $this->getDbTable();
        $activ = $this->_setActivation();
        $new = $activ['key'];
        $data = array(
            'email'=>trim($email),
            'code'=>$activ['cryp']
        );
        $where = $table->getAdapter()->quoteInto('userid = ?', $userID);
        $table->update($data, $where);
        return $new;
    }
    
    public function delConfirm($userID,$code)
    {
        $result = FALSE;
        $check = $this->_checkConfirm($userID, $code);
        if($check == TRUE){
            $confirm = $this->getConfirm($userID);
            $data = array('email'=>$confirm->email);
            $users = new Community_Model_Users();
            $users->updateProfil($userID, $data);
            
            $table = $this->getDbTable();
            $where = $table->getAdapter()->quoteInto('userid = ?',$userID);
            $table->delete($where);
            $result = TRUE;
        }
        return $result;
    }
    
    protected function _checkConfirm($userID,$code)
    {
        $result = FALSE;
        $selectpass = $this->getConfirm($userID);

        if (isset($selectpass)){
            $parts	= explode( ':', $selectpass->code );
            $salt	= @$parts[1];
            $crypt = md5($code.$salt).':'.$salt;
            
            if ($crypt === $selectpass->code){
                $result = TRUE;
            }
        }
        return $result;
    }

    protected function _setActivation()
    {
        mt_srand((double)microtime()*1000000);
        $confirmKey = md5(uniqid(rand(), true));
        $salt = md5(uniqid(rand(), true));
        $cryp = md5($confirmKey.$salt).':'.$salt;
        
        $result = array('cryp'=>$cryp,'key'=>$confirmKey);
        return $result;
    }
}
