<?php

/**
 * EventsMembers.php
 * Description of EventsMembers
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 11.01.2013 09:52:26
 * 
 */
class Default_Model_EventsMembers {
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
            $this->setDbTable('Default_Model_DbTable_EventsMembers');
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
    
    public function getJoinMembers($eventID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('eventid = ?',$eventID)
                        ->where('status = ?',1)
                        ->order('created DESC')
                );
        return $result;
    }
    
    public function getJoinMembersFriends($eventID,$list)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('eventid = ?',$eventID)
                        ->where('status = ?',1)
                        ->where('memberid IN (?)',$list)
                        ->order('created DESC')
                );
        return $result;
    }
    
    public function getMaybeMembers($eventID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('eventid = ?',$eventID)
                        ->where('status = ?',0)
                        ->order('created DESC')
                );
        return $result;
    }
    
    public function getMaybeMembersFriends($eventID,$list)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('eventid = ?',$eventID)
                        ->where('status = ?',0)
                        ->where('memberid IN (?)',$list)
                        //->order('created DESC')
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

    public function setMember($eventID,$status,$userID)
    {
        $check = $this->checkMember($eventID, $userID);
        $table = $this->getDbTable();
        ($status == 1)?$text='You have joined this event':$text='You will maybe join this event';
        if(count($check)>0){
            if($check->status != $status){
                $data = array(
                    'status'=>$status
                );
                $where = array();
                $where[] = $table->getAdapter()->quoteInto('eventid = ?', $eventID);
                $where[] = $table->getAdapter()->quoteInto('memberid = ?',$userID);
                $table->update($data, $where);
                $result = array('error'=>FALSE,'message'=>$text);
            } else {
                $result = array('error'=>TRUE,'message'=>'You have joined this event');
            }
        } else {
            $created = Zend_Date::now();
            $data = array(
                'memberid'=>$userID,
                'status'=>$status,
                'eventid'=>$eventID,
                'created'=>$created->get(Zend_Date::TIMESTAMP)
            );
            $table->insert($data);
            $result = array('error'=>FALSE,'message'=>$text);
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
