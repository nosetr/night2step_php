<?php

/**
 * MsgRecepient.php
 * Description of MsgRecepient
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 13:45:06
 * 
 */
class Community_Model_MsgRecepient
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
            $this->setDbTable('Community_Model_DbTable_MsgRecepient');
        }
        return $this->_dbTable;
    }
    
    public function getAll($userID,$view,$last=FALSE,$limit=FALSE,$array=FALSE,$admin=FALSE)
    {
        $activ = $this->getDbTable();
        $msg = new Community_Model_DbTable_Msg();
        $actTab = $activ->info();
        $msgTab = $msg->info();
        
        $select = $activ->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                            ->setIntegrityCheck(false);
        if ($view == 0){
            if ($array == FALSE){
                (is_array($admin))?$select->where('msg_from IN (?)',$admin):
                    $select->where('msg_from = ?',$userID);
            } else {
                $select->where('msg_id IN (?)',$array);
            }
            if ($last != FALSE)
                $select->where('msg_id < ?',$last);
            
            if ($array == FALSE){
                (is_array($admin))?$select->orWhere('msg_to IN (?)',$admin):
                    $select->orWhere('msg_to = ?',$userID);
                if ($last != FALSE)
                    $select->where('msg_id < ?',$last);
            }
        } else {
            $select->where('msg_from = ?',$userID)
                    ->where('msg_to = ?',$view);
            if ($last != FALSE)
                $select->where('msg_id < ?',$last);
            $select->orWhere('msg_to = ?',$userID)
                    ->where('msg_from = ?',$view);
            if ($last != FALSE)
                $select->where('msg_id < ?',$last);
        }
        
        if ($limit != FALSE)
            $select->limit($limit);
        
        
        $select->joinRight($msgTab['name'],
                            $msgTab['name'].'.id = '.$actTab['name'].'.msg_id')
                ->order('msg_id DESC');
        $result = $activ->fetchAll($select);
        
        return $result;
    }

    public function getMsg($id)
    {
        $activ = $this->getDbTable();
        
        $result = $activ->fetchRow(
                    $activ->select()
                        ->where('msg_id = ?',$id)
                );
        return $result;
    }
    
    public function getAjaxRead($userID)
    {
        $activ = $this->getDbTable();
        
        $select = $activ->select();
        $select->where('msg_to = ?',$userID);
        $select->where('ajax_read = ?',0);
        
        $result = $activ->fetchAll($select);
        
        return $result;
    }
    
    public function getAjaxMsg($userID)
    {
        $activ = $this->getDbTable();
        $msg = new Community_Model_DbTable_Msg();
        $actTab = $activ->info();
        $msgTab = $msg->info();
        
        $select = $activ->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                            ->setIntegrityCheck(false);
        
        $select->where('msg_to = ?',$userID)
                ->where('is_read = ?',0);
        
        $select->joinRight($msgTab['name'],
                            $msgTab['name'].'.id = '.$actTab['name'].'.msg_id')
                ->order('posted_on DESC');
        $result = $activ->fetchAll($select);
        return $result;
    }
    
    public function setMsg($data)
    {
        $table = $this->getDbTable();
        
        $table->insert($data);
    }
    
    public function setRead($id)
    {
        $table = $this->getDbTable();
        $data = array(
            'is_read'=> 1,
            'ajax_read'=>1
        );
        $where = $table->getAdapter()->quoteInto('msg_id = ?', $id);
        $table->update($data, $where);
    }
    
    public function setAjaxRead($id)
    {
        $table = $this->getDbTable();
        $data = array(
            'ajax_read'=>1
        );
        $where = $table->getAdapter()->quoteInto('msg_id = ?', $id);
        $table->update($data, $where);
    }

    public function delUserAllMsg($userID)
    {
        $table = $this->getDbTable();
        $msg = new Community_Model_Msg();
        
        $result = $table->fetchAll(
                        $table->select()
                            ->where('msg_from = ?',$userID)
                            ->where('msg_to = ?',$userID)
                    );
        
        return $result;
        
        foreach ($result as $res)
        {
            $msg->delMsg($res->msg_id);
        }
        
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('msg_from = ?',$userID);
        $where[] = $table->getAdapter()->quoteInto('msg_to = ?',$userID);
        $table->delete($where);
    }
}