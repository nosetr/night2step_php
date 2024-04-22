<?php

/**
 * HomeEvents.php
 * Description of HomeEvents
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 03.05.2013 14:24:32
 * 
 */
class N2S_View_Helper_HomeEvents extends Zend_View_Helper_Abstract
{
    function homeEvents()
    {
        $ajaxMod = new Default_Model_Ajaxlist();
        $eventsMod = new Default_Model_Events();
        $photos = new Default_Model_Photos();
        $adresses = new Default_Model_Adresses();
        
        $now = Zend_Date::now();
        $timestamp = $now->get(Zend_Date::TIMESTAMP);
        
        $session = new Zend_Session_Namespace('geopos');
        
        $lat = $session->latitude;
        $lon = $session->longitude;
        $searchRadius = $session->radius;
        $minLat = $session->minlatitude;
        $minLon = $session->minlongitude;
        $maxLon = $session->maxlongitude;
        $maxLat = $session->maxlatitude;
        
        $fRows = array();
        $fRadius = 0;
        $fHtml = '';
        
        $getLim = $archLim = 4;
        $archive = FALSE;
        $start = TRUE;
        $eradius = 6378.137;
        begin:
            
        $eventsData = $ajaxMod->getHomeList($timestamp,'event',$minLat,$maxLat,$minLon,$maxLon,$getLim,$archive);
        
        if(count($eventsData) < $getLim){
            if($searchRadius < 10000){
                @set_time_limit(60);//1 minute
                $searchRadius = $searchRadius + 20;

                $minLon = $lon - $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $maxLon = $lon + $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $minLat = $lat - ($searchRadius / 69);
                $maxLat = $lat + ($searchRadius / 69);

                goto begin; // Such weiter
            } else {
                if($archive == FALSE){
                    if(count($eventsData) > 0){
                        $getLim = $archLim = $getLim - count($eventsData);
                        foreach ($eventsData as $ev){
                            $fRows[] = $ev->cid;
                            $dist = $this->view->distance($ev->latitude, $ev->longitude, $lat, $lon);
                            if($dist > $fRadius)
                                    $fRadius = $dist;
                        }
                        $events = $eventsMod->getAllEventsLast(null, null, null, null, $fRows, $start);
                        if(count($events) > 0){
                            foreach ($events as $event){
                                $photo = $photos->getPhotoID($event->photoid);
                                if (count($photo) > 0 && file_exists($photo->thumbnail)){
                                    $evimg = $photo->thumbnail;
                                } else {
                                    $evimg = 'images/no-photo-thumb.png';
                                }
                                $link = $this->view->url(array('module'=>'default',
                                        'controller'=>'events','action'=>'show','id'=>$event->id),
                                        'default', true);
                                $title = $event->title;
                                $Time = new Zend_Date($event->start);
                                $endTime = new Zend_Date($event->end);
                                $location = $adresses->getAdress($event->locid);
                                $formAdress = $location->address;
                                $loclink = $this->view->url(array('module'=>'default',
                                                        'controller'=>'venues','action'=>'show',
                                                        'id'=>$location->id),
                                                        'default', true);

                                $fHtml .= '<div';
                                if($Time->isLater($now))
                                    $fHtml .= ' itemtype="http://schema.org/Event" itemscope';
                                $fHtml .= ' class="LBox evArM" style="height:130px;">';

                                if($endTime->isEarlier($now))
                                {
                                    $fHtml .= '<div class="artOF" style="right:-40%;">'.$this->view->translate('ARCHIVE').'</div>';                    
                                }

                                $fHtml .= '<div  style="margin-right: 15px;width:100px;height:100px;float:left;">';
                                $fHtml .= '<a';
                                if($Time->isLater($now))
                                    $fHtml .=' itemprop="url"';
                                $fHtml .= ' href="'.$link.'">';
                                $fHtml .= '<img';
                                if($Time->isLater($now))
                                    $fHtml .= ' itemprop="image"';
                                $fHtml .= ' style="width:100px;height:100px;" src="'.$evimg.'" alt=""/>';
                                $fHtml .= '</a>';
                                $fHtml .= '<div style="text-align: center; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(51, 51, 51);">'.$Time->get(Zend_Date::TIME_SHORT).'</div>';
                                $fHtml .= '</div>';
                                $fHtml .= '<div style="margin-left:115px;"><h3><a class="black" href="'.$link.'"><span';
                                if($Time->isLater($now))
                                    $fHtml .= ' itemprop="name"';
                                $fHtml .= '>'.$this->view->shortText($title,200).'</span></a></h3>';
                                $fHtml .= '<div>';
                                $fHtml .= '<b>'.$this->view->translate('Start').':</b> <time';
                                if($Time->isLater($now))
                                    $fHtml .= ' itemprop="startDate" content="'.$Time->get(Zend_Date::ISO_8601).'"';
                                $fHtml .= '>'.$Time->get(Zend_Date::DATE_LONG).'</time><br />';
                                $fHtml .= '<div';
                                if($Time->isLater($now))
                                    $fHtml .= ' itemprop="location" itemtype="http://schema.org/Place" itemscope';
                                $fHtml .= '><a';
                                if($Time->isLater($now))
                                    $fHtml .= ' itemprop="url"';
                                $fHtml .= ' href="'.$loclink.'">@ <span';
                                if($Time->isLater($now))
                                    $fHtml .= ' itemprop="name"';
                                $fHtml .= '>'.$this->view->shortText($location->name,40).'</span></a>';
                                $fHtml .= '<div class="TSmal">';
                                if($Time->isLater($now)){
                                    $fHtml .= $this->view->addressSchemaHtml($formAdress);
                                } else {
                                    $fHtml .= $formAdress;
                                }
                                $fHtml .= '</div>';
                                $fHtml .= '</div></div>';
                                if($event->gastlist == 1)
                                        $fHtml .= $this->view->guestList($event->id);
                                $fHtml .= '</div>';
                                $fHtml .= '</div>';
                            }
                        }
                    }
                    $archive = TRUE;
                    $start = false;
                    $searchRadius = $session->radius;
                    $minLat = $session->minlatitude;
                    $minLon = $session->minlongitude;
                    $maxLon = $session->maxlongitude;
                    $maxLat = $session->maxlatitude;
                    
                    goto begin; // Such weiter
                }
            }
        }
        
        $Radius = acos(sin(0/180*M_PI)*sin(0/180*M_PI)+cos(0/180*M_PI)*cos(0/180*M_PI)*cos($minLon/180*M_PI-$maxLon/180*M_PI) )*$eradius; //ellipse-max-radius
        if($Radius < $fRadius)
            $Radius = $fRadius;
        $Radius = ceil($Radius*0.1 + $Radius)/2;
        
        $rows = array();
        foreach ($eventsData as $ev){
            $rows[] = $ev->cid;
        }
        
        //$rows = array_merge($fRows, $rows);
        
        $events = $eventsMod->getAllEventsLast(null, null, null, null, $rows, $start);
        
        if(count($events) < $archLim){
            $getLim++;
            goto begin; 
        }
        
        $html = '<div>';
        $html .= '<div class="n2Module">';
        $html .= '<h3 style="margin:0px;"><a style="color:rgb(255, 255, 255);" href="'.$this->view->url(array('module'=>'default','controller'=>'events','action'=>'index'),'default', true).'">'.$this->view->translate('Events').'</a><span class="hhelp right">'.$this->view->translate('Search radius').': '.$Radius.$this->view->translate('km').'</span></h3>';
        $html .= $fHtml;
        if(count($events) > 0){
            foreach ($events as $event){
                $photo = $photos->getPhotoID($event->photoid);
                if ($photo && file_exists($photo->thumbnail)){
                    $evimg = $photo->thumbnail;
                } else {
                    $evimg = 'images/no-photo-thumb.png';
                }
                $link = $this->view->url(array('module'=>'default',
                        'controller'=>'events','action'=>'show','id'=>$event->id),
                        'default', true);
                $title = $event->title;
                $vok = array('"');
                $seoTitle = str_replace($vok, "", $title);
                $Time = new Zend_Date($event->start);
                $endTime = new Zend_Date($event->end);
                $location = $adresses->getAdress($event->locid);
                $formAdress = $location->address;
                $loclink = $this->view->url(array('module'=>'default',
                                        'controller'=>'venues','action'=>'show',
                                        'id'=>$location->id),
                                        'default', true);

                $html .= '<div';
                if($Time->isLater($now))
                    $html .= ' itemtype="http://schema.org/Event" itemscope';
                $html .= ' class="LBox evArM" style="height:130px;">';

                if($endTime->isEarlier($now))
                    $html .= '<div class="artOF" style="right:-40%;">'.$this->view->translate('ARCHIVE').'</div>';

                $html .= '<div style="margin-right: 15px;width:100px;height:100px;float:left;">';
                $html .= '<a';
                if($Time->isLater($now))
                    $html .=' itemprop="url"';
                $html .= ' title="'.$seoTitle.'" href="'.$link.'">';
                $html .= '<img';
                if($Time->isLater($now))
                    $html .= ' itemprop="image"';
                $html .= ' style="width:100px;height:100px;" src="'.$evimg.'" alt="'.$seoTitle.'"/>';
                $html .= '</a>';
                $html .= '<div style="text-align: center; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(51, 51, 51);">'.$Time->get(Zend_Date::TIME_SHORT).'</div>';
                $html .= '</div>';
                $html .= '<div style="margin-left:115px;"><h3><a class="black" href="'.$link.'"><span';
                if($Time->isLater($now))
                    $html .= ' itemprop="name"';
                $html .= '>'.$this->view->shortText($title,200).'</span></a></h3>';
                $html .= '<div>';
                $html .= '<b>'.$this->view->translate('Start').':</b> <time';
                if($Time->isLater($now))
                    $html .= ' itemprop="startDate" content="'.$Time->get(Zend_Date::ISO_8601).'"';
                $html .= '>'.$Time->get(Zend_Date::DATE_LONG).'</time><br />';
                $html .= '<div';
                if($Time->isLater($now))
                    $html .= ' itemprop="location" itemtype="http://schema.org/Place" itemscope';
                $html .= '><a';
                if($Time->isLater($now))
                    $html .= ' itemprop="url"';
                $html .= ' href="'.$loclink.'">@ <span';
                if($Time->isLater($now))
                    $html .= ' itemprop="name"';
                $html .= '>'.$this->view->shortText($location->name,40).'</span></a>';
                $html .= '<div class="TSmal">';
                if($Time->isLater($now)){
                    $html .= $this->view->addressSchemaHtml($formAdress);
                } else {
                    $html .= $formAdress;
                }
                $html .= '</div></div>';
                $html .= '</div>';
                if($event->gastlist == 1)
                        $html .= $this->view->guestList($event->id);
                $html .= '</div>';
                $html .= '</div>';
            }
        }
        
        $html .= '</div><div class="clear"></div></div>';
        
        return $html;
    }
}
