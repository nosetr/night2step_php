<?php

/**
 * HomeVenues.php
 * Description of HomeVenues
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.05.2013 21:04:42
 * 
 */
class N2S_View_Helper_HomeVenues extends Zend_View_Helper_Abstract
{
    function homeVenues()
    {
        $adresses = new Default_Model_Adresses();
        $photos = new Default_Model_Photos();
        
        $session = new Zend_Session_Namespace('geopos');
        
        $lat = $session->latitude;
        $lon = $session->longitude;
        $searchRadius = $session->radius;
        $minLat = $session->minlatitude;
        $minLon = $session->minlongitude;
        $maxLon = $session->maxlongitude;
        $maxLat = $session->maxlatitude;
        
        $getLim = 4;
        begin:
        
        $eventsData = $adresses->getList($minLat, $maxLat, $minLon, $maxLon, NULL, $getLim);
        
        if(count($eventsData) < $getLim && $searchRadius < 10000){
            @set_time_limit(60);//1 minute
            $searchRadius = $searchRadius + 20;

            $minLon = $lon - $searchRadius / abs(cos(deg2rad($lat)) * 69);
            $maxLon = $lon + $searchRadius / abs(cos(deg2rad($lat)) * 69);
            $minLat = $lat - ($searchRadius / 69);
            $maxLat = $lat + ($searchRadius / 69);
            
            goto begin; // Such weiter
        }
        
        $eradius = 6378.137;
        $Radius = acos(sin(0/180*M_PI)*sin(0/180*M_PI)+cos(0/180*M_PI)*cos(0/180*M_PI)*cos($minLon/180*M_PI-$maxLon/180*M_PI) )*$eradius; //ellipse-max-radius

        $Radius = ceil($Radius*0.1 + $Radius)/2;
        
        $locale = Zend_Registry::get('Zend_Locale');
        
        $html = '<div>';
        $html .= '<div class="n2Module">';
        $html .= '<h3 style="margin:0px;"><a href="'.$this->view->url(array('module'=>'default','controller'=>'venues','action'=>'index'),'default', true).'" style="color:rgb(255, 255, 255);">'.$this->view->translate('Last added venues').'</a><span class="hhelp right">'.$this->view->translate('Search radius').': '.$Radius.$this->view->translate('km').'</span></h3>';
        if(count($eventsData) > 0){
            foreach ($eventsData as $event){
                $dist = $this->view->distance($event->latitude, $event->longitude, $lat, $lon);
                $dist = new Zend_Measure_Length(round($dist,1),Zend_Measure_Length::KILOMETER,$locale);

                $photo = $photos->getPhotoID($event->photoid);
                if (isset($photo) && file_exists($photo->thumbnail)){
                    $evimg = $photo->thumbnail;
                } else {
                    $evimg = '/images/no-photo-marker-thumb.png';
                }

                $title = $event->name;
                $link = $this->view->url(array('module'=>'default',
                                            'controller'=>'venues','action'=>'show','id'=>$event->id),
                                            'default', true);
                
                $html .= '<div itemtype="http://schema.org/LocalBusiness" itemscope itemprop="location" class="LBox evArM">';
                    $html .= '<div  style="margin-right: 15px;width:100px;height:100px;float:left;">';
                        $html .= '<a itemprop="url" href="'.$link.'">';
                            $html .= '<img itemprop="image" style="width:100px;height:100px;" src="'.$evimg.'" alt=""/>';
                        $html .= '</a>';
                    $html .= '</div>';
                    $html .= '<div style="margin-left:115px;"><h3><a class="black" href="'.$link.'"><span itemprop="name">'.$this->view->shortText($title,200).'</span></a></h3>';
                    $html .= '<div>'.$this->view->addressSchemaHtml($event->address).'</div>';    
                    $html .= '<div class="TSmal">'.$dist.'</div>';
                    $html .= '</div>';
                $html .= '</div>';
            }
        }
        $html .= '</div><div class="clear"></div></div>';
        
        return $html;
    }
}
