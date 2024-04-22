<?php

/**
 * Cron.php
 * Description of Cron
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 14.10.2013 10:59:09
 * 
 */
class Default_Model_Search
{
    /*
    public function setTermList()
    {
        $pathM = APPLICATION_PATH . '/../search/index_multi';
        if (is_dir($pathM) != FALSE){
            $index1 = Zend_Search_Lucene::open($pathM);

            $pq = (array) $index1->terms();

            $results = array();
            foreach ($pq as $key => $value) {
                $value = (array) $value;
                $results[] = $value ["text"];
            }
            $result = array_unique($results);
            
            $path = APPLICATION_PATH . '/../search';
            
            $indexPath = $path.'/index_term';
            $newPath = $indexPath.'_'.time();
            
            $index = Zend_Search_Lucene::create($newPath);
            $document = new Zend_Search_Lucene_Document();

            foreach ($result as $r) {
                $document->addField(Zend_Search_Lucene_Field::Text('term', $r, 'utf-8'));
            }
            
            $index->addDocument($document);
            
            $index->optimize();
            $index->commit();

            if(is_dir($indexPath))
                $this->_delTree($indexPath);

            @rename($newPath, $indexPath);
        }
    }
    */
    
    public function setMultiList()
    {
        @set_time_limit(5 * 60);//5 minutes

        $path = APPLICATION_PATH . '/../search';
        if (is_dir($path) == FALSE){
            @mkdir($path, 0755);
        }
        
        $indexPath = $path.'/index_multi';
        $newPath = $indexPath.'_'.time();
        $type = 'user';
        $photos = new Default_Model_Photos();
        $adresses = new Default_Model_Adresses();
        
        $index = Zend_Search_Lucene::create($newPath);
        $document = new Zend_Search_Lucene_Document();
        
        BEGINN:
            
        $local = NULL;
        $ph = NULL;
        $time = NULL;
        $address = null;
        $select = NULL;
        $text = NULL;
        
        switch ($type){
            case 'user':
                $table = new Community_Model_DbTable_Users();
                $select = $table->select();
                $select->where('type = ?','profil')
                        ->where('deactivated = ?',0);
                        //->where('activation = ?','');
                $title = 'name';
                $item_id = 'userid';
                $next = 'local';
                break;
            case 'local':
                $table = new Default_Model_DbTable_Adresses();
                $title = 'name';
                $item_id = 'id';
                $ph = 'photoid';
                $address = 'address';
                $text = 'description';
                $next = 'event';
                break;
            case 'event':
                $table = new Default_Model_DbTable_Events();
                $select = $table->select();
                $select->where('permission = ?',0)
                        ->where('published = ?',1);
                $title = 'title';
                $item_id = 'id';
                $ph = 'photoid';
                $time = 'start';
                if(in_array('locid', $table->info('cols')))
                    $local = 'locid';
                $text = 'description';
                $next = 'album';
                break;
            case 'album':
                $table = new Default_Model_DbTable_PhotoAlbums();
                $select = $table->select();
                $select->where('permissions = ?',0)
                        ->where('partypics = ?',1);
                $title = 'name';
                $item_id = 'id';
                $ph = 'photoid';
                $time = 'start';
                if(in_array('locid', $table->info('cols')))
                    $local = 'locid';
                $text = 'description';
                $next = NULL;
                break;
            default: return;
        }
        if($select == NULL){
            $data = $table->fetchAll();
        } else {
            $data = $table->fetchAll($select);
        }
        
        if(count($data) > 0){
            foreach ($data as $d){
                $document->addField(Zend_Search_Lucene_Field::unIndexed('itemID', $d->$item_id));
                $document->addField(Zend_Search_Lucene_Field::Text('title',substr($d->$title, 0, 100), 'utf-8'));
                $document->addField(Zend_Search_Lucene_Field::unIndexed('art', 'SEARCH_'.$type, 'utf-8'));
                switch ($type){
                    case 'user':
                        $link = '/community/index/profil/id/'.$d->$item_id;
                        break;
                    case 'local':
                        $link = '/venues/show/id/'.$d->$item_id;
                        break;
                    case 'event':
                        $link = '/events/show/id/'.$d->$item_id;
                        break;
                    case 'album':
                        $link = '/photos/useralbums/view/'.$d->$item_id.'/id/'.$d->creator;
                        break;
                }
                $document->addField(Zend_Search_Lucene_Field::unIndexed('link', $link));
                //Local
                $local_name = NULL;
                if($local != NULL){
                    if($d->$local > 0){
                        $adress = $adresses->getAdress($d->$local);
                        if (isset($adress))
                            $local_name = $adress->name;
                    }
                }
                $document->addField(Zend_Search_Lucene_Field::Text('local', $local_name, 'utf-8'));
                
                //Avatar
                $evimg = NULL;
                if($ph != NULL){
                    if($d->$ph > 0){
                        $photo = $photos->getPhotoID($d->$ph);
                        if (count($photo) > 0)
                            $evimg = $photo->thumbnail;
                    }
                }
                $document->addField(Zend_Search_Lucene_Field::unIndexed('thumb', $evimg));
                
                //Content
                $dContent = NULL;
                if($text != NULL){
                    if($d->$text != NULL && $d->$text != ''){
                        $string = substr($d->$text,0,150);
                        if(strlen($d->$text) > 150)
                            $string = $string.'...';
                        $dContent = $string;
                    }
                }
                $document->addField(Zend_Search_Lucene_Field::text('content', $dContent, 'utf-8'));
                
                //Time
                $dTime = NULL;
                if($time != NULL)
                    $dTime = $d->$time;
                $document->addField(Zend_Search_Lucene_Field::unIndexed('time', $dTime));

                //Address
                $dAddress = NULL;
                if($address != NULL)
                    $dAddress = $d->$address;
                $document->addField(Zend_Search_Lucene_Field::unIndexed('address', $dAddress, 'utf-8'));
                
                $index->addDocument($document);
            }
        }
        
        if($next != NULL){
            $type = $next;
            goto BEGINN;
        }

        $index->optimize();
        $index->commit();
        
        if(is_dir($indexPath))
            $this->_delTree($indexPath);
        
        @rename($newPath, $indexPath);
    }
    
