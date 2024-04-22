<?php

/**
 * UserBanner.php
 * Description of UserBanner
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 09.11.2012 14:48:10
 * 
 */
class N2S_View_Helper_UserBanner extends Zend_View_Helper_Abstract
{
    function userBanner($storage,$id,$owner=FALSE)
    {
        $images = new Default_Model_Background();
        $img = $images->getImg($id, $storage);
        $first = FALSE;
        $ex = TRUE;
        $map = FALSE;
        $browse = '';
        $positionLink = '/events/ajax/act/posit/for/'.$storage;
        if($owner == TRUE){
            $this->view->jQuery()->addJavascriptFile('/js/plupload/plupload.js');
            $this->view->jQuery()->addJavascriptFile('/js/plupload/plupload.flash.js');
            $this->view->jQuery()->addJavascriptFile('/js/plupload/plupload.html5.js');
        }
        $imgex=FALSE;
        if(isset($img)){
            $photos = new Default_Model_Photos();
            $photo = $photos->getPhotoID($img->imageid);
            if($owner == TRUE && file_exists($photo->original)){
                list($width, $height) = getimagesize($photo->original);
                $p = 740/$width;
                $h = $height*$p;
                $imgex=TRUE;
                $phShow = $photo->original;
                $top = $img->top;
            } elseif($owner == FALSE && file_exists($img->image)) {
                $p = 740;
                $h = 200;
                $imgex=TRUE;
                $phShow = $img->image;
                $top = 0;
            } else {
                $images->delImg($id, $storage);
            }
        }
        if($imgex==TRUE){
            $image = '';
            $permis = Community_Model_Permissions::getPermissions($photo->creator);
            if($owner == FALSE && $photo->permissions <= $permis)
                $image .= '<a class="n2s-phBox n2lbox.ajax" href="'.$this->view->url(array("module"=>"default","controller"=>"photo","action"=>"view","id"=>$img->imageid),"default",true).'">';
            $image .= '<img id="n2s-moveable" rel="'.$h.'" style="image-rendering:optimizeQuality; -ms-interpolation-mode:bicubic;position:absolute;width:740px;top:'.$top.'px;" src="'.$phShow.'" alt=""/>';
            if($owner == FALSE && $photo->permissions <= $permis)
                $image .= '</a>';
        } else {
            if($owner == TRUE){
                $image = '';
                $browse = '<div id="n2s-newloadban"><div id="browse" style="position:absolute;width:740px;height:198px;border:1px solid #999999;"><a class="n2s-imgup n2lbox.ajax" href="/ajax/imgup/task/usbannchange"><p style="font-weight: bold; font-size: 20px; text-align: center; color: rgb(170, 170, 170); padding: 86px 0px;">'.$this->view->translate('Click to add a photo').'</p></a></div></div>';
                $first = TRUE;
            } else {
                switch ($storage) {
                    case 'event':
                        $events = new Default_Model_Events();
                        $event = $events->getEvent($id);
                        break;
                    case 'venue':
                        $events = new Default_Model_Adresses();
                        $event = $events->getAdress($id);
                        break;
                }
                $request = Zend_Controller_Front::getInstance()->getRequest();
                if($storage == 'venue'){
                    if(isset($event)){
                        if($request->isXmlHttpRequest()){
                            $image = '<iframe class="n2lbox-iframe" scrolling="no" frameborder="0" src="/events/map/static/1/id/'.$id.'/height/200" hspace="0"></iframe>';
                        } else {
                            $image = $this->view->simpleMaps($id,200);
                        }
                        $map = TRUE;
                    } else {
                        $ex = FALSE;
                    }
                } elseif($storage == 'profil'){
                    $image = '';
                    $map = TRUE;
                    $ex = FALSE;
                } else {
                    if($event && $event->locid > 0){
                        if($request->isXmlHttpRequest()){
                            $image = '<iframe class="n2lbox-iframe" scrolling="no" frameborder="0" src="/events/map/static/1/id/'.$event->locid.'/height/200" hspace="0"></iframe>';
                        } else {
                            $image = $this->view->simpleMaps($event->locid,200);
                        }
                        $map = TRUE;
                    } else {
                        $ex = FALSE;
                    }
                }
            }
        }
        $html = '<input type="hidden" name="positbanner" value="'.$positionLink.'/target/'.$id.'"/>';
        if($ex == TRUE){
            $html .= '<div id="proPr1" style="border-bottom: 10px solid #FFFFFF;"><div id="n2s-backImg" class="n2s-buttonArray">';
            if($owner == TRUE){
                $html .= '<div id="n2s-rotations" class="n2s-transpb">'.$this->view->translate('Drag to change position').'</div>';
                $html .= '<a class="n2s-imgup n2lbox.ajax" href="'.$this->view->url(array("module"=>"default","controller"=>"ajax","action"=>"imgup","task"=>"usbannchange"),"default",true).'"><div id="n2s-imgupdate" class="n2s-transpb">'.$this->view->translate('Change background photo').'</div></a>';
            }
            $html .= $image;
            $html .= '</div></div>';
            $html .= $browse;
            if($owner == TRUE){
                $html .= '<script type="text/javascript">';
                $html .= '$(document).ready(function() {';
                if($first == FALSE){
                    $html .= 'if($("#n2s-moveable").length)';
                    $html .= 'n2s.ajax.banner();';
                } else {
                    $html .= 'if($("#browse").length) $("#browse").css({"top":$("#proPr1").position().top+"px"});';
                }
                $html .= '});</script>';
            }
        }
        $result = array('map'=>$map,'html'=>$html);
        return $result;
    }
}