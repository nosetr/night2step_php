<?php

/**
 * ActivitiesSave.php
 * Description of ActivitiesSave
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 13:52:19
 * 
 */
class Community_Model_ActivitiesSave
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
    
    public function setActiveFriends($actor,$target)
    {
        $table = $this->getDbTable();
        $Date = new Zend_Date(date('Y-m-d H:i:s'));
        $created = $Date->get(Zend_Date::TIMESTAMP);
        
        $data = array(
                'actor'=>$actor,
                'target'=>$target,
                'title'=> '{actor} and {target} are now friends',
                'app'=>'friends',
                'cid'=>0,
                'created'=>$created
            );
        
        $table->insert($data);
    }

    public function setActiveVideo($userID,$target,$title,$app,$cid,$params)
    {
        $table = $this->getDbTable();
        $Date = new Zend_Date(date('Y-m-d H:i:s'));
        $created = $Date->get(Zend_Date::TIMESTAMP);
        
        if($app == 'videos'){
            $data = array(
                'actor'=>$userID,
                'target'=>$target,
                'title'=> $title,
                'app'=>$app,
                'cid'=>$cid,
                'created'=>$created,
                'params'=>$params,
                'comment_id'=>$cid,
                'comment_type'=>$app,
                'like_id'=>$cid,
                'like_type'=>$app
            );
        }
        
        $table->insert($data);
    }
    
    public function setActiveStatus($userID,$target,$status)
    {
        $table = $this->getDbTable();
        $Date = new Zend_Date(date('Y-m-d H:i:s'));
        $created = $Date->get(Zend_Date::TIMESTAMP);
        if ($userID == $target){
            $title = '{actor}';
        } else {
            $title = '{actor} has posted to the {target}s pinboard';
        }
            $data = array(
                'actor'=>$userID,
                'target'=>$target,
                'title'=> $title,
                'content'=> $status,
                'app'=>'profile',
                'cid'=>$userID,
                'created'=>$created,
                'comment_type'=>'profile.status',
                'like_type'=>'profile.status'
            );
        
        $table->insert($data);
    }
    
    public function setActiveNewAvatar($userID,$img,$origimg)
    {
        $table = $this->getDbTable();
        $Date = new Zend_Date(date('Y-m-d H:i:s'));
        $created = $Date->get(Zend_Date::TIMESTAMP);
        
            $data = array(
                'actor'=>$userID,
                'title'=> '{actor} has changed profilimage',
                'params'=>'image='.$img.'
origimage='.$origimg,
                'app'=>'avatar',
                'created'=>$created,
                'comment_type'=>'profile.avatar',
                'like_type'=>'profile.avatar'
            );
        
        $table->insert($data);
    }
    
    public function setActiveNewEvent($userID,$params,$cid)
    {
        $table = $this->getDbTable();
        $Date = new Zend_Date(date('Y-m-d H:i:s'));
        $created = $Date->get(Zend_Date::TIMESTAMP);
        
        $data = array(
            'actor'=>$userID,
            'title'=> '{actor} has added a new event {link}{title}</a>',
            'created'=>$created,
            'comment_type'=>'n2sevents',
            'like_type'=>'n2sevents',
            'app'=>'n2sevents',
            'cid'=>$cid,
            'params'=>$params,
            'comment_id'=>$cid,
            'like_id'=>$cid,
        );
        $table->insert($data);
    }
}