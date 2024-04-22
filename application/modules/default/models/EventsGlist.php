<?php

/**
 * EventsGlist.php
 * Description of EventsGlist
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 14.01.2013 11:12:02
 * 
 */
class Default_Model_EventsGlist {
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
            $this->setDbTable('Default_Model_DbTable_EventsGList');
        }
        return $this->_dbTable;
    }
    
    public function getMembers($eventID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('eventid = ?',$eventID)
                        ->order('created DESC')
                );
        return $result;
    }
    
    public function checkMember($eventID,$userID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                    $table->select()
                        ->where('eventid = ?',$eventID)
                        ->where('memberid = ?',$userID)
                );
        return $result;
    }

    public function setMember($eventID,$userID)
    {
        $check = $this->checkMember($eventID, $userID);
        $table = $this->getDbTable();
        if(count($check)>0){
            $result = array('error'=>TRUE,'message'=>'You are allready on the guest list');
        } else {
            $created = Zend_Date::now();
            $data = array(
                'memberid'=>$userID,
                'eventid'=>$eventID,
                'created'=>$created->get(Zend_Date::TIMESTAMP)
            );
            $table->insert($data);
            $result = array('error'=>FALSE,'message'=>'You\'re on the guest list');
        }
        return $result;
    }
    
    public function delMember($eventID,$userID)
    {
        $table = $this->getDbTable();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('eventid = ?', $eventID);
        $where[] = $table->getAdapter()->quoteInto('memberid = ?',$userID);
        $table->delete($where);
        $result = array('error'=>FALSE,'message'=>'deljoin');//Message nicht übersetzen!
        return $result;
    }
}
