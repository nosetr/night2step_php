<?php

/**
 * Adresses.php
 * Description of Adresses
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.10.2012 18:59:59
 * 
 */
class Default_Model_Adresses
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
            $this->setDbTable('Default_Model_DbTable_Adresses');
        }
        return $this->_dbTable;
    }
    
    public static function search($term)
    {
       $adresses = new Default_Model_DbTable_Adresses();

       $results = $adresses->getAdress($term);

       return $results;
    }
    
    public function checkAdress($name)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll(
                    $table->select()
                        ->where('name = ?',trim($name))
                );
        return $result;
    }
        
    public function adressImgUpdate($ID, $img)
    {
        $comuserTab = $this->getDbTable();
        $data = array(
            'photoid'=> $img
        );
        $where = $comuserTab->getAdapter()->quoteInto('id = ?', $ID);
        $comuserTab->update($data, $where);
    }
    
    public function setAdmin($objectID,$userID)
    {
        $table = $this->getDbTable();
        $object = $this->getAdress($objectID);
        if($object->creator == 0){
            $pass = $this->_generatePassword();
            $arrayData = array(
                'name'=>$object->name,
                'type'=>'venue',
                'avatar'=>$object->photoid,
                'password'=>$pass
            );
            $acces = new Community_Model_Access();
            $setProf = $acces->addProfil($arrayData);
            $admins = new Community_Model_Admins();
            $admins->setAdmin($userID, $setProf, 'venue',$pass);
            $data = array(
                'creator'=>$setProf
            );
            $where = $table->getAdapter()->quoteInto('id = ?', $objectID);
            $table->update($data, $where);
        }
    }

    public function setAdress($Location,$Adress,$actor,$created = NULL)
    {
        if($created == NULL){
            $now = Zend_Date::now();
            $created = $now->get(Zend_Date::TIMESTAMP);
        }
        $geoloc = N2S_GeoCode_GoogleGeocode::googleGeocode($Adress);
        if(is_array($geoloc) && count($geoloc) > 0){
            if(isset($geoloc['short_country'])){
                $shortcountry = $geoloc['short_country'];
                $rtz = Zend_Locale::getLocaleToTerritory($shortcountry);
                $locale = new Zend_Locale($rtz);
                $rtz = $locale->getLanguage();
                $geoloc2 = N2S_GeoCode_GoogleGeocode::googleGeocode($Adress,$rtz);
                if(is_array($geoloc2) && count($geoloc2) > 0)
                    $geoloc == $geoloc2;
            } else {
                $shortcountry = NULL;
            }
            
            if($shortcountry != null && isset($geoloc['formatted_address']) && isset($geoloc['lat']) && isset($geoloc['lng'])){
                $formAdress = $geoloc['formatted_address'];
                $formAd = array_filter(explode( ', ', $formAdress ));
                $lat=$geoloc['lat'];
                $lng=$geoloc['lng'];
                
                $find = 0;
                $flat = 255;
                $flng = 255;
                $check = $this->checkAdress(trim($Location));
                if(count($check)>0){
                    foreach ($check as $ch){
                        if($ch->address == $formAdress && $ch->country == $shortcountry){
                            $find = $ch->id;
                            $flat = $ch->latitude;
                            $flng = $ch->longitude;
                        }
                    }
                }
                if($find > 0){
                    return array('id'=>$find,'lat'=>$flat,'lng'=>$flng,'new'=>FALSE);
                }else{
                    $table = $this->getDbTable();
                    $data = array(
                        'name'=>trim($Location),
                        'address'=>$formAdress,
                        'country'=>$shortcountry,
                        'latitude'=>$lat,
                        'longitude'=>$lng,
                        'label'=>trim($Location).' '.trim($formAd[1])
                    );
                    $table->insert($data);
                    $lastID = $table->getAdapter()->lastInsertId();

                    $activ = new Community_Model_Activities();
                    $actData = array(
                        'actor'=>$actor,
                        'title'=>'{actor} has added a new venue {cid}.',
                        'app'=>'venues',
                        'action'=>'created',
                        'cid'=>$lastID,
                        'locid'=>$lastID,
                        'comment'=>'venues',

                        'created'=>$created
                    );
                    $activ->setActiv($actData);

                    return array('id'=>$lastID,'lat'=>$lat,'lng'=>$lng,'new'=>TRUE);
                }
            } else {
                return array('id'=>0,'lat'=>255,'lng'=>255,'new'=>FALSE);
            }
        }
    }

    public function getAdressWithCreator($userID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                    $table->select()
                        ->where('creator = ?',$userID)
                );
        return $result;
    }

    public function getAdress($id)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                    $table->select()
                        ->where('id = ?',$id)
                );
        return $result;
    }
    
    public function getList($minLat=null,$maxLat=null,$minLon=null,$maxLon=null,$lastID=null,$limit=null)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        if($minLat)
            $select->where('latitude >= ?',$minLat);
        if($maxLat)
            $select->where('latitude <= ?',$maxLat);
        if($minLon)
            $select->where('longitude >= ?',$minLon);
        if($maxLon)
            $select->where('longitude <= ?',$maxLon);
        if($lastID)
            $select->where('id < ?',$lastID);
        if($limit)
            $select->limit($limit);
        $select->order('id DESC');
        $result = $table->fetchAll($select);
        
        return $result;
    }
    
    public function updateAdress($id,$data)
    {
        $table = $this->getDbTable();
        $where = $table->getAdapter()->quoteInto('id = ?', $id);
        $table->update($data, $where);
    }

    protected function _generatePassword()
    {
	mt_srand();
        $salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	
        $pw = '';
	for($i = 0;$i < 8;$i++)
	{
		$pw .= $salt[mt_rand(0, strlen($salt)-1)];
	}
	return $pw;
    }
}