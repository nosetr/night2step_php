<?php

/**
 * Notifications.php
 * Description of Notifications
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 13:17:50
 * 
 */
class Community_Model_Notifications
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
            $this->setDbTable('Community_Model_DbTable_Notifications');
        }
        return $this->_dbTable;
    }
    
    public function getNoti($id)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                    $table->select()
                        ->where('id = ?',$id)
                );
        return $result;
    }
    
    public function getUserNoti($userID, $limit = 0, $last = 0)
    {
        $table = $this->getDbTable();
        
        $select = $table->select()
                ->where('target = ?',$userID)
                ->order('created DESC');
        if ($last > 0)
            $select->where('id < ?',$last);
        if ($limit > 0)
            $select->limit($limit);
        
        $result = $table->fetchAll($select);
        return $result;
    }
    
    public function getAjaxRead($userID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('target = ?',$userID)
                        ->where('ajax_read = ?',0)
                        ->order('created DESC')
                );
        return $result;
    }
    
    public function setUserNoti($actor,$target,$title,$app,$content='',$contentid = 0)
    {
        $table = $this->getDbTable();
        $Date = new Zend_Date(date('Y-m-d H:i:s'));
        $created = $Date->get(Zend_Date::TIMESTAMP);
        
        $data = array(
                'actor'=>$actor,
                'target'=>$target,
                'title'=>$title,
                'content'=>$content,
                'contentid'=>$contentid,
                'app'=>$app,
                'created'=>$created
            );
        
        $table->insert($data);
    }
    
    public function setAjaxRead($id)
    {
        $table = $this->getDbTable();
        
        $data = array(
                'ajax_read'=>1
            );
        
        $where = $table->getAdapter()->quoteInto('id = ?', $id);
        $table->update($data, $where);
    }
    
    public function delUserNoti($ID,$userID)
    {
        $table = $this->getDbTable();
        
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('target = ?',$userID);
        $where[] = $table->getAdapter()->quoteInto('id = ?', $ID);
        $table->delete($where);
    }
}