<?php

/**
 * Background.php
 * Description of Background
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 12.11.2012 14:10:57
 * 
 */
class Default_Model_Background
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
            $this->setDbTable('Default_Model_DbTable_Background');
        }
        return $this->_dbTable;
    }
    
    public function getImg($id,$storage)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                $table->select()
                    ->where('objectid = ?',$id)
                    ->where('storage = ?',$storage)
                );
        return $result;
    }
    
    public function delImg($id,$storage)
    {
        $table = $this->getDbTable();
        
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('objectid = ?',$id);
        $where[] = $table->getAdapter()->quoteInto('storage = ?',$storage);
        $table->delete($where);
    }
    
    public function updatePosit($id,$userID,$top)
    {
        $table = $this->getDbTable();
        $check = $table->fetchRow(
                $table->select()
                    ->where('id = ?',$id)
                    ->where('creator = ?', $userID)
                );
        if(isset($check)){
            $data = array(
                'top'=>$top,
                'image'=>$this->crupImg($check->imageid, $top)
            );

            $where = array();
            $where[] = $table->getAdapter()->quoteInto('id = ?', $id);
            $where[] = $table->getAdapter()->quoteInto('creator = ?', $userID);
            $table->update($data, $where);
        }
    }
    
    public function updateImg($creator,$storage,$object,$image,$top=0)
    {
        $table = $this->getDbTable();
        $data = array(
            'imageid'=>$image,
            'top'=>$top,
            'image'=>$this->crupImg($image, $top)
        );
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('objectid = ?', $object);
        $where[] = $table->getAdapter()->quoteInto('storage = ?', $storage);
        $where[] = $table->getAdapter()->quoteInto('creator = ?', $creator);
        $table->update($data, $where);
    }

    public function setImg($creator,$storage,$object,$image,$top=0)
    {
        $check = $this->getImg($object,$storage);
        if(count($check)>0)
        {
            $this->updateImg($creator, $storage, $object, $image, $top);
        } else {
            $table = $this->getDbTable();
            $data = array(
                'creator'=>$creator,
                'storage'=>$storage,
                'objectid'=>$object,
                'imageid'=>$image,
                'top'=>$top,
                'image'=>$this->crupImg($image, $top)
            );
            $table->insert($data);
        }
    }
    
    public function crupImg($imgID,$top)
    {
        $photos = new Default_Model_Photos();
        $photo = $photos->getPhotoID($imgID);
        if(isset($photo) && file_exists($photo->original)){
            $basePath   = BASE_PATH.'/albums/';
            $index      = $basePath.'index.html';
            $albPath    = $basePath.$photo->albumid;
            $origPath   = $albPath.'/banners';
            if (is_dir($origPath) == FALSE){
                mkdir($origPath, 0755);
                copy ($index, $origPath.'index.html');
            }

            $filter = new N2S_Filter_File_Cropthumb(array(
                'width' => 750,
                'thumbwidth' => 740,
                'thumbheight' => 200,
                'x'=>0,
                'y'=>  abs($top),
                'w'=>740,
                'directory'=>$origPath
            ));
            $newPh = $filter->filter(BASE_PATH.'/'.$photo->original);
            $path_parts = pathinfo($newPh);
            $newPh = 'albums/'.$photo->albumid.'/banners/'.$path_parts['basename'];
            return $newPh;
        }
    }

    public function dubbleImg($storage,$object,$new)
    {
        $check = $this->getImg($object,$storage);
        if(count($check)>0)
        {
            $table = $this->getDbTable();
            $data = array(
                'creator'=>$check->creator,
                'storage'=>$storage,
                'objectid'=>$new,
                'imageid'=>$check->imageid,
                'top'=>$check->top,
                'image'=>$check->image
            );
            $table->insert($data);
        }
    }
}