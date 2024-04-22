<?php

/**
 * Photos.php
 * Description of Photos
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 09:09:50
 * 
 */
class Default_Model_Photos
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
            $this->setDbTable('Default_Model_DbTable_Photos');
        }
        return $this->_dbTable;
    }
    
    public function getPhotoID($id)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('id = ?',$id);
        $photo = $table->fetchRow($select);
        return $photo;
    }
    
    public function getEditPhoto($userID,$id)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchRow(
                    $activ->select()
                        ->where('creator = ?',$userID)
                        ->where('id = ?',$id)
                );
        return $result;
    }
    
    public function getAllUserPhotos($userID,$permis=NULL)
    {
        $photos = $this->getDbTable();
        $select = $photos->select();
        $select->where('creator = ?',$userID)
                    ->where('published = 1');
        if(isset($permis))
            $select->where('permissions <= ?',$permis);
        $select->order('id DESC');
        $result = $photos->fetchAll($select);
        return $result;
    }
    
    public function getAlbumPhotos($userID,$albumID)
    {
        $photos = $this->getDbTable();
        $select = $photos->select();
        $select->where('creator = ?',$userID)
                    ->where('albumid =?',$albumID);
        $result = $photos->fetchAll($select);
        return $result;
    }

    public function getAllAlbumPhotosToPermis($userID,$albumID,$permis)
    {
        $photos = $this->getDbTable();
        $select = $photos->select();
        $select->where('creator = ?',$userID)
                    ->where('albumid =?',$albumID)
                    ->where('permissions != ?',$permis);
        $result = $photos->fetchAll($select);
        return $result;
    }

    public function getAllAlbumPhotos($userID,$albumID,$last=0,$limit=NULL,$permis=NULL)
    {
        $photos = $this->getDbTable();
        $select = $photos->select();
        $select->where('creator = ?',$userID)
                    ->where('albumid =?',$albumID)
                    ->where('published = 1')
                    ->order('id DESC');
        if($last > 0)
            $select->where('id < ?',$last);
        if(isset($permis))
            $select->where('permissions <= ?',$permis);
        if(isset($limit))
            $select->limit($limit);
        $result = $photos->fetchAll($select);
        return $result;
    }
    
    public function getLastAlbumImg($userID,$id,$limit = 5,$outID = 0)
    {
        $photos = $this->getDbTable();
        $select = $photos->select();
        $select->where('creator = ?',$userID)
                ->where('albumid = ?',$id)
                ->where('id != ?',$outID)
                ->where('published = 1');
        if($limit > 1){
            $select->order('created DESC')
                    ->limit($limit);
            $result = $photos->fetchAll($select);
        }else{
            $result = $photos->fetchRow($select);
        }
        return $result;
    }
    
    //Check
    public function getNextPhoto($id,$creator,$album=null,$permis=null,$list= null)
    {
        $activ = $this->getDbTable();
        $select = $activ->select();
        $select->where('id < ?',$id)
            ->where('creator = ?',$creator)
            ->where('published = 1');
        if(isset($permis))
            $select->where('permissions <= ?',$permis);
        if(isset($list))
            $select->where('id IN (?)',$list);
        $select->order('id DESC');
        if($album != null)
            $select->where('albumid = ?',$album);
        $result = $activ->fetchRow($select);
        
        if (count($result) > 0){
            return $result;
        } else {
            $select2 = $activ->select();
            $select2->where('id > ?',$id)
                ->where('creator = ?',$creator)
                ->where('published = 1');
            if(isset($permis))
                $select2->where('permissions <= ?',$permis);
            if(isset($list))
                $select2->where('id IN (?)',$list);
            $select2->order('id DESC');
            if($album != null)
                $select2->where('albumid = ?',$album);
            $result2 = $activ->fetchRow($select2);
            
            if (count($result2) > 0){
                return $result2;
            } else {
                $result3 = $this->getPhotoID($id);
                return $result3;
            }
        }
    }
    
    //Check
    public function getPrewPhoto($id,$creator,$album=null,$permis=null,$list= null)
    {
        $activ = $this->getDbTable();
        $select = $activ->select();
        $select->where('id > ?',$id)
            ->where('creator = ?',$creator)
            ->where('published = 1');
        if(isset($permis))
            $select->where('permissions <= ?',$permis);
        if(isset($list))
            $select->where('id IN (?)',$list);
        $select->order('id ASC');
        if($album != null)
            $select->where('albumid = ?',$album);
        $result = $activ->fetchRow($select);
        if (count($result) > 0){
            return $result;
        } else {
            $select2 = $activ->select();
            $select2->where('id < ?',$id)
                        ->where('creator = ?',$creator)
                        ->where('published = 1');
            if(isset($permis))
                $select2->where('permissions <= ?',$permis);
            if(isset($list))
                $select2->where('id IN (?)',$list);
            $select2->order('id ASC');
            if($album != null)
                $select2->where('albumid = ?',$album);
            $result = $activ->fetchRow($select2);
            
            if (count($result) > 0){
                return $result;
            } else {
                $result3 = $this->getPhotoID($id);
                return $result3;
            }
        }
    }

    public function updateComPhoto ($userID,$photo,$album,$data)
    {
        $table = $this->getDbTable();
        
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('id = ?', $photo);
        $where[] = $table->getAdapter()->quoteInto('albumid = ?', $album);
        $where[] = $table->getAdapter()->quoteInto('creator = ?', $userID);
        $table->update($data, $where);
    }
    
    public function delComPhoto($userID,$photo,$album)
    {
        $table = $this->getDbTable();
        
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('albumid = ?', $album);
        $where[] = $table->getAdapter()->quoteInto('id = ?', $photo);
        $where[] = $table->getAdapter()->quoteInto('creator = ?', $userID);
        $table->delete($where);
    }
    
    public function editThempPhoto($albID)
    {
        $basePath   = BASE_PATH.'/albums/';
        $index      = $basePath.'index.html';
        $albPath    = $basePath.$albID;
        $origPath   = $albPath.'/originalphotos/';
        $photoPath   = $albPath.'/photos/';

        if (is_dir($albPath) == FALSE){
            mkdir($albPath, 0755);
            copy ($index, $albPath.'/index.html');
        }
        if (is_dir($origPath) == FALSE){
            mkdir($origPath, 0755);
            copy ($index, $origPath.'/index.html');
        }
        if (is_dir($photoPath) == FALSE){
            mkdir($photoPath, 0755);
            copy ($index, $photoPath.'/index.html');
        }
        
        $path = pathinfo(BASE_PATH.'/'.$photo->thumbnail);
        $filter = new N2S_Filter_File_Cropthumb(array(
            'width' => $width,
            'thumbwidth' => 134,
            'thumbheight' => 134,
            'x'=>$x,
            'y'=>$y,
            'w'=>$w,
            'directory'=>$path['dirname'],
            'name'  => 'thumb_'
        ));
        $filter->filter(BASE_PATH.'/'.$photo->original);
    }

    public function uploadPhoto($albumID)
    {
        $basePath   = BASE_PATH.'/albums/';
        $index      = $basePath.'index.html';
        $albPath    = $basePath.$albumID;
        $origPath   = $albPath.'/originalphotos/';
        $photoPath   = $albPath.'/photos/';
        
        if (is_dir($albPath) == FALSE){
            mkdir($albPath, 0755);
            copy ($index, $albPath.'/index.html');
        }
        if (is_dir($origPath) == FALSE){
            mkdir($origPath, 0755);
            copy ($index, $origPath.'/index.html');
        }
        if (is_dir($photoPath) == FALSE){
            mkdir($photoPath, 0755);
            copy ($index, $photoPath.'/index.html');
        }
        
        @set_time_limit(5 * 60);//5 minutes
        try
        {
            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->addValidator('Count',false, array('min'=>1, 'max'=>100))
                    ->addValidator('Size',false,$this->view->returnBytes(ini_get('upload_max_filesize')))
                    ->addValidator('Extension',false,array('extension' => 'JPG,JPEG,PNG,GIF,jpg,jpeg,png,gif','case' => true));

            $adapter->setDestination($origPath);

            $files = $adapter->getFileInfo();
            foreach($files as $fieldname=>$fileinfo)
            {
                if (($adapter->isUploaded($fileinfo['name']))&& ($adapter->isValid($fileinfo['name'])))
                {
                    // Clean the fileName for security reasons
                    $fileName = md5(preg_replace('/[^\w\._]+/', '_', $fileinfo['name']));
                    $filenameF = $fileName.'_'.time();
                    $filename = $filenameF.'.png';
                    
                    $adapter->addFilter('Rename',array('target'=>$origPath.$filename,'overwrite'=>false))
                        ->addFilter(new N2S_Filter_File_Resize(array(
                                    'width' => 200,
                                    'height' => 960,
                                    'keepRatio' => true,
                                    'directory' => $photoPath
                                )))
                        ->addFilter(new N2S_Filter_File_Multicropthumb(array(
                                    'thumbwidth' => 134,
                                    'thumbheight' => 134,
                                    'directory' => $photoPath,
                                    'name'  => 'thumb_'
                                )));
                    $adapter->receive($fileinfo['name']);
                    
                    if ($adapter->receive()) {
                        $baseImgPath = 'albums/'.$albumID;
                        
                        $imgPath = $baseImgPath.'/photos/'.$filename;
                        chmod($imgPath, 0644);
                        $thumbPath = $baseImgPath.'/photos/thumb_'.$filename;
                        chmod($thumbPath, 0644);
                        $origimgPath = $baseImgPath.'/originalphotos/'.$filename;
                        chmod($origimgPath, 0644);
                        
                        return array('img'=>$imgPath,'thumb'=>$thumbPath,'orig'=>$origimgPath,'title'=>$filenameF);
                    }
                }
            }
        }
        catch (Exception $ex)
        {
            echo "Exception!\n";
            echo $ex->getMessage();
        }
    }
    
    public function setAfterRotateSize($id)
    {
        $photo = $this->getPhotoID($id);
        if(isset($photo)){
            $data = array('width'=>$photo->height,'height'=>$photo->width);
            $this->updateComPhoto($photo->creator, $id, $photo->albumid, $data);
        }
    }

    public function setPhoto($userID,$albumID,$image,$thumbnail,$original,$title,$width,$height,$published,$permissions = 0)
    {
        $table = $this->getDbTable();
        $created = new Zend_Date(date('Y-m-d H:i:s'));
        if($permissions == 0){
            $albums = new Default_Model_PhotoAlbums();
            $album = $albums->getComAlbumInfo($albumID);
            $permissions = $album->permissions;
        }
        
        $data = array(
            'title'=>$title,
            'albumid'=> $albumID,
            'image'=> $image,
            'thumbnail'=> $thumbnail,
            'original'=> $original,
            'permissions'=> $permissions,
            'published'=> $published,
            'creator'=> $userID,
            'created'=> $created->get(Zend_Date::TIMESTAMP),
            'width'=>$width,
            'height'=>$height
        );
        
        $table->insert($data);
        return $table->getAdapter()->lastInsertId();
    }
    
    public function setHit($photo)
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity() || $auth->getIdentity()->userid != $photo->creator) {
            $table = $this->getDbTable();
            $hit = $photo->hits + 1;
            $data = array('hits'=>$hit);
            $where = $table->getAdapter()->quoteInto('id = ?', $photo->id);
            $table->update($data, $where);
        }
    }
}