    public function setList($type = 'user')
    {
        @set_time_limit(5 * 60);//5 minutes

        $path = APPLICATION_PATH . '/../search';
        if (is_dir($path) == FALSE){
            @mkdir($path, 0755);
        }
        
        $indexPath = $path.'/index_'.$type;
        $newPath = $indexPath.'_'.time();
        
        $index = Zend_Search_Lucene::create($newPath);
        $document = new Zend_Search_Lucene_Document();
        
        $local = NULL;
        $ph = NULL;
        $time = NULL;
        $address = null;
        $select = NULL;
        
        switch ($type){
            case 'user':
                $table = new Community_Model_DbTable_Users();
                $select = $table->select();
                $select->where('type = ?','profil')
                        ->where('deactivated = ?',0);
                        //->where('activation = ?','');
                $title = 'name';
                $item_id = 'userid';
                break;
            case 'local':
                $table = new Default_Model_DbTable_Adresses();
                $title = 'name';
                $item_id = 'id';
                $ph = 'photoid';
                $address = 'address';
                break;
            case 'event':
                $table = new Default_Model_DbTable_Events();
                $select = $table->select();
                $select->where('permission = ?',0)
                        ->where('published = ?',1);
                $title = 'title';
                $item_id = 'id';
                $ph = 'photoid';
                $time = 'start';
                if(in_array('locid', $table->info('cols')))
                    $local = 'locid';
                break;
            case 'album':
                $table = new Default_Model_DbTable_PhotoAlbums();
                $select = $table->select();
                $select->where('permissions = ?',0)
                        ->where('partypics = ?',1);
                $title = 'name';
                $item_id = 'id';
                $ph = 'photoid';
                $time = 'start';
                if(in_array('locid', $table->info('cols')))
                    $local = 'locid';
                break;
            default: return;
        }
        if($select == NULL){
            $data = $table->fetchAll();
        } else {
            $data = $table->fetchAll($select);
        }
        
        if(count($data) > 0){
            //$view = Zend_Layout::getMvcInstance()->getView();
            
            if($ph != NULL)
                $photos = new Default_Model_Photos();
            if($local != NULL)
                $adresses = new Default_Model_Adresses();
            
            foreach ($data as $d){
                $document->addField(Zend_Search_Lucene_Field::unIndexed('itemID', $d->$item_id));
                $document->addField(Zend_Search_Lucene_Field::Text('title',substr($d->$title, 0, 100), 'utf-8'));
                $document->addField(Zend_Search_Lucene_Field::unIndexed('art', 'SEARCH_'.$type, 'utf-8'));
                switch ($type){
                    case 'user':
                        $link = '/community/index/profil/id/'.$d->$item_id;
                        break;
                    case 'local':
                        $link = '/venues/show/id/'.$d->$item_id;
                        break;
                    case 'event':
                        $link = '/events/show/id/'.$d->$item_id;
                        break;
                    case 'album':
                        $link = '/photos/useralbums/view/'.$d->$item_id.'/id/'.$d->creator;
                        break;
                }
                $document->addField(Zend_Search_Lucene_Field::unIndexed('link', $link));
                //Local
                if($local != NULL){
                    if($d->$local > 0){
                        $adress = $adresses->getAdress($d->$local);
                        (isset($adress)) ? $local_name = $adress->name:$local_name = NULL;
                    } else {
                        $local_name = NULL;
                    }
                    $document->addField(Zend_Search_Lucene_Field::Text('local', $local_name, 'utf-8'));
                }
                //Avatar
                if($ph != NULL){
                    $evimg = NULL;
                    if($d->$ph > 0){
                        $photo = $photos->getPhotoID($d->$ph);
                        if (count($photo) > 0){
                            $evimg = $photo->thumbnail;
                        }
                    }
                    $document->addField(Zend_Search_Lucene_Field::unIndexed('thumb', $evimg));
                }
                //Time
                if($time != NULL){
                    $document->addField(Zend_Search_Lucene_Field::unIndexed('time', $d->$time));
                }
                //Address
                if($address != NULL){
                    $document->addField(Zend_Search_Lucene_Field::unIndexed('address', $d->$address, 'utf-8'));
                }
                
                $index->addDocument($document);
            }
        }

        $index->optimize();
        $index->commit();
        
        if(is_dir($indexPath))
            $this->_delTree($indexPath);
        
        @rename($newPath, $indexPath);
    }

    private function _delTree($dir)
    { 
        $files = array_diff(scandir($dir), array('.','..')); 

        foreach ($files as $file) { 
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file"); 
        }

        return rmdir($dir); 
    }
}
