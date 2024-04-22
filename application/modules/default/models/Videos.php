<?php

/**
 * Videos.php
 * Description of Videos
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 21.04.2013 09:53:56
 * 
 */
class Default_Model_Videos {
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
            $this->setDbTable('Default_Model_DbTable_Videos');
        }
        return $this->_dbTable;
    }
    
    public function getMovie($id)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('id = ?',$id);
        $result = $table->fetchRow($select);
        return $result;
    }
    
    public function checkMovie($creator,$video_id,$type)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('creator = ?',$creator);
        $select->where('video_id = ?',$video_id);
        $select->where('type = ?',$type);
        $result = $table->fetchRow($select);
        return $result;
    }

    public function setMovie($data)
    {
        $result = 0;
        if(isset($data) && is_array($data)){
            if(isset($data['creator']) && isset($data['video_id']) && isset($data['type'])){
                $check = $this->checkMovie($data['creator'], $data['video_id'], $data['type']);
                if(!isset($check)){
                    $Date = Zend_Date::now();
                    $created = $Date->get(Zend_Date::TIMESTAMP);
                    $data['created'] = $created;
                    $table = $this->getDbTable();
                    $table->insert($data);
                    
                    $result = $table->getAdapter()->lastInsertId();
                } else {
                    $result = $check->id;
                }
            }
        }
        return $result;
    }
    
    public function updateMovie($id,$data)
    {
        $table = $this->getDbTable();
        $where = $table->getAdapter()->quoteInto('id = ?', $id);
        $table->update($data, $where);
    }

    public function delMovie($id)
    {
        $table = $this->getDbTable();
        $where = $table->getAdapter()->quoteInto('id = ?',$id);
        $table->delete($where);
    }
}
