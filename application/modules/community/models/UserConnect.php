<?php

/**
 * UserConnect.php
 * Description of UserConnect
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.09.2013 11:53:11
 * 
 */
class Community_Model_UserConnect
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
            $this->setDbTable('Community_Model_DbTable_UserConnect');
        }
        return $this->_dbTable;
    }

    public function getConnect($type, $connectid = null, $userID = null)
    {
        if($connectid != NULL || $userID != NULL){
            $table = $this->getDbTable();
            $select = $table->select();
            $select->where('type = ?',$type);
            if ($connectid != NULL)
                $select->where('connectid = ?',$connectid);
            if ($userID != NULL)
                $select->where('userid = ?',$userID);
            $result = $table->fetchRow($select);
            return $result;
        } else {
            return FALSE;
        }
    }

    public function setConnect($connectid, $type, $userID)
    {
        $check = $this->getConnect($type, $connectid, $userID);
        if(!isset($check)){
            $table = $this->getDbTable();
            $data = array(
                'connectid'=>$connectid,
                'type'=>$type,
                'userid'=>$userID
            );
            $table->insert($data);
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function delConnect($type, $userID)
    {
        $table = $this->getDbTable();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('type = ?', $type);
        $where[] = $table->getAdapter()->quoteInto('userid = ?', $userID);
        $table->delete($where);
    }
}
