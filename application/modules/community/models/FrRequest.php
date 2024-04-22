<?php

/**
 * FrRequest.php
 * Description of FrRequest
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 17.10.2012 18:20:05
 * 
 */
class Community_Model_FrRequest
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
            $this->setDbTable('Community_Model_DbTable_FrRequest');
        }
        return $this->_dbTable;
    }
    
    public function checkFrRequest($from,$to, $status = 0)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchAll(
                    $activ->select()
                        ->where('connect_from = ?',$from)
                        ->where('connect_to = ?',$to)
                        ->where('status = ?',$status)
                        ->orWhere('connect_from = ?',$to)
                        ->where('connect_to = ?',$from)
                        ->where('status = ?',$status)
                );
        return $result;
    }
    
    public function checkIfFriend($from,$to)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                $table->select()
                    ->where('connect_from = ?',$from)
                    ->where('connect_to = ?',$to)
                    ->where('status = ?','1')
                );
        (isset($result))?$r = TRUE: $r = FALSE;
        return $r;
    }

    public function getFrRequest($ID)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchRow(
                    $activ->select()
                        ->where('connection_id = ?',$ID)
                        ->where('status = ?',0)
                );
        return $result;
    }
    
    public function getFrRequest_from($userID)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchAll(
                    $activ->select()
                        ->where('connect_from = ?',$userID)
                        ->where('status = ?',0)
                        ->order('connection_id DESC')
                );
        return $result;
    }
    
    public function getFrRequest_to($userID)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchAll(
                    $activ->select()
                        ->where('connect_to = ?',$userID)
                        ->where('status = ?',0)
                        ->order('connection_id DESC')
                );
        return $result;
    }
    
    public function getFriendsList($userID)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchAll(
                $activ->select()
                    ->where('connect_to = ?',$userID)
                    ->where('status = ?',1)
                );
        return $result;
    }
    
    public function getFriendsActivities($userID,$nFrCount = 6)
    {
        $frs = $this->getFriendsList($userID);
        $frAr = array();
        $task = 'friends';
        if(count($frs) > 0){
            foreach ($frs as $fr){
                $frAr[] = $fr->connect_from;
            }
        } else {
            $users = new Community_Model_Users();
            $frs = $users->getMostActiv();
            if(count($frs) > 0){
                foreach ($frs as $fr){
                    $frAr[] = $fr->userid;
                }
                shuffle($frAr);
                $frAr = array_slice($frAr, 0, $nFrCount);
                $task = 'activs';
            }
        }
        return array('task'=>$task,'users'=>$frAr);
    }

    public function getFriendsListArray($userID)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchAll(
                $activ->select()
                    ->where('connect_to = ?',$userID)
                    ->where('status = ?',1)
                );
        return $result->toArray();
    }
    
    public function getCommonFriends($user1, $user2)
    {
        $friends1 = $this->getFriendsList($user1);
        $friends2 = $this->getFriendsList($user2);
        
        $fr1 = array();
        $fr2 = array();
        
        foreach ($friends1 as $f) { $fr1[] = $f->connect_from; }
        
        foreach ($friends2 as $f) { $fr2[] = $f->connect_from; }
        
        $result = array_intersect($fr1,$fr2);
        
        return $result;
    }

    public function getFriendsFriends($userID)
    {
        $friends = $this->getFriendsList($userID);
        $curUser = array($userID);
        $firstFr = array();
        $secFr = array();
        $profil = new Community_Model_Users();
        foreach ($friends as $fr)
        {
            $frUser = $profil->getUser($fr->connect_from);
            if($frUser){
                $firstFr[] = $fr->connect_from;
            } else {
                $this->delFriendship($fr->connect_from, $userID);
            }
        }
        
        if(count($firstFr) > 0){
            $activ = $this->getDbTable();
            $firstResult = $activ->fetchAll(
                    $activ->select()
                        ->where('connect_to IN (?)',$firstFr)
                        ->where('status = ?',1)
                    );
            foreach ($firstResult as $r)
            {
                $frUser = $profil->getUser($r->connect_from);
                if($frUser){
                    $secFr[] = $r->connect_from;
                } else {
                    $this->delFriendship($r->connect_from, $r->connect_to);
                }
            }
            $result = array_unique(array_diff($secFr, $firstFr, $curUser));
        } else {
            $result = $firstFr;
        }
        
        return $result;
    }
    
    public function getMayBeFriends($curuser)
    {
        $fr = $this->getFriendsFriends($curuser);
        if(count($fr) > 0){
            $fromRq = $this->getFrRequest_from($curuser);
            $toRq = $this->getFrRequest_to($curuser);
            
            if (count($fromRq) > 0){
                $from = array();
                foreach ($fromRq as $f){
                    $from[] = $f->connect_to;
                }
                $fr = array_diff($fr, $from);
            }

            if (count($toRq) > 0){
                $to = array();
                foreach ($toRq as $t){
                    $to[] = $t->connect_from;
                }
                $fr = array_diff($fr, $to);
            }
            
            return $fr;
        }
    }

    public function getAjaxRead($userID)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchAll(
                $activ->select()
                    ->where('connect_to = ?',$userID)
                    ->where('status = ?',0)
                    ->where('ajax_read = ?',0)
                );
        return $result;
    }

    public function getCheckInList($userID,$list)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchAll(
                $activ->select()
                    ->where('connect_to = ?',$userID)
                    ->where('status = ?',1)
                    ->where('connect_from IN (?)',$list)
                );
        return $result;
    }
    
    public function setFrRequest($from,$to,$msg = '',$status = 0,$ajax_read = 0,$created = 0)
    {
        $table = $this->getDbTable();
        
        if ($created == 0){
            $Date = new Zend_Date(date('Y-m-d H:i:s'));
            $created = $Date->get(Zend_Date::TIMESTAMP);
        }
        
        $data = array(
                'connect_from'=>$from,
                'connect_to'=>$to,
                'status'=>$status,
                'ajax_read'=>$ajax_read,
                'msg'=>$msg,
                'created'=>$created
            );
        $table->insert($data);
        $id = $table->getAdapter()->lastInsertId();
        
        if($status == 1){
            $activ = new Community_Model_Activities();
            $actData = array(
                'actor'=>$from,
                'target'=>$to,
                'title'=>'{actor} and {target} are now friends.',
                'app'=>'friends',
                'action'=>'created',
                'permission' => 40
            );
            $activ->setActiv($actData);
        }
        
        return $id;
    }
    
    public function setStatusOK($ID)
    {
        $table = $this->getDbTable();
        
        $data = array(
                'status'=>1
            );
        $where = $table->getAdapter()->quoteInto('connection_id = ?', $ID);
        $table->update($data, $where);
    }
    
    public function setAjaxRead($ID)
    {
        $table = $this->getDbTable();
        
        $data = array(
                'ajax_read'=>1
            );
        $where = $table->getAdapter()->quoteInto('connection_id = ?', $ID);
        $table->update($data, $where);
    }

    public function delFrRequest($ID)
    {
        $table = $this->getDbTable();
        
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('connection_id = ?', $ID);
        $where[] = $table->getAdapter()->quoteInto('status = ?', 0);
        $table->delete($where);
    }

    public function delFriendship($from, $to)
    {
        $table = $this->getDbTable();
        
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('connect_from = ?', $from);
        $where[] = $table->getAdapter()->quoteInto('connect_to = ?', $to);
        $where[] = $table->getAdapter()->quoteInto('status = ?', 1);
        $table->delete($where);
        
        $where2 = array();
        $where2[] = $table->getAdapter()->quoteInto('connect_from = ?', $to);
        $where2[] = $table->getAdapter()->quoteInto('connect_to = ?', $from);
        $where2[] = $table->getAdapter()->quoteInto('status = ?', 1);
        $table->delete($where2);
    }
}
