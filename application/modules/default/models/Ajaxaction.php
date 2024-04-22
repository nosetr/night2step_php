<?php

/**
 * Ajaxaction.php
 * Description of Ajaxaction
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 12:24:31
 * 
 */
class Default_Model_Ajaxaction
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
            $this->setDbTable('Default_Model_DbTable_Ajaxprocess');
        }
        return $this->_dbTable;
    }
    
    public function setAction($userID, $objectID, $storage, $action = '1')
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('objectid = ?',$objectID)
                        ->where('creator = ?',$userID)
                        ->where('action = ?',$action)
                        ->where('storage = ?',$storage)
                );
        if (count($result) == 0){
            $data = array(
                'objectid' => $objectID,
                'creator' => $userID,
                'action' => $action,
                'storage' => $storage
            );
            $table->insert($data);
        }
    }
    
    public function getActions($userID, $storage)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('creator = ?',$userID)
                        ->where('storage = ?',$storage)
                );
        return $result;
    }
    
    public function getDelComment($ID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                    $table->select()
                        ->where('objectid = ?',$ID)
                        ->where('storage = ?','comment')
                        ->where('action = ?',1)
                );
        return $result;
    }
    
    public function getDel($ID, $storage)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                    $table->select()
                        ->where('objectid = ?',$ID)
                        ->where('storage = ?',$storage)
                        ->where('action = ?',1)
                );
        return $result;
    }

    public function delActions($userID, $storage)
    {
        $table = $this->getDbTable();
        
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('creator = ?', $userID);
        $where[] = $table->getAdapter()->quoteInto('storage = ?', $storage);
        $table->delete($where);
    }

    public function delAction($userID, $photoID, $storage)
    {
        $table = $this->getDbTable();
        
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('creator = ?', $userID);
        $where[] = $table->getAdapter()->quoteInto('objectid = ?', $photoID);
        $where[] = $table->getAdapter()->quoteInto('storage = ?', $storage);
        $table->delete($where);
    }
}