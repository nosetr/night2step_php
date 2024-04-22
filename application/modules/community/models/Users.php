<?php

/**
 * Users.php
 * Description of Users
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 19.09.2012 19:28:11
 * 
 */
class Community_Model_Users
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
            $this->setDbTable('Community_Model_DbTable_Users');
        }
        return $this->_dbTable;
    }
    
    public function getUser($id)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('userid = ?',$id);

        $result = $table->fetchRow($select);
        
        return $result;
    }
    
    public function getMostActiv($count = 20)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('points > ?',0);
        $select->where('type = ?','profil');
        $select->order('points DESC');
        $select->limit($count);
        $result = $table->fetchAll($select);
        
        return $result;
    }
    
    public function getHomeUsers($lim = 4)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('type = ?','profil')
                ->where('deactivated = ?','0');
        $select->order('userid DESC');
        $select->limit($lim);
        $result = $table->fetchAll($select);
        
        return $result;
    }

    public function getUsersInList($list,$order = FALSE)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('userid IN (?)',$list)
                ->where('deactivated = ?','0');
        if($order == true)
            $select->order('lastvisitDate DESC');

        $result = $table->fetchAll($select);
        
        return $result;
    }

    public function updateLastVisit($id)
    {
        $table = $this->getDbTable();
        $Date = new Zend_Date(date('Y-m-d H:i:s'));
        $data = array(
            'lastvisitDate'=>$Date->get(Zend_Date::TIMESTAMP)
        );
        $where = $table->getAdapter()->quoteInto('userid = ?', $id);
        $table->update($data, $where);
    }

    public function updateAvatar($id,$photoID)
    {
        $table = $this->getDbTable();
        $data = array(
            'avatar'=>$photoID
        );
        $where = $table->getAdapter()->quoteInto('userid = ?', $id);
        $table->update($data, $where);
        
        $actData = array(
            'actor'=>$id,
            'title'=>'{actor} has uploaded a new avatar.',
            'app'=>'profile',
            'action'=>'avatar',
            'cid'=>$photoID,
            'comment'=>'photos'
            );
        $activs = new Community_Model_Activities();
        $activs->setActiv($actData);
    }
    
    public function updateProfil($id,$data)
    {
        $table = $this->getDbTable();
        
        $where = $table->getAdapter()->quoteInto('userid = ?', $id);
        $table->update($data, $where);
    }
}
