<?php

/**
 * HomeAlbums.php
 * Description of HomeAlbums
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 02.05.2013 22:53:04
 * 
 */
class N2S_View_Helper_HomeAlbums extends Zend_View_Helper_Abstract
{
    function homeAlbums()
    {
        $ajaxMod = new Default_Model_Ajaxlist();
        
        $eDate = Zend_Date::now();
        $timestamp = $eDate->get(Zend_Date::TIMESTAMP);
        
        $session = new Zend_Session_Namespace('geopos');
        
        $lat = $session->latitude;
        $lon = $session->longitude;
        $searchRadius = $session->radius;
        $minLat = $session->minlatitude;
        $minLon = $session->minlongitude;
        $maxLon = $session->maxlongitude;
        $maxLat = $session->maxlatitude;
        
        $getLim = 8;
        begin:
            
        $eventsData = $ajaxMod->getHomeList($timestamp,'photo',$minLat,$maxLat,$minLon,$maxLon,$getLim);
        
        if(count($eventsData) < $getLim && $searchRadius < 10000){
            @set_time_limit(60);//1 minute
            $searchRadius = $searchRadius + 20;
            //$Radius = $searchRadius;

            $minLon = $lon - $searchRadius / abs(cos(deg2rad($lat)) * 69);
            $maxLon = $lon + $searchRadius / abs(cos(deg2rad($lat)) * 69);
            $minLat = $lat - ($searchRadius / 69);
            $maxLat = $lat + ($searchRadius / 69);
            
            goto begin; // Such weiter
        }
        
        $eradius = 6378.137;
        $Radius = acos(sin(0/180*M_PI)*sin(0/180*M_PI)+cos(0/180*M_PI)*cos(0/180*M_PI)*cos($minLon/180*M_PI-$maxLon/180*M_PI) )*$eradius; //ellipse-max-radius

        $Radius = ceil($Radius*0.1 + $Radius)/2;
        
        $rows = array();
        foreach ($eventsData as $ev){
            $rows[] = $ev->cid;
        }
        
        $albums = new Default_Model_PhotoAlbums();
        $events = $albums->getAllPartyAlbumsLast(null,null,null,null,$rows);
        
        $photos = new Default_Model_Photos();
        $addresses = new Default_Model_Adresses();
        
        $count = 0;
        $albhtml = '<div id="n2s-albcontent">';
        $albhtml .= '<div class="n2Module">';
        $albhtml .= '<h3 style="margin:0px;"><a style="color:rgb(255, 255, 255);" href="'.$this->view->url(array('module'=>'default','controller'=>'photos','action'=>'index'),'default', true).'">'.$this->view->translate('Last uploaded partypics').'</a><span class="hhelp right">'.$this->view->translate('Search radius').': '.$Radius.$this->view->translate('km').'</span></h3>';
        foreach ($events as $alb){
            $aurl = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'useralbums','view'=>$alb->id,'id'=>$alb->creator), 'default', true);

            $photocount = $alb->photocount;
            if($alb->photoid > 0){
                $photo = $photos->getPhotoID($alb->photoid);
                if($photo->albumid != $alb->id || !file_exists(BASE_PATH.'/'.$photo->original)){
                    $albums->updateComProfilImgAlbum($alb->id, 0);
                    if($photocount > 0){
                        $photo = $photos->getLastAlbumImg($alb->creator, $alb->id, 1);
                    }
                }
            }elseif($photocount > 0){
                $photo = $photos->getLastAlbumImg($alb->creator, $alb->id, 1);
            }
            $albhtml .= '<div class="n2s-albbox">';
            $count++;
            $albhtml .= '<div class="n2s-albsur"><a class="img_link" href="'.$aurl.'"><div class="n2s-albthumb" rel="'.$alb->id.'">';
            if ($photocount > 0 && $photo){
                (file_exists(BASE_PATH.'/'.$photo->original))?$albimg=$photo->original:$albimg=$photo->image;
                $width = $photo->width;
                $height = $photo->height;
                if($width == 0 && $height == 0){
                    $width = 225;
                    $height= 150;
                }
                if($width > $height){
                    $height = ($height*305)/$width;
                    $width = 225;
                    if($height < 150){
                        $width = ($width*150)/$height;
                        $height = 150;
                    }
                } else {
                    $width = ($width*150)/$height;
                    $height = 150;
                    if($width < 225){
                        $height = ($height*225)/$width;
                        $width = 225;
                    }
                }
                ($height>150)?$top=(150-$height)/4:$top=0;
                ($width>225)?$left=(225-$width)/2:$left=0;
                $albhtml .= '<img class="n2s-mainAlbPh" style="margin-left:'.$left.'px;margin-top:'.$top.'px;width:'.$width.'px;height:'.$height.'px;" src="'.$albimg.'" alt=""/>';
            } else {
                $albhtml .= '<img src="images/no-photo-albcanvas.png" alt=""/>';
            }
            if($alb->locid > 0){
                $address = $addresses->getAdress($alb->locid);
                if($address){
                    $albnamehtml = '<div><a href="'.$this->view->url(array('module'=>'default','controller'=>'venues','action'=>'show','id'=>$alb->locid),'default', true).'">@ '.$address->name.'</a></div>';
                } else {
                    $albnamehtml = '';
                    $updata=array('locid'=>'0','location'=>'','latitude'=>'255','longitude'=>'255');
                    $albums->updateAlbum($alb->id, $updata);
                }
            } else {
                $albnamehtml = '';
            }
            $albhtml .= '</div></a>';
            $albhtml .= '<div class="gdyf"><div class="n2s-albtitle">';
            $albhtml .= '<div class="fbkl">';
            $albhtml .= '<a class="img_link" href="'.$aurl.'"><div class="n2s-albname">'.$alb->name.'</div></a>';
            $albhtml .= '<div class="n2s-camera">'.$photocount.'</div>';
            $albhtml .= '</div>';
            $albhtml .= '<div class="description">'.$albnamehtml.'<div>'.$alb->description.'</div>';
            $date = new Zend_Date($alb->partydate);
            $eventDate = $date->get(Zend_Date::DATE_MEDIUM);
            $albhtml .= '<div class="adMod">'.$eventDate.'</div></div>';
            $albhtml .= '</div></div></div>';
            $albhtml .= '</div>';
        }
        if($count < 8){
            $getLim++;
            goto begin; 
        }
        
        $albhtml .= '</div><div class="clear"></div></div>';
        
        return $albhtml;
    }
}
