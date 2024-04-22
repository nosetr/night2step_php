<?php

/**
 * PhotoAlbums.php
 * Description of PhotoAlbums
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 30.10.2012 19:36:14
 * 
 */
class Default_Model_PhotoAlbums
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
            $this->setDbTable('Default_Model_DbTable_PhotoAlbums');
        }
        return $this->_dbTable;
    }
    
    public static function search($id)
    {
       $album = new Community_Model_DbTable_PhotoAlbums();

       $results = $album->getAlbum($id);

       return $results;
    }

    public function getAllPartyAlbums()
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('partypics = ?','1')
                        ->order('partydate DESC')
                        ->group('partydate')
                );
        return $result;
    }
    
    public function getAllPartyAlbumsLast($minLat=null,$maxLat=null,
            $minLon=null,$maxLon=null,$rows = null,$start = false)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        //$select->where('published = ?','1');
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
            $select->order('start ASC');
        } else {
            //$select->order('start DESC');
            $select->order(array('start DESC',
                           'id DESC'));
        }
        $result = $table->fetchAll($select);
        return $result;
    }

    public function getActualPartyAlbums($date,$jump = 0)
    {
        $from = $date.'-31';
        if($jump == 0){
            $til = $date.'-01';
        } else {
            $til = $jump.'-01';
        }
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('partypics = ?','1')
                        ->where('partydate <= ?',$from)
                        ->where('partydate >= ?',$til)
                        ->order('partydate DESC')
                        ->group('partydate')
                );
        return $result;
    }

    public function getNextPartyAlbums($date)
    {
        $from = $date.'-01';
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('partypics = ?','1')
                        ->where('partydate < ?',$from)
                        ->order('partydate DESC')
                        ->group('partydate')
                        ->limit(1)
                );
        return $result;
    }

    public function getToDayPartyAlbums($partydate)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('partydate = ?',$partydate)
                        ->order('created DESC')
                );
        return $result;
    }

    public function getComProfilImgAlbum($userID)
    {
        $type = 'profimg';
        
        $activ = $this->getDbTable();
        $result = $activ->fetchRow(
                    $activ->select()
                        ->where('creator = ?',$userID)
                        ->where('type = ?',$type)
                );
        return $result;
    }
    
    public function getComEventsImgAlbum($userID)
    {
        $type = 'eventimg';
        
        $activ = $this->getDbTable();
        $result = $activ->fetchRow(
                    $activ->select()
                        ->where('creator = ?',$userID)
                        ->where('type = ?',$type)
                );
        return $result;
    }
    
    public function getComAlbum($userID=null,$row=null,$photoCount=FALSE,$permis=NULL)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        if(isset($row)){
            $select->where('id IN (?)',$row);
        } else {
            $select->where('creator = ?',$userID);
        }
        if($photoCount==TRUE)
            $select->where('photocount > ?',0);
        if(isset($permis))
            $select->where('permissions <= ?',$permis);
        $select->order('created DESC');
        $result = $table->fetchAll($select);
        return $result;
    }

    public function getComAlbumInfo($ID)
    {
        $activ = $this->getDbTable();
        $result = $activ->fetchRow(
                    $activ->select()
                        ->where('id = ?',$ID)
                );
        return $result;
    }

    public function setComProfilImgAlbum($userID)
    {
        $table = $this->getDbTable();
        $created = new Zend_Date(date('Y-m-d H:i:s'));
        
            $data = array(
                'creator'=> $userID,
                'created'=> $created->get(Zend_Date::TIMESTAMP),
                'name'=> 'profilimages',
                'type'=> 'profimg'
            );
        
        $table->insert($data);
        return $table->getAdapter()->lastInsertId();
    }

    public function setComEventsImgAlbum($userID)
    {
        $table = $this->getDbTable();
        $created = new Zend_Date(date('Y-m-d H:i:s'));
        
            $data = array(
                'creator'=> $userID,
                'created'=> $created->get(Zend_Date::TIMESTAMP),
                'name'=> 'eventsimages',
                'type'=> 'eventimg'
            );
        
        $table->insert($data);
        return $table->getAdapter()->lastInsertId();
    }
    
    public function setComAlbum($userID,$album,$updata)
    {
        $addresses = new Default_Model_Adresses();
        $table = $this->getDbTable();
        $data = array(
            'description'=>$updata['albdescription']
        );
        if(isset($updata['permissions'])){
            $photos = new Default_Model_Photos();
            $photo = $photos->getAllAlbumPhotosToPermis($userID, $album, $updata['permissions']);
            if(count($photo) > 0){
                @set_time_limit(2 * 60);
                $phData = array('permissions'=>$updata['permissions']);
                foreach ($photo as $ph)
                {
                    $photos->updateComPhoto($userID, $ph->id, $album, $phData);
                }
            }
        }
        if((isset($updata['permissions']) && isset($updata['event']) && $updata['event'] == 0) ||
                (isset($updata['permissions']) && !isset($updata['event']))){
            $data['permissions']=$updata['permissions'];
        } else {
            $data['permissions']=0;
        }
        if(isset($data['permissions'])){
            $photos = new Default_Model_Photos();
            $photo = $photos->getAllAlbumPhotosToPermis($userID, $album, $data['permissions']);
            if(count($photo) > 0){
                @set_time_limit(2 * 60);
                $phData = array('permissions'=>$data['permissions']);
                foreach ($photo as $ph)
                {
                    $photos->updateComPhoto($userID, $ph->id, $album, $phData);
                }
            }
        }
        if(isset($updata['event']))
            $data['partypics']=$updata['event'];
        if($updata['albname'])
            $data['name'] = $updata['albname'];
        if(isset($updata['locid']) && $updata['locid']>0){
            $address = $addresses->getAdress($updata['locid']);
            if($address){
                $data['locid']=$updata['locid'];
                $data['location']=trim($updata['loc']);
                $data['latitude']=$address->latitude;
                $data['longitude']=$address->longitude;
            }
        }elseif(isset($updata['locid']) && $updata['locid']==0 && isset($updata['loc']) && !empty($updata['loc']) && isset($updata['albaddress']) && !empty($updata['albaddress'])){
            $address = $addresses->setAdress($updata['loc'], $updata['albaddress'],$userID);
            $data['locid']=$address['id'];
            $data['location']=$address['name'];
            $data['latitude']=$address['lat'];
            $data['longitude']=$address['lng'];
        }else{
            $data['locid']=0;
            $data['latitude']=255;
            $data['longitude']=255;
        }
            
        if($album>0){
            $ajaxList = new Default_Model_Ajaxlist();
            $data['partypics']=$updata['event'];
            
            if(isset($updata['event']) && $updata['event'] == 1){
                $date = new Zend_Date($updata['eventdate']);
                $today = Zend_Date::now();
                if($date->isEarlier($today)){
                    $alb = $this->getComAlbumInfo($album);
                    $special = $alb->photocount;
                } else {
                    $special = 0;
                }
                $data['partydate'] = $date->toString('YYYY-MM-dd');
                $data['start'] = $date->get(Zend_Date::TIMESTAMP);
                $ajaxList->setList($album, $userID,
                        $date->toString('YYYY'),
                        $date->toString('M'),
                        $date->toString('d'),
                        $date->get(Zend_Date::TIMESTAMP),
                        'photo',
                        $data['latitude'],
                        $data['longitude'],
                        $special);
                
                $activ = new Community_Model_Activities();
                $actData = array(
                    'actor'=>$userID,
                    'title'=>'{actor} has created a new event album {cid}.',
                    'app'=>'albums',
                    'action'=>'created',
                    'cid'=>$album,
                    'locid'=>$data['locid'],
                    'comment'=>'albums',
                    'permission'=>$data['permissions']
                );
                $activ->setActiv($actData);
                
            } else {
                $ajaxList->delList($album,'photo');
                $data['partydate'] = NULL;
            }
            
            $where = array();
            $where[] = $table->getAdapter()->quoteInto('id = ?', $album);
            $where[] = $table->getAdapter()->quoteInto('creator = ?', $userID);
            $table->update($data, $where);
        } else {
            $created = new Zend_Date(date('Y-m-d H:i:s'));
            $data['created']=$created->get(Zend_Date::TIMESTAMP);
            $data['type']='user';
            $data['creator']=$userID;
            $table->insert($data);
            return $table->getAdapter()->lastInsertId();
        }
    }
    
    public function updateAlbum($ID,$data)
    {
        $table = $this->getDbTable();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('id = ?', $ID);
        $table->update($data, $where);
    }
    
    public function updateComProfilImgAlbum($album,$photo)
    {
        $table = $this->getDbTable();
        $data = array(
                'photoid'=> $photo
            );
        $where = $table->getAdapter()->quoteInto('id = ?', $album);
        $table->update($data, $where);
    }
    
    //Check
    public function delAlbum($ID,$creator)
    {
        $table = $this->getDbTable();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('id = ?', $ID);
        $where[] = $table->getAdapter()->quoteInto('creator = ?', $creator);
        $table->delete($where);
    }
}