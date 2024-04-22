<?php

/**
 * Comments.php
 * Description of Comments
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 31.10.2012 12:58:42
 * 
 */
class Default_Model_Comments
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
            $this->setDbTable('Default_Model_DbTable_Comments');
        }
        return $this->_dbTable;
    }
    
    public function getAlbumsComments($data)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('contentid IN (?)',$data)
                        ->where('published = ?','1')
                        ->where('type = ?','photos')
                        ->order('date DESC')
                );
        return $result;
    }
    
    //Check
    public function getComments($id,$type,$last=null,$count=null)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        if($count)
            $select->limit($count);
        if($last)
            $select->where('id < ?',$last);
        $select->where('contentid = ?',$id);
        $select->where('published = ?','1');
        $select->where('type = ?',$type);
        $select->order('date DESC');
        $result = $table->fetchAll($select);
        return $result;
    }
    
    //Check
    public function getComment($id)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchRow(
                    $activ->select()
                        ->where('id = ?',$id)
                );
        return $result;
    }
    
    //Check
    public function getEditComment($userID,$id)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchRow(
                    $activ->select()
                        ->where('post_by = ?',$userID)
                        ->where('id = ?',$id)
                );
        return $result;
    }
    
    //Check
    public function setComment($data)
    {
        $table = $this->getDbTable();
        $table->insert($data);
        return $table->getAdapter()->lastInsertId();
    }
    
    public function setDeactive($userID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll($table->select()
                ->where('post_by = ?',$userID));
        foreach ($result as $r){
            $data = array('type'=>$r->type.'_deactive');
            $where = $table->getAdapter()->quoteInto('id = ?',$r->id);
            $table->update($data, $where);
            unset($data);
        }
    }
    
    public function setActive($userID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll($table->select()
                ->where('post_by = ?',$userID));
        foreach ($result as $r){
            list($app,$deact) = explode('_', $r->type);
            if(isset($deact)){
                $data = array('type'=>$app);
                $where = $table->getAdapter()->quoteInto('id = ?',$r->id);
                $table->update($data, $where);
                unset($data);
            }
        }
    }
    
    //Check
    public function delComment($ID)
    {
        $table = $this->getDbTable();
        $where = $table->getAdapter()->quoteInto('id = ?', $ID);
        $table->delete($where);
    }
    
    //Check
    public function delSetComment($ID,$type)
    {
        $table = $this->getDbTable();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('contentid = ?', $ID);
        $where[] = $table->getAdapter()->quoteInto('type = ?', $type);
        $table->delete($where);
    }
    
    public function checkAdmin($userID,$id,$type)
    {
        $spam = FALSE;
        switch ($type){
            case 'photos':
                $model = new Default_Model_Photos();
                $r = $model->getPhotoID($id);
                if(isset($r) && $r->creator == $userID)
                    $spam = TRUE;
                break;
            case 'events':
                $model = new Default_Model_Events();
                $r = $model->getEvent($id);
                if(isset($r) && $r->creator == $userID)
                    $spam = TRUE;
                break;
            case 'albums':
                $model = new Default_Model_PhotoAlbums();
                $r = $model->getComAlbumInfo($id);
                if(isset($r) && $r->creator == $userID)
                    $spam = TRUE;
                break;
            case 'venues':
                $model = new Default_Model_Adresses();
                $r = $model->getAdress($id);
                if(isset($r) && $r->creator == $userID)
                    $spam = TRUE;
                break;
            default :
                $spam = FALSE;
        }
        
        return $spam;
    }
}