<?php

/**
 * Msg.php
 * Description of Msg
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 13:38:59
 * 
 */
class Community_Model_Msg
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
            $this->setDbTable('Community_Model_DbTable_Msg');
        }
        return $this->_dbTable;
    }
    
    public function setMsg($from,$from_name,$to,$string,$created = 0,$is_read = 0,$ajax_read = 0)
    {
        $table = $this->getDbTable();
        if ($created == 0){
            $Date = new Zend_Date(date('Y-m-d H:i:s'));
            $created = $Date->get(Zend_Date::TIMESTAMP);
        }
        
        $data = array(
                'from'=>$from,
                'from_name'=>$from_name,
                'posted_on'=>$created,
                'body'=>$string
            );
        
        $table->insert($data);
        
        $id = $table->getAdapter()->lastInsertId();
        $data2 = array(
            'msg_id'=>$id,
            'msg_from'=>$from,
            'msg_to'=>$to,
            'is_read'=>$is_read,
            'ajax_read'=>$ajax_read
        );
        $recep = new Community_Model_MsgRecepient();
        $recep->setMsg($data2);
        return $id;
    }
    
    public function getMsg($id)
    {
        $activ = $this->getDbTable();
        $msg = new Community_Model_DbTable_MsgRecepient();
        $actTab = $activ->info();
        $msgTab = $msg->info();
        
        $select = $activ->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                            ->setIntegrityCheck(false);
        
        $select->where('id = ?',$id);
        
        $select->joinRight($msgTab['name'],
                            $msgTab['name'].'.msg_id = '.$actTab['name'].'.id');
        $result = $activ->fetchRow($select);
        return $result;
    }

    public function getAll($array)
    {
        $activ = $this->getDbTable();
        
        $result = $activ->fetchAll(
                        $activ->select()
                            ->where('id IN (?)',$array)
                            ->order('posted_on DESC')
                    );
        
        return $result;
    }
    
    public function delMsg($ID)
    {
        $table = $this->getDbTable();
        
        $where = $table->getAdapter()->quoteInto('id = ?', $ID);
        $table->delete($where);
    }
}