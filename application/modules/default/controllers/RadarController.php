<?php

/**
 * RadarController.php
 * Description of RadarController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 26.10.2012 11:02:11
 * 
 */
class Default_RadarController extends Zend_Controller_Action
{
    public function init()
    {
        if ($this->_helper->FlashMessenger->hasMessages()) {
            $this->view->flashmessage = $this->_helper->FlashMessenger->getMessages();
        }
    }
    
    public function indexAction()
    {
        $show = (string)$this->_request->getParam('show');
        $Task = (string)$this->_request->getParam('task',null);
        $gSearch = $this->_request->getParam('geosearch',null);
        $session = new Zend_Session_Namespace('geopos');
        if(is_array($gSearch))
            (count($gSearch)>0)?$gSearch=$gSearch[count($gSearch)-1]:$gSearch=$gSearch[0];
        
        if ($gSearch == null){
            $latitude = $session->latitude;
            $longitude = $session->longitude;
            $radius = $session->radius;
            $minlatitude = $session->minlatitude;
            $minlongitude = $session->minlongitude;
            $maxlongitude = $session->maxlongitude;
            $maxlatitude = $session->maxlatitude;
        } else {
            $geoS = $this->_geoSearch($gSearch);
            
            $latitude = $geoS['latitude'];
            $longitude = $geoS['longitude'];
            $radius = $geoS['radius'];
            $minlatitude = $geoS['minlatitude'];
            $minlongitude = $geoS['minlongitude'];
            $maxlongitude = $geoS['maxlongitude'];
            $maxlatitude = $geoS['maxlatitude'];
        }
        
        switch ($show){
            case 'events':
                $get = 'event';
                break;
            case 'photos':
                $get = 'photo';
                break;
            default :
                $this->_helper->redirector('notfound', 'Error', 'default');
        }
                
        if(!$this->_request->isXmlHttpRequest()) { //without json. Only html
            $this->view->headLink()->appendStylesheet('/css/timenavi.css');
            $this->view->jQuery()->addJavascriptFile('/js/n2s.list.js');
            ($Task == 'archive')?$t = '/task/'.$Task:$t = '';
            if ($gSearch == null){
                $link = 'show/'.$show.$t.'/time/';
            } else {
                $link = 'show/'.$show.$t.'/geosearch/'.$gSearch.'/time/';
            }
            
            $html = $this->view->geoSearch('/radar/index/'.$link,$gSearch).'<div id=radius></div><br/>';
            
            $this->view->html = $html;
            $this->view->link = $link;
            $this->view->gsearch = urlencode($gSearch);
            
        } else { //Only json
          $first = (bool)$this->_request->getParam('first',false);
          $time = (int)$this->_request->getParam('time',null);
          $eCount = (int)$this->_request->getParam('count',130);
          $minLon = (float)$this->_request->getParam('minlon');
          $maxLon = (float)$this->_request->getParam('maxlon');
          $minLat = (float)$this->_request->getParam('minlat');
          $maxLat = (float)$this->_request->getParam('maxlat');
          
          $ajaxMod = new Default_Model_Ajaxlist();
          if($time == null){
              $eDate = Zend_Date::now();
              if($get=='photo' && $Task != 'archive'){
                  $eDate->sub('2', Zend_Date::WEEK);
              }
          } else {
              $eDate = new Zend_Date($time);
          }
          $timestamp = $eDate->get(Zend_Date::TIMESTAMP);
          
          if($first == true)//Firs jquery request
          {
            $lat = $latitude;
            $lon = $longitude;
            $searchRadius = $radius;//km
            
            if($minlatitude && $minlongitude
                      && $maxlatitude && $maxlongitude){
                $minLon = $minlongitude;
                $maxLon = $maxlongitude;
                $minLat = $minlatitude;
                $maxLat = $maxlatitude;
            } else {
                $minLon = $lon - $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $maxLon = $lon + $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $minLat = $lat - ($searchRadius / 69);
                $maxLat = $lat + ($searchRadius / 69);
            }
            
            $eradius = 6378.137;
            
            switch ($get){
                case 'event':
                    $noRegion = $this->view->translate('It\'s seems to be no events in your region');
                    $noEvents = $this->view->translate('We have no commend events to show for you. Sorry!');
                    break;
                case 'photo':
                    $noRegion = $this->view->translate('We haven\'t find any albums in your region');
                    $noEvents = $this->view->translate('There are no albums in process');
                    break;
            }
                
            begin:
            
            $eventsData = $ajaxMod->getList($timestamp,$get,$Task,$minLat,$maxLat,$minLon,$maxLon);
            if(count($eventsData) == 0 && $searchRadius < 10000){
                @set_time_limit(60);//1 minute
                $searchRadius = $searchRadius + 20;
                //$Radius = $searchRadius;
                
                $minLon = $lon - $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $maxLon = $lon + $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $minLat = $lat - ($searchRadius / 69);
                $maxLat = $lat + ($searchRadius / 69);
                
                goto begin; // Such weiter
            } elseif (count($eventsData) > 0) {
                $html = '<input id="minLon" type="hidden" value="'.$minLon.'"/>';
                $html .= '<input id="maxLon" type="hidden" value="'.$maxLon.'"/>';
                $html .= '<input id="minLat" type="hidden" value="'.$minLat.'"/>';
                $html .= '<input id="maxLat" type="hidden" value="'.$maxLat.'"/>';
                
                $Radius = acos(sin(0/180*M_PI)*sin(0/180*M_PI)
                        + cos(0/180*M_PI)*cos(0/180*M_PI)*cos($minLon/180*M_PI-$maxLon/180*M_PI) )
                * $eradius; //ellipse-max-radius
                
                $Radius = ceil($Radius*0.1 + $Radius)/2;
                
                ($searchRadius > $radius)?
                    $message = '<div>'.$noRegion.'</div>':
                    $message = false;
                $result = array('error'=>false,'message'=>$message,
                    'radius'=>'<b>'.$this->view->translate('Search radius').':</b> '.$Radius.' '.$this->view->translate('km'),'html'=>$html);
                $this->_helper->json($result);
            } else {
                die($this->_helper->json(array('error'=>true,'action'=>'stop','message'=>$noEvents)));
            }
          } else { // Second jquery request
              $html = '';
              $navhtml = '';
              $stepCount = 0;
              $showMBox = 0;
              $dataCount = 0;
              $jsonYear = FALSE;
              secondbegin:
                  
              $YEAR = $eDate->get(Zend_Date::YEAR);
              $MONTH = $eDate->get(Zend_Date::MONTH);
              $DAY = $eDate->get(Zend_Date::DAY);
              
              $eventsData = $ajaxMod->getList($timestamp, $get, $Task,
                    $minLat, $maxLat, $minLon, $maxLon);
              if(count($eventsData) == 0){
                  if($dataCount == 0){
                    die($this->_helper->json(array('error'=>true,'action'=>'stop','time'=>$timestamp)));
                  } else {
                    goto returnResults;
                  }
              }
              
              $rows = array();
              foreach ($eventsData as $ev){
                  $rows[] = $ev->cid;
              }
              
              ($Task == 'archive')?$start = false:$start = true;
              if($get == 'photo'){
                  $eventsMod = new Default_Model_PhotoAlbums();
                  $events = $eventsMod->getAllPartyAlbumsLast(null,null,null,null,$rows,$start);
              } else {
                  $eventsMod = new Default_Model_Events();
                  $events = $eventsMod->getAllEventsLast(null,null,null,null,$rows,$start);
              }
              
              $photos = new Default_Model_Photos();
              $adresses = new Default_Model_Adresses();
              $locale = Zend_Registry::get('Zend_Locale');
              
                //Main HTML
                $countE = count($events);
                $stepCount = $stepCount + floor(count($events)/4);// 4 ist Zahl von Events in eine Reihe
                if($stepCount < 1)
                    $stepCount = 1;
                $showBox = 0;
                $i = 0;
                $lineCount = 0;//Eventzähler pro Reihe
                $checkYear = FALSE;
                $checkDay = FALSE;
                foreach ($events as $event){
                    $i++;
                    $evDate = new Zend_Date($event->start);
                    if (!$evDate->equals($YEAR, Zend_Date::YEAR) || $time == null) {
                        $checkYear = TRUE;
                        if($showBox == 1){
                            $html .= '</div>';
                            $html .= '</div>';
                            $showBox = 0;
                        }
                        if($showMBox == 1){
                            $html .= '</div>';
                            $showMBox = 0;
                        }
                        $YEAR = $evDate->get(Zend_Date::YEAR);
                        $html .= '<div class="date"><div id="'.$YEAR.'" class="d1 year">'.$YEAR.'</div></div>';
                        if ($time != null)
                            $navhtml .= '</ul>';
                        if($jsonYear == TRUE){
                            $navhtml .= '</div>';
                        } else {
                            $jsonYear = TRUE;
                        }
                        $navhtml .= '<div class="navHover"><div class="navYear" onclick="list.goToByScroll(\''.$YEAR.'\')">'.$YEAR.'</div><ul id="activ">';
                    }
                    if (!$evDate->equals($MONTH, Zend_Date::MONTH) || $time == null || $checkYear == TRUE) {
                        $checkYear = FALSE;
                        $checkDay = TRUE;
                        if($showBox == 1){
                            $html .= '</div>';
                            $html .= '</div>';
                            $showBox = 0;
                        }
                        if($showMBox == 1){
                            $html .= '</div>';
                        }
                        $MONTH = $evDate->get(Zend_Date::MONTH);
                        Zend_Date::setOptions(array('format_type' => 'php'));//For stand-alone $evDate
                        $stAloneMonth = Zend_Locale::getTranslation(array('gregorian', 'stand-alone', 'wide', $evDate->get('n')), 'month', $locale);
                        Zend_Date::setOptions(array('format_type' => 'iso'));//For standart
                        if (is_numeric($stAloneMonth))
                            $stAloneMonth = $evDate->get(Zend_Date::MONTH_NAME);
                        $html .= '<div class="date"><div id="'.$YEAR.'-'.$MONTH.'"
                            onclick="list.fadOutAlbs(\''.$YEAR.'-'.$MONTH.'\')"
                                class="d1 month">'.$stAloneMonth.'</div></div>';
                        $html .= '<div id="mBox'.$YEAR.'-'.$MONTH.'" rel="'.$YEAR.'-'.$MONTH.'" class="mBox">';
                        $showMBox = 1;
                        $navhtml .= '<li id="navM'.$YEAR.'-'.$MONTH.'" class="navM" onclick="list.goToByScroll(\''.$YEAR.'-'.$MONTH.'\')">'.$stAloneMonth.'</li>';
                    }
                    if (!$evDate->equals($DAY, Zend_Date::DAY) || $time == null || $checkDay == TRUE) {
                        $checkDay = FALSE;
                        if($showBox == 1){
                            $html .= '</div>';
                            $html .= '</div>';
                        }
                        $DAY = $evDate->get(Zend_Date::DAY);
                        $html .= '<div class="date2">';
                        $url = array('module'=>'default','action'=>'showday','data'=>$event->start);
                        if($get == 'photo'){
                            $url['controller']='photos';
                        } else {
                            $url['controller']='events';
                        }
                        if ($gSearch != null){
                            $url['geosearch']=$gSearch;
                        }
                        $html .= '<a class="dateDay" href="'.$this->view->url($url,'default', true).'">'.$evDate->get(Zend_Date::WEEKDAY).' '.$DAY.'</a>';
                        $html .= '<div class="dateAlb">';
                        $showBox = 1;
                        $lineCount = 0;
                    }
                    //Event INFO
                    if($event->photoid > 0)
                        $photo = $photos->getPhotoID($event->photoid);
                    if ($event->photoid > 0 && count($photo) > 0 && file_exists($photo->thumbnail)){
                        $evimg = $photo->thumbnail;
                    } else {
                        if($get == 'photo'){
                            $photocount = count($photos->getAllAlbumPhotos($event->creator, $event->id));
                            if($photocount > 0){
                                $photo = $photos->getLastAlbumImg($event->creator, $event->id, '1');
                                (file_exists($photo->thumbnail))?
                                            $evimg=$photo->thumbnail:
                                        $evimg='images/no-photo-thumb.png';
                            } else {
                                $evimg = 'images/no-photo-thumb.png';
                            }
                        } else {
                            $evimg = 'images/no-photo-thumb.png';
                        }
                    }
                    $dataCount++;
                    $lineCount++;
                    switch ($get){
                        case 'photo':
                            $link = $this->view->url(array('module'=>'default',
                                    'controller'=>'photos','action'=>'useralbums',
                                    'view'=>$event->id,'id'=>$event->creator),
                                    'default', true);
                            $title = $event->name;
                                break;
                        case 'event':
                            $link = $this->view->url(array('module'=>'default',
                                    'controller'=>'events','action'=>'show','id'=>$event->id),
                                    'default', true);
                            $title = $event->title;
                                break;
                    }
                    $html .= '<span class="EVBox">';
                    $html .= '<a href="'.$link.'" id="'.$event->start.'" class="eBox">
                                <img src="'.$evimg.'" alt=""/>
                            </a>
                            <div class="EVInfo">';
                    if($get == 'event')
                        $html .= '<a href="'.$link.'" class="timestamp">'.$evDate->get(Zend_Date::TIME_SHORT).'</a>';
                    $html .= '<a href="'.$link.'"><img src="'.$evimg.'" alt=""/></a>
                                <div><a class="EVTitle" href="'.$link.'">'.
                            $this->view->shortText($title,40).'</a></div>';

                    if(0 < $event->locid){
                        $location = $adresses->getAdress($event->locid);
                        if(count($location) > 0){
                            $loclink = $this->view->url(array('module'=>'default',
                                            'controller'=>'venues','action'=>'show',
                                            'id'=>$event->locid),
                                            'default', true);
                                $dist = $this->view->distance($location->latitude, $location->longitude, $latitude, $longitude);
                            $dist = new Zend_Measure_Length(round($dist,1),Zend_Measure_Length::KILOMETER,$locale);
                            $html .= '<div class="TSmal"><a class="EVTitle" href="'.$loclink.'">@ '.
                                        $this->view->shortText($location->name,23).'</a><br/>'.$dist.'</div>';
                        }
                    }
                    $html .= '</div>
                        </span>';
                    if($lineCount == 4){
                        $lineCount = 0;
                        $html .= '<div />';
                    }
                    //END Event INFO
                    $time = $event->start;

                    if($countE == $i){
                        $html .= '</div>';
                        $html .= '</div>';
                    }
                }
                
                //$countE = count($events);
                //$stepCount = $stepCount + floor(count($events)/4);// 4 ist Zahl von Events in eine Reihe
              //die(var_dump($eCount));
                //END Main HTML
                
                if ($eCount > $stepCount * 130){
                    $eDate = new Zend_Date($time);
                    $timestamp = $eDate->get(Zend_Date::TIMESTAMP);
                    goto secondbegin;
                }
                
                returnResults:
                
                if($jsonYear == TRUE)
                    $navhtml = $navhtml.'</ul></div>';
                $result = array('error'=>false,'html'=>$html,'nav'=>$navhtml,'newnav'=>$jsonYear);
                $this->_helper->json($result);
            }
        }
    }
    
    public function relevantAction()
    {
        $show = (string)$this->_request->getParam('show');
        $id = (int)$this->_request->getParam('id',null);
        $time = (int)$this->_request->getParam('time',null);
        $start = (int)$this->_request->getParam('start',0);
        $end = (int)$this->_request->getParam('end',0);
        $title = (string)$this->_request->getParam('title',null);
        
        $venues = new Default_Model_Adresses();
        $venue = $venues->getAdress($id);
        if(!isset($venue))
            $this->_helper->redirector('notfound', 'Error', 'default');
        switch ($show){
            case 'events':
                $get = 'event';
                break;
            case 'photos':
                $get = 'photo';
                break;
            default :
                $this->_helper->redirector('notfound', 'Error', 'default');
        }
        if(!$this->_request->isXmlHttpRequest()) { //without json. Only html
            $this->view->headLink()->appendStylesheet('/css/timenavi.css');
            $this->view->jQuery()->addJavascriptFile('/js/n2s.list.js');
            $venLink = $this->view->url(array('module'=>'default','controller'=>'venues','action'=>'show','id'=>$id),'default', true);
            switch ($get){
                case 'event':
                    $this->view->headTitle(sprintf($this->view->translate('Relevant events to %s'),$venue->name), 'PREPEND');
                    if($title == null)
                        $title = sprintf($this->view->translate('Relevant events to %s'),'<a href="'.$venLink.'">'.$venue->name.'</a>');
                    break;
                case 'photo':
                    $this->view->headTitle(sprintf($this->view->translate('Relevant albums to %s'),$venue->name), 'PREPEND');
                    if($title == null)
                        $title = sprintf($this->view->translate('Relevant albums to %s'),'<a href="'.$venLink.'">'.$venue->name.'</a>');
                    break;
                default :
                    $this->_helper->redirector('notfound', 'Error', 'default');
            }
            $link = 'show/'.$show.'/id/'.$id.'/start/'.$start.'/end/'.$end.'/time/';
            $html = '<h1>'.$title.'</h1>';
            $this->view->html = $html;
            $this->view->link = $link;
        } else { //Only json
            switch ($get){
                case 'event':
                    $noEvents = 'We have not find any relativ events';
                    $count1 = '%d event';
                    $count2 = '%d events';
                    break;
                case 'photo':
                    $noEvents = 'We have not find any relativ photos';
                    $count1 = '%d album';
                    $count2 = '%d albums';
                    break;
                default :
                    $this->_helper->redirector('notfound', 'Error', 'default');
            }
            $html = '';
            $navhtml = '';
            $first = (bool)$this->_request->getParam('first',false);
            if($first == FALSE){
                $eCount = (int)$this->_request->getParam('count',130);
                $eCount = floor($eCount/130);
                if($eCount < 1)
                    $eCount = 1;
            } else {
                $eCount = NULL;
            }
            if($time == null){
                $eDate = Zend_Date::now();
            } else {
                $eDate = new Zend_Date($time);
            }
            ($time == NULL)?$timestamp = NULL:$timestamp = $eDate->get(Zend_Date::TIMESTAMP);
            
            $model = new Default_Model_Ajaxlist();
            $events = $model->getRelative($id, $get, $timestamp, $eCount, $start, $end);
            
            if(count($events) > 0){
                if($first == TRUE){
                    (count($events) == 1)? $evcount = sprintf($this->view->translate($count1),count($events)): $evcount = sprintf($this->view->langHelper($count2,count($events)),count($events));
                    die($this->_helper->json(array('error'=>FALSE,'html'=>'','radius'=>'<b>'.$evcount.'</b>')));
                }
                $photos = new Default_Model_Photos();
                $i = 0;
                $YEAR = $eDate->get(Zend_Date::YEAR);
                $MONTH = $eDate->get(Zend_Date::MONTH);
                $DAY = $eDate->get(Zend_Date::DAY);
                $lineCount = 0;//Eventzähler pro Reihe
                $checkYear = FALSE;
                $checkDay = FALSE;
                $countE = count($events);
                $jsonYear = FALSE;
                $showBox = 0;
                $showMBox = 0;
                $dataCount = 0;
                $stepCount = 0;
                $stepCount = $stepCount + floor(count($events)/4);// 4 ist Zahl von Events in eine Reihe
                if($stepCount < 1)
                    $stepCount = 1;
                $locale = Zend_Registry::get('Zend_Locale');
                foreach ($events as $event){
                    $i++;
                    $evDate = new Zend_Date($event["start"]);
                    if (!$evDate->equals($YEAR, Zend_Date::YEAR) || $time == null) {
                        $checkYear = TRUE;
                        if($showBox == 1){
                            $html .= '</div>';
                            $html .= '</div>';
                            $showBox = 0;
                        }
                        if($showMBox == 1){
                            $html .= '</div>';
                            $showMBox = 0;
                        }
                        $YEAR = $evDate->get(Zend_Date::YEAR);
                        $html .= '<div class="date"><div id="'.$YEAR.'" class="d1 year">'.$YEAR.'</div></div>';
                        if ($time != null)
                            $navhtml .= '</ul>';
                        if($jsonYear == TRUE){
                            $navhtml .= '</div>';
                        } else {
                            $jsonYear = TRUE;
                        }
                        $navhtml .= '<div class="navHover"><div class="navYear" onclick="list.goToByScroll(\''.$YEAR.'\')">'.$YEAR.'</div><ul id="activ">';
                    }
                    if (!$evDate->equals($MONTH, Zend_Date::MONTH) || $time == null || $checkYear == TRUE) {
                        $checkYear = FALSE;
                        $checkDay = TRUE;
                        if($showBox == 1){
                            $html .= '</div>';
                            $html .= '</div>';
                            $showBox = 0;
                        }
                        if($showMBox == 1){
                            $html .= '</div>';
                        }
                        $MONTH = $evDate->get(Zend_Date::MONTH);
                        Zend_Date::setOptions(array('format_type' => 'php'));//For stand-alone $evDate
                        $stAloneMonth = Zend_Locale::getTranslation(array('gregorian', 'stand-alone', 'wide', $evDate->get('n')), 'month', $locale);
                        Zend_Date::setOptions(array('format_type' => 'iso'));//For standart
                        if (is_numeric($stAloneMonth))
                            $stAloneMonth = $evDate->get(Zend_Date::MONTH_NAME);
                        $html .= '<div class="date"><div id="'.$YEAR.'-'.$MONTH.'"
                            onclick="list.fadOutAlbs(\''.$YEAR.'-'.$MONTH.'\')"
                                class="d1 month">'.$stAloneMonth.'</div></div>';
                        $html .= '<div id="mBox'.$YEAR.'-'.$MONTH.'" rel="'.$YEAR.'-'.$MONTH.'" class="mBox">';
                        $showMBox = 1;
                        $navhtml .= '<li id="navM'.$YEAR.'-'.$MONTH.'" class="navM" onclick="list.goToByScroll(\''.$YEAR.'-'.$MONTH.'\')">'.$stAloneMonth.'</li>';
                    }
                    if (!$evDate->equals($DAY, Zend_Date::DAY) || $time == null || $checkDay == TRUE) {
                        $checkDay = FALSE;
                        if($showBox == 1){
                            $html .= '</div>';
                            $html .= '</div>';
                        }
                        $DAY = $evDate->get(Zend_Date::DAY);
                        $html .= '<div class="date2">';
                        $url = array('module'=>'default','action'=>'showday','data'=>$event["start"]);
                        if($get == 'photo'){
                            $url['controller']='photos';
                        } else {
                            $url['controller']='events';
                        }
                        $html .= '<a href="'.$this->view->url($url,'default', true).'" class="dateDay">'.$evDate->get(Zend_Date::WEEKDAY).' '.$DAY.'</a>';
                        $html .= '<div class="dateAlb">';
                        $showBox = 1;
                        $lineCount = 0;
                    }
                    //Event INFO
                    $evimg = 'images/no-photo-thumb.png';
                    if($event["photoid"] > 0)
                        $photo = $photos->getPhotoID($event["photoid"]);
                    if ($event["photoid"] > 0 && count($photo) > 0 && file_exists($photo->thumbnail)){
                        $evimg = $photo->thumbnail;
                    } else {
                        if($get == 'photo'){
                            $photocount = count($photos->getAllAlbumPhotos($event["creator"], $event["cid"]));
                            if($photocount > 0){
                                $photo = $photos->getLastAlbumImg($event["creator"], $event["cid"], '1');
                                if(count($photo) > 0 && file_exists($photo->thumbnail))
                                    $evimg = $photo->thumbnail;
                            }
                        }
                    }
                    $dataCount++;
                    $lineCount++;
                    switch ($get){
                        case 'photo':
                            $link = $this->view->url(array('module'=>'default',
                                    'controller'=>'photos','action'=>'useralbums',
                                    'view'=>$event["cid"],'id'=>$event["creator"]),
                                    'default', true);
                            $title = $event["name"];
                                break;
                        case 'event':
                            $link = $this->view->url(array('module'=>'default',
                                    'controller'=>'events','action'=>'show','id'=>$event["cid"]),
                                    'default', true);
                            $title = $event["title"];
                                break;
                    }
                    $html .= '<span class="EVBox">';
                    $html .= '<a href="'.$link.'" id="'.$event["start"].'" class="eBox">
                                <img src="'.$evimg.'" alt=""/>
                            </a>
                            <div class="EVInfo">';
                    if($get == 'event')
                        $html .= '<a href="'.$link.'" class="timestamp">'.$evDate->get(Zend_Date::TIME_SHORT).'</a>';
                    $html .= '<a href="'.$link.'"><img src="'.$evimg.'" alt=""/></a>
                                <div><a class="EVTitle" href="'.$link.'">'.
                            $this->view->shortText($title,40).'</a></div>';
                    $html .= '</div>
                        </span>';
                    if($lineCount == 4){
                        $lineCount = 0;
                        $html .= '<div />';
                    }
                    //END Event INFO
                    $time = $event["start"];

                    if($countE == $i){
                        $html .= '</div>';
                        $html .= '</div>';
                    }
                }
                if($jsonYear == TRUE)
                    $navhtml = $navhtml.'</ul></div>';
                $result = array('error'=>false,'html'=>$html,'nav'=>$navhtml,'newnav'=>$jsonYear);
            } else {
                $result = array('error'=>true,'action'=>'stop','message'=>$noEvents);
            }
            
            $this->_helper->json($result);
        }
    }

    public function listAction()
    {
        $show = $this->_request->getParam('show');
        $time = (int)$this->_request->getParam('time',null);
        $gSearch = $this->_request->getParam('geosearch',null);
        if(is_array($gSearch))
            (count($gSearch)>0)?$gSearch=$gSearch[count($gSearch)-1]:$gSearch=$gSearch[0];
        
        $session = new Zend_Session_Namespace('geopos');
        
        if ($gSearch == null){
            $latitude = $session->latitude;
            $longitude = $session->longitude;
            $radius = $session->radius;
            $minlatitude = $session->minlatitude;
            $minlongitude = $session->minlongitude;
            $maxlongitude = $session->maxlongitude;
            $maxlatitude = $session->maxlatitude;
        } else {
            $geoS = $this->_geoSearch($gSearch);
            
            $latitude = $geoS['latitude'];
            $longitude = $geoS['longitude'];
            $radius = $geoS['radius'];
            $minlatitude = $geoS['minlatitude'];
            $minlongitude = $geoS['minlongitude'];
            $maxlongitude = $geoS['maxlongitude'];
            $maxlatitude = $geoS['maxlatitude'];
        }
        
        switch ($show){
            case 'events':
                $get = 'event';
                break;
            case 'photos':
                if(!$this->_request->isXmlHttpRequest()) {
                    $this->view->headLink()->appendStylesheet('/css/photos.css');
                    $this->view->headLink()->appendStylesheet('/css/ajaxcontent.css');
                    $this->view->jQuery()->addJavascriptFile('/js/n2s.photos.js');
                    $this->view->jQuery()->addJavascriptFile('/js/n2s.cycle.js');
                }
                $get = 'photo';
                break;
            default :
                $this->_helper->redirector('notfound', 'Error', 'default');
        }
                
        if(!$this->_request->isXmlHttpRequest()) { //without json. Only html
            $this->view->jQuery()->addJavascriptFile('/js/n2s.list.js');
            if ($gSearch == null){
                $link = 'show/'.$show.'/time/'.$time.'/last/';
            } else {
                $link = 'show/'.$show.'/geosearch/'.$gSearch.'/time/'.$time.'/last/';
            }
            $html = $this->view->geoSearch('/radar/list/'.$link,$gSearch).'<div id=radius></div><br/>';
            
            $this->view->html = $html;
            $this->view->link = $link;
            $this->view->gsearch = urlencode($gSearch);
        } else { //Only json
          $first =  $this->_request->getParam('first',false);
          $minLon = $this->_request->getParam('minlon');
          $maxLon = $this->_request->getParam('maxlon');
          $minLat = $this->_request->getParam('minlat');
          $maxLat = $this->_request->getParam('maxlat');
          
          $ajaxMod = new Default_Model_Ajaxlist();
          ($time == null) ? $eDate = Zend_Date::now():
              $eDate = new Zend_Date($time);
          $timestamp = $eDate->get(Zend_Date::TIMESTAMP);
          if($first == true)//Firs jquery request
          {
            $lat = $latitude;
            $lon = $longitude;
            $searchRadius = $radius;//km
            
            if($minlatitude && $minlongitude
                      && $maxlatitude && $maxlongitude){
                $minLon = $minlongitude;
                $maxLon = $maxlongitude;
                $minLat = $minlatitude;
                $maxLat = $maxlatitude;
            } else {
                $minLon = $lon - $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $maxLon = $lon + $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $minLat = $lat - ($searchRadius / 69);
                $maxLat = $lat + ($searchRadius / 69);
            }
            
            switch ($get){
                case 'event':
                    $noRegion = $this->view->translate('It\'s seems to be no events in your region');
                    $noEvents = $this->view->translate('We have no commend events to show for you. Sorry!');
                    break;
                case 'photo':
                    $pDate = new Zend_Date($time);
                    $pDAY = $pDate->get(Zend_Date::DAY_SHORT);
                    $pMONTH = $pDate->get(Zend_Date::MONTH_SHORT);
                    $pYEAR = $pDate->get(Zend_Date::YEAR);
                    $noRegion = $this->view->translate('We haven\'t find any albums in your region');
                    $noEvents = $this->view->translate('There are no albums in process').$pDAY.'|'.$pMONTH.'|'.$pYEAR.'|'.$time;
                    break;
            }
                
            begin:
                
            $eventsData = $ajaxMod->getList($timestamp,$get,null,$minLat,$maxLat,$minLon,$maxLon,true);
            if(count($eventsData) == 0 && $searchRadius < 10000){
                @set_time_limit(60);//1 minute
                $searchRadius = $searchRadius + 20;
                //$Radius = $searchRadius;
                
                $minLon = $lon - $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $maxLon = $lon + $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $minLat = $lat - ($searchRadius / 69);
                $maxLat = $lat + ($searchRadius / 69);
                
                goto begin; // Such weiter
            } elseif (count($eventsData) > 0) {
                $html = '<input id="minLon" type="hidden" value="'.$minLon.'"/>';
                $html .= '<input id="maxLon" type="hidden" value="'.$maxLon.'"/>';
                $html .= '<input id="minLat" type="hidden" value="'.$minLat.'"/>';
                $html .= '<input id="maxLat" type="hidden" value="'.$maxLat.'"/>';
                
                ($searchRadius > $radius)?
                    $message = '<div>'.$noRegion.'</div>':
                    $message = false;
                $result = array('error'=>false,'message'=>$message,'html'=>$html);
                $this->_helper->json($result);
            } else {
                die($this->_helper->json(array('error'=>true,'action'=>'stop','message'=>$noEvents)));
            }
          } else { // Second jquery request              
              $html = '<div style="margin-right: 20px;border-bottom: 1px dotted rgb(153, 153, 153);">';
                            
              $eventsData = $ajaxMod->getList($timestamp, $get, null,
                    $minLat, $maxLat, $minLon, $maxLon, true);
              
              if(count($eventsData) == 0){
                    die($this->_helper->json(array('error'=>true,'action'=>'stop','time'=>$timestamp)));
              }
              
              $rows = array();
              foreach ($eventsData as $ev){
                  $rows[] = $ev->cid;
              }
              //var_dump($rows);
              if($get == 'photo'){
                  $eventsMod = new Default_Model_PhotoAlbums();
                  $events = $eventsMod->getAllPartyAlbumsLast(null,null,null,null,$rows);
              } else {
                  $eventsMod = new Default_Model_Events();
                  $events = $eventsMod->getAllEventsLast(null,null,null,null,$rows,true);
              }
              
              $locale = Zend_Registry::get('Zend_Locale');
              
              if($get == 'photo'){
                $html = $this->view->action('albums','photos','default',array('rows'=>$rows,'page'=>1));
                $mainDist = $this->view->distance($minLat, $minLon, $maxLat, $maxLon);
              } else {
                  $photos = new Default_Model_Photos();
                  $adresses = new Default_Model_Adresses();
                    //Main HTML
                  $lineCount = 0;
                  $mainDist = 0;
                  foreach ($events as $event){
                        $lineCount++;
                        if($lineCount == 1)
                            $html .= '<div class="EvLine" style="border-top: 1px dotted rgb(153, 153, 153);">';
                        //Event Photo
                        $photo = $photos->getPhotoID($event->photoid);
                        if (isset($photo) && file_exists($photo->thumbnail)){
                            $evimg = $photo->thumbnail;
                        } else {
                            $evimg = 'images/no-photo-thumb.png';
                        }//END Event Photo
                        switch ($get){
                            case 'event':
                                $link = $this->view->url(array('module'=>'default',
                                        'controller'=>'events','action'=>'show','id'=>$event->id),
                                        'default', true);
                                $title = $event->title;
                                    break;
                        }
                        $Time = new Zend_Date($event->start);
                        $location = $adresses->getAdress($event->locid);
                        $html .= '<div class="LBox" style="min-height: 125px;float: left;';
                        if($lineCount == 1)
                            $html .= 'border-right: 1px dotted rgb(153, 153, 153);';
                        $html .= 'padding: 5px; width: 48%;overflow: hidden;">';
                        $now = Zend_Date::now();
                        $endTime = new Zend_Date($event->end);
                        if($endTime->isEarlier($now))
                            $html .= '<div class="artOF">'.$this->view->translate('ARCHIVE').'</div>';
                        $html .= '<div  style="margin-right: 15px;width:100px;height:100px;float:left;">';
                        $html .= '<a href="'.$link.'">';
                        $html .= '<img  style="width:100px;height:100px;" src="'.$evimg.'" alt=""/>';
                        $html .= '</a><div style="text-align: center; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(51, 51, 51);">'.$Time->get(Zend_Date::TIME_SHORT).'</div></div>';
                        $html .= '<div style="margin-left:115px;"><h3><a class="black" href="'.$link.'">'.
                                $this->view->shortText($title,200).'</a></h3>';
                        if(count($location) > 0){
                            $formAdress = $location->address;
                            $dist = $this->view->distance($location->latitude, $location->longitude, $latitude, $longitude);
                            if($mainDist < $dist)
                                $mainDist = $dist;
                            $dist = new Zend_Measure_Length(round($dist,1),Zend_Measure_Length::KILOMETER,$locale);
                            $loclink = $this->view->url(array('module'=>'default',
                                                    'controller'=>'venues','action'=>'show',
                                                    'id'=>$location->id),
                                                    'default', true);
                            $html .= '<div><b>'.$this->view->translate('Location').':</b><br /><a href="'.$loclink.'">@ '.$this->view->shortText($location->name,40).'</a></div>';
                            $html .= '<div class="TSmal">'.$formAdress.'<br/>'.$dist.'</div>'.$this->view->guestList($event->id);
                        }
                        $html .= '</div></div>';
                        if($lineCount == 2){
                            $html .= '<div class="clear"></div></div>';
                            $lineCount = 0;
                        }
                        //END Event INFO
                        $time = $event->start;
                    }
                    if($lineCount == 1)
                        $html .= '<div class="clear"></div></div>';
                    $html .= '<div class="clear"></div>';
                }
                
                $mainDist = new Zend_Measure_Length(ceil($mainDist),Zend_Measure_Length::KILOMETER,$locale);
                $result = array('error'=>false,'html'=>$html,'radius'=>'<b>'.$this->view->translate('Search radius').':</b> '.$mainDist);
                $this->_helper->json($result);
            }
        }
    }
        
    private function _geoSearch($pos)
    {
        $geoloc = N2S_GeoCode_GoogleGeocode::googleGeocode($pos);
        
        if(is_array($geoloc) && count($geoloc) > 0){
            $a_lat = $geoloc['ne_lat'];
            $a_long = $geoloc['ne_lng'];
            $b_lat = $geoloc['sw_lat'];
            $b_long = $geoloc['sw_lng'];
            $eradius = 6378.137;

            $distanz = acos(sin($b_lat/180*M_PI)*sin($a_lat/180*M_PI) + cos($b_lat/180*M_PI)*cos($a_lat/180*M_PI)*cos($b_long/180*M_PI-$a_long/180*M_PI) ) * $eradius;

            if($a_lat > $b_lat){
                $min_lat = $b_lat;
                $max_lat = $a_lat;
            } else {
                $min_lat = $a_lat;
                $max_lat = $b_lat;
            }

            if($a_long > $b_long){
                $min_long = $b_long;
                $max_long = $a_long;
            } else {
                $min_long = $a_long;
                $max_long = $b_long;
            }

            $searchLat = $geoloc['lat'];
            $searchLon = $geoloc['lng'];

            if(ceil($distanz)/2 < 2){
                $searchRadius = 2;
                $min_lat = $searchLat - ($searchRadius / 69);
                $min_long = $searchLon - $searchRadius / abs(cos(deg2rad($searchLat)) * 69);
                $max_lat = $searchLat + ($searchRadius / 69);
                $max_long = $searchLon + $searchRadius / abs(cos(deg2rad($searchLat)) * 69);
            } else {
                $searchRadius = ceil($distanz)/2;
            }
            
            $result = array(
                'radius' => $searchRadius,
                'latitude' => $searchLat,
                'longitude' => $searchLon,
                'minlatitude' => $min_lat,
                'minlongitude' => $min_long,
                'maxlatitude' => $max_lat,
                'maxlongitude' => $max_long
            );
            return $result;
        }
    }
}