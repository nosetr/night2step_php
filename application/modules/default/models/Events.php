<?php

/**
 * Events.php
 * Description of Events
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 26.10.2012 10:56:48
 * 
 */
class Default_Model_Events
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
            $this->setDbTable('Default_Model_DbTable_Events');
        }
        return $this->_dbTable;
    }

    //Check
    public function getAllEventsLast($minLat=null,$maxLat=null,
            $minLon=null,$maxLon=null,$rows = null,$start = false)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('published = ?','1');
        if($rows)
            $select->where('id IN (?)',$rows);
        if($minLat)
            $select->where('latitude >= ?',$minLat);
        if($maxLat)
            $select->where('latitude <= ?',$maxLat);
        if($minLon)
            $select->where('longitude >= ?',$minLon);
        if($maxLon)
            $select->where('longitude <= ?',$maxLon);
        if($start == true){
            $select->order('start ASC','id DESC');
        } else {
            //$select->order('start DESC');
            $select->order(array('start DESC',
                           'id DESC'));
        }
        $result = $table->fetchAll($select);
        return $result;
    }
    
    public function getGListToSend()
    {
        $created = Zend_Date::now();
        $time = $created->get(Zend_Date::TIMESTAMP);
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('published = ?','1');
        $select->where('gastlist = ?','1');
        $select->where('glistSendEMail = ?','1');
        $select->where('glistEndDate < ?',$time);
        $select->where('glistEMailSended = ?','0');
        $select->order('glistEndDate ASC','id ASC');
        $select->limit(20);
        $result = $table->fetchAll($select);
        return $result;
    }

    public function getActualEventsLast($date,$jump = null,$minLat=null,$maxLat=null,$minLon=null,$maxLon=null)
    {
        $from = $date.'-31';
        if($jump){
            $til = $date.'-01';
        } else {
            $til = $jump.'-01';
        }
        $table = $this->getDbTable();
        
        $select = $table->select();
        $select->where('published = ?','1');
        $select->where('partyday <= ?',$from);
        $select->where('partyday >= ?',$til);
        if($minLat)
            $select->where('latitude >= ?',$minLat);
        if($maxLat)
            $select->where('latitude <= ?',$maxLat);
        if($minLon)
            $select->where('longitude >= ?',$minLon);
        if($maxLon)
            $select->where('longitude <= ?',$maxLon);
        $select->order('partyday DESC');
        $select->group('partyday');
        $result = $table->fetchAll($select);
        return $result;
    }

    public function getNextEventLast($date,$minLat=null,$maxLat=null,$minLon=null,$maxLon=null)
    {
        $from = $date.'-01';
        $table = $this->getDbTable();
        
        $select = $table->select();
        $select->where('published = ?','1');
        $select->where('partyday < ?',$from);
        if($minLat)
            $select->where('latitude >= ?',$minLat);
        if($maxLat)
            $select->where('latitude <= ?',$maxLat);
        if($minLon)
            $select->where('longitude >= ?',$minLon);
        if($maxLon)
            $select->where('longitude <= ?',$maxLon);
        $select->order('partyday DESC');
        $select->group('partyday');
        $select->limit(1);
        $result = $table->fetchAll($select);
        return $result;
    }

    public function getToDayEventsLast($eventdate,$minLat=null,$maxLat=null,$minLon=null,$maxLon=null)
    {
        $table = $this->getDbTable();
        
        $select = $table->select();
        $select->where('published = ?','1');
        $select->where('partyday = ?',$eventdate);
        if($minLat)
            $select->where('latitude >= ?',$minLat);
        if($maxLat)
            $select->where('latitude <= ?',$maxLat);
        if($minLon)
            $select->where('longitude >= ?',$minLon);
        if($maxLon)
            $select->where('longitude <= ?',$maxLon);
        $select->order('start');
        $result = $table->fetchAll($select);
        return $result;
    }
    
    public function getEvent($id)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                $table->select()
                    ->where('id = ?',$id)
                );
        return $result;
    }
    
    public function getUserEvents($user,$published = 1,$permis = 0)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        if($published == 1){
            $select->where('published >= ?',$published);
        } else {
            $select->where('published = ?',$published);
        }
        $select->where('creator = ?',$user)
                    ->where('permission <= ?',$permis)
                    ->order(array('start DESC',
                           'id DESC'));
        $result = $table->fetchAll($select);
        return $result;
    }

    public function updateEvent($id,$data)
    {
        $table = $this->getDbTable();
        $where = $table->getAdapter()->quoteInto('id = ?', $id);
        $table->update($data, $where);
    }
        
    public function eventImgUpdate($ID, $img)
    {
        $comuserTab = $this->getDbTable();
        $data = array(
            'photoid'=> $img
        );
        $where = $comuserTab->getAdapter()->quoteInto('id = ?', $ID);
        $comuserTab->update($data, $where);
    }
    
    public function updateEventsThumb($curevent,$x,$y,$w)
    {
        $event = $this->getEvent($curevent);
        $photos = new Community_Model_Photos();
        $photo = $photos->getSimplePhoto($event->photoid);
        
        $filter = new N2S_Filter_File_Cropthumb(array(
                                'width' => 200,
                                'thumbwidth' => 134,
                                'thumbheight' => 134,
                                'x'=>$x,
                                'y'=>$y,
                                'w'=>$w,
                                'name'  => 'thumb_'
                            ));
        $filter->filter(BASE_PATH.'/'.$photo->image);
    }
    
    public function setEvent($userID,$Title,$Description = '',$locid = 0)
    {
        $table = $this->getDbTable();
        $created = Zend_Date::now();
        $data = array(
            'creator'=>$userID,
            'title'=>trim($Title),
            'description'=>trim($Description),
            'created'=>$created->get(Zend_Date::TIMESTAMP),
            'locid'=>$locid
        );
        $table->insert($data);
        $lastID = $table->getAdapter()->lastInsertId();
        
        return $lastID;
    }
    
    public function delEventsDraft($userID,$eventID)
    {
        $table = $this->getDbTable();
        
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('published = ?', '0');
        $where[] = $table->getAdapter()->quoteInto('id = ?', $eventID);
        $where[] = $table->getAdapter()->quoteInto('creator = ?', $userID);
        $table->delete($where);
    }
}