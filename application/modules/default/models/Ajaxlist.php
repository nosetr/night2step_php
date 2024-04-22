<?php

/**
 * Ajaxlist.php
 * Description of Ajaxlist
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 26.10.2012 11:09:44
 * 
 */
class Default_Model_Ajaxlist
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
            $this->setDbTable('Default_Model_DbTable_Ajaxlist');
        }
        return $this->_dbTable;
    }
    
    public function getHomeList($timestamp,$app,
            $minLat,$maxLat,$minLon,$maxLon,$limit,$archive = TRUE)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('app = ?',$app);
        $select->where('latitude >= ?',$minLat);
        $select->where('latitude <= ?',$maxLat);
        $select->where('longitude >= ?',$minLon);
        $select->where('longitude <= ?',$maxLon);
        
        if($app === 'photo')
            $select->where('special > ?',0);
        if($archive == TRUE){
            $select->where('time < ?',$timestamp);
        } else {
            $select->where('time >= ?',$timestamp);
        }
        $select->order('time DESC');
            
        $select->limit($limit);
            
        $result = $table->fetchAll($select);
        return $result;
    }
    
    public function getList($timestamp,$app,$archiv = null,
            $minLat=null,$maxLat=null,$minLon=null,$maxLon=null,$day=false)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('app = ?',$app);
        if($minLat)
            $select->where('latitude >= ?',$minLat);
        if($maxLat)
            $select->where('latitude <= ?',$maxLat);
        if($minLon)
            $select->where('longitude >= ?',$minLon);
        if($maxLon)
            $select->where('longitude <= ?',$maxLon);
        if($day == true){
            $evDate = new Zend_Date($timestamp);
            $evDAY = $evDate->get(Zend_Date::DAY_SHORT);
            $evMONTH = $evDate->get(Zend_Date::MONTH_SHORT);
            $evYEAR = $evDate->get(Zend_Date::YEAR);
            $select->where('day = ?',$evDAY);
            $select->where('year = ?',$evYEAR);
            $select->where('month = ?',$evMONTH);
            $select->order('time ASC');
        } else {
            if($archiv != NULL){
                if($app === 'photo')
                    $select->where('special > ?',0);
                $select->where('time < ?',$timestamp);
                $select->order('time DESC');
            } else {
                if($app === 'photo')
                    $select->where('special = ?',0);
                $select->where('time > ?',$timestamp);
                $select->order('time ASC');
            }
            $select->limit(1);
        }
        $result = $table->fetchAll($select);
        
        if($day == false && count($result) > 0){
            $result->toArray();
            $select2 = $table->select();
            $select2->where('app = ?',$app);
            $select2->where('year = ?',$result[0]['year']);
            $select2->where('month = ?',$result[0]['month']);
            if($minLat)
                $select2->where('latitude >= ?',$minLat);
            if($maxLat)
                $select2->where('latitude <= ?',$maxLat);
            if($minLon)
                $select2->where('longitude >= ?',$minLon);
            if($maxLon)
                $select2->where('longitude <= ?',$maxLon);
            if($archiv != NULL){
                $select2->where('time < ?',$timestamp);
                if($app === 'photo')
                    $select2->where('special > ?',0);
                $select2->order('time DESC');
            } else {
                if($app === 'photo')
                    $select2->where('special = ?',0);
                $select2->where('time > ?',$timestamp);
                $select2->order('time ASC');
            }
            $result2 = $table->fetchAll($select2);
            return $result2;
        } else {
            return $result;
        }
    }

    public function getRelative($id,$app,$time = null, $count = null, $start = 0, $end = 0)
    {
        switch ($app){
            case 'event':
                $eventTab = new Default_Model_DbTable_Events();
                break;
            case 'photo':
                $eventTab = new Default_Model_DbTable_PhotoAlbums();
                break;
        }
        $event = $eventTab->info();
        $ajaxTab = $this->getDbTable();
        $ajax = $ajaxTab->info();
        $select = $eventTab->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                            ->setIntegrityCheck(false);
        $select->where('locid = ?',$id);
        if($time != null){
            $select->where('start < ?',$time);
        }
        $select->joinRight(array('a' => $ajax['name']), $event['name'].'.id = a.cid')
            ->where('a.app = ?',$app);
        if($start > 0 && $end > 0){
            $startDuration = new Zend_Date($start);
            $syear = $startDuration->toString('YYYY');
            $smonth = $startDuration->toString('M');
            $sday = $startDuration->toString('d');                

            $endDuration = new Zend_Date($end);
            $eyear = $endDuration->toString('YYYY');
            $emonth = $endDuration->toString('M');
            $eday = $endDuration->toString('d');
            
            $select->where('a.year >= ?',$syear);
            $select->where('a.year <= ?',$eyear);
            $select->where('a.month >= ?',$smonth);
            $select->where('a.month <= ?',$emonth);
            $select->where('a.day >= ?',$sday);
            $select->where('a.day <= ?',$eday);
        }
        $select->order(array('a.time DESC',
                    'a.id DESC'));
        
        $result = $eventTab->fetchAll($select);
        
        if($count != NULL && count($result) > $count){
            $result = $result->toArray();//Ausgabe als array
            $result = array_slice($result, 0, $count);
        }
        
        return $result;
    }
    
    public function getSameTime($cid, $time)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                $table->select()
                    ->where('cid != ?',$cid)
                    ->where('time = ?',$time)
                );
        return $result;
    }
    
    public function checkList($cid,$app)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                $table->select()
                    ->where('cid = ?',$cid)
                    ->where('app = ?',$app)
                );
        return $result;
    }
    
    public function setDeactive($userID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll($table->select()
                ->where('creator = ?',$userID));
        foreach ($result as $r){
            $data = array('app'=>$r->app.'_deactive');
            $where = $table->getAdapter()->quoteInto('id = ?',$r->id);
            $table->update($data, $where);
            unset($data);
        }
    }
    
    public function setActive($userID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchAll($table->select()
                ->where('creator = ?',$userID));
        foreach ($result as $r){
            list($app,$deact) = explode('_', $r->app);
            if(isset($deact)){
                $data = array('app'=>$app);
                $where = $table->getAdapter()->quoteInto('id = ?',$r->id);
                $table->update($data, $where);
                unset($data);
            }
        }
    }

    public function setList($id,$creator,$year,$month,$day,$time,$app,$lat,$lon,$special=NULL)
    {
        $check = $this->checkList($id, $app);
        if(count($check)>0){
            $this->updateList($id, $year, $month, $day, $time, $app, $lat, $lon, $special);
        } else {
            $table = $this->getDbTable();
            $data = array(
                'year'=>$year,
                'month'=>$month,
                'day'=>$day,
                'time'=>$time,
                'cid'=>$id,
                'creator'=>$creator,
                'app'=>$app,
                'latitude'=>$lat,
                'longitude'=>$lon,
                'special'=>$special
            );
            $table->insert($data);
        }
    }
    
    public function updateList($id,$year,$month,$day,$time,$app,$lat,$lon,$special=NULL)
    {
        $table = $this->getDbTable();
        $data = array(
            'year'=>$year,
            'month'=>$month,
            'day'=>$day,
            'time'=>$time,
            'cid'=>$id,
            'app'=>$app,
            'latitude'=>$lat,
            'longitude'=>$lon,
            'special'=>$special
        );
        $where = $table->getAdapter()->quoteInto('cid = ?', $id);
        $table->update($data, $where);
    }
    
    public function updateSpecial($id,$app,$spec)
    {
        $table = $this->getDbTable();
        $data = array('special'=>$spec);
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('cid = ?', $id);
        $where[] = $table->getAdapter()->quoteInto('app = ?', $app);
        $table->update($data, $where);
    }

    public function delList($id,$app)
    {
        $table = $this->getDbTable();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('cid = ?', $id);
        $where[] = $table->getAdapter()->quoteInto('app = ?', $app);
        $table->delete($where);
    }
}