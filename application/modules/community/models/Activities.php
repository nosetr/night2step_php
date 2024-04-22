<?php

/**
 * Activities.php
 * Description of Activities
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 20.03.2013 17:37:35
 * 
 */
class Community_Model_Activities
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
            $this->setDbTable('Community_Model_DbTable_Activities');
        }
        return $this->_dbTable;
    }
    
    public function getActivs($last = NULL,$array = null,$cid = 0,$app = null,$back = FALSE)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        if($last > 0 && $array == null)
            ($back == FALSE)?$select->where('id < ?',$last):$select->where('id > ?',$last);
        if($array != null && is_array($array)){
            $select->where('actor IN (?)',$array);
            if($last > 0)
                ($back == FALSE)?$select->where('id < ?',$last):$select->where('id > ?',$last);
            $select->orWhere('target IN (?)',$array);
            if($last > 0)
                ($back == FALSE)?$select->where('id < ?',$last):$select->where('id > ?',$last);
        } else {
            if($last > 0)
                ($back == FALSE)?$select->where('id < ?',$last):$select->where('id > ?',$last);
            if($cid > 0)
                $select->where('cid = ?',$cid);
            if($app != null)
                $select->where('app = ?',$app);
        }
        //$select->where('app = ?','albums');
        //$select->where('action = ?','uploaded');
        //$select->where('app = ?','friends');
        //$select->where('app = ?','profile');
        //$select->where('action = ?','post');
        //$select->where('app = ?','events');
        $select->order('id DESC');
        
        $result = $table->fetchAll($select);
        return $result;
    }
    
    public function getDayActiv($actor,$cid,$app,$action) //If params must be edit (AlbumUpload)
    {
        $now = Zend_Date::now();
        $middle = $now->get(Zend_Date::TIMESTAMP);
        $from = $middle - 43200;// - 12 Std.
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('actor = ?',$actor);
        $select->where('cid = ?',$cid);
        $select->where('app = ?',$app);
        $select->where('action = ?',$action);
        $select->where('created > ?',$from);
        $select->limit(1);
        $select->order('id DESC');
        
        $result = $table->fetchAll($select);
        return $result;
    }

    public function getActivID($id)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('id = ?',$id);
        
        $result = $table->fetchRow($select);
        return $result;
    }
    
    public function setActiv($data)
    {
        if(isset($data) && is_array($data)){
            $Date = Zend_Date::now();
            $created = $Date->get(Zend_Date::TIMESTAMP);
            $data['created'] = $created;
            $table = $this->getDbTable();
            $table->insert($data);
        }
    }
    
    public function updateActiv($id,$data,$updateCreated = FALSE)
    {
        if($updateCreated == TRUE){
            $Date = Zend_Date::now();
            $created = $Date->get(Zend_Date::TIMESTAMP);
            $data['created'] = $created;
        }
        $table = $this->getDbTable();
        $where = $table->getAdapter()->quoteInto('id = ?', $id);
        $table->update($data, $where);
    }

    public function delActivs($id)
    {
        $table = $this->getDbTable();
        $where = $table->getAdapter()->quoteInto('id = ?',$id);
        $table->delete($where);
    }
}
