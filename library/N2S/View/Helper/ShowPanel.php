<?php

/**
 * ShowPanel.php
 * Description of ShowPanel
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 07.02.2013 18:33:29
 * 
 */
class N2S_View_Helper_ShowPanel extends Zend_View_Helper_Abstract
{
    function showPanel($creator,$type,$owner=false,$id = null,$start = 0,$end = 0,$cid = 0)
    {
        $permis = Community_Model_Permissions::getPermissions($creator);
        
        $count = 0;
        $html = '';
        
        if($creator > 0 && $type != 'event' && $type != 'album'){
            //events
            $events = new Default_Model_Events();
            $event = $events->getUserEvents($creator, 1, $permis);
            if($owner == TRUE || 0 < count($event)){
                $html .= $this->_display($creator,$event,'events',$owner);
                $count++;
            }
            //photos
            $photos = new Default_Model_Photos();
            $photo = $photos->getAllUserPhotos($creator, $permis);
            if($owner == TRUE || 0 < count($photo)){
                $html .= $this->_display($creator,$photo,'photos',$owner);
                $count++;
            }
        }
        
        if(($type == 'venue' || (($type == 'album' || $type == 'event') && $start > 0 && $end > 0)) && $id != null){
            $ajaxModel = new Default_Model_Ajaxlist();
            if($type != 'event'){
                //relevant events
                $event = $ajaxModel->getRelative($id,'event',NULL,NULL,$start,$end);
                if($owner == TRUE || 0 < count($event)){
                    $html .= $this->_display($creator,$event,'relevents',$owner,$id,$start,$end,$cid,$type);
                    $count++;
                }
            }
            if($type != 'album'){
                //relevant albums
                $album = $ajaxModel->getRelative($id,'photo',NULL,NULL,$start,$end);
                if($owner == TRUE || 0 < count($album)){
                    $html .= $this->_display($creator,$album,'albums',$owner,$id,$start,$end,$cid,$type);
                    $count++;
                }
            }
        }
        
        if($type == 'profil'){
            //friends
            $friends = new Community_Model_FrRequest();
            $friend = $friends->getFriendsList($creator);
            if(count($friend) > 0){
                $list = array();
                foreach ($friend as $fr){
                    $list[] = $fr->connect_from;
                }
                $users = new Community_Model_Users();
                $friend = $users->getUsersInList($list,TRUE);
            }
            if($owner == TRUE || 0 < count($friend)){
                $html .= $this->_display($creator,$friend,'friends',$owner);                
                $count++;
            }
        }
        
        $width = $count * 125;
        $phtml = '<div style="width:'.$width.'px;font-size:11px;float:right;padding:10px 0px 10px 10px;">';
        $phtml .= $html;
        $phtml .= '<div class="clear"></div></div>';
        return array('html' => $phtml,'width' => 675 - $width);
    }
    
    private function _display($creator,$events,$type,$owner = FALSE,$id = null,$start = 0,$end = 0,$cid = 0,$cType = null)
    {
        $edit = false;
        switch ($type){
            case 'friends':
                $title = 'Friends';
                $phid = 'avatar';
                $link = $this->view->url(array('module'=>'community',
                            'controller'=>'friends','action'=>'show','id'=>$creator),'default', true);
                break;
            case 'relevents':
                $title = 'Relevant events';
                $phid = 'photoid';
                if(count($events) == 1){
                    foreach ($events as $e){
                        $evID = $e->cid;
                    }
                    $linkArray = array('module'=>'default',
                                'controller'=>'events','action'=>'show','id'=>$evID);
                } else {
                    $linkArray = array('module'=>'default',
                                'controller'=>'events','action'=>'showrelevant','id'=>$id);
                    if($start > 0 && $end > 0){
                        $linkArray['start'] = $start;
                        $linkArray['end'] = $end;
                    }
                    if($cid > 0 && $cType != null){
                        $linkArray['task'] = $cType;
                        $linkArray['cid'] = $cid;
                    }
                }
                $link = $this->view->url($linkArray,'default', true);
                break;
            case 'albums':
                $title = 'Relevant albums';
                $phid = 'photoid';
                if(count($events) == 1){
                    foreach ($events as $e){
                        $evID = $e->cid;
                        $evCr = $e->creator;
                    }
                    $linkArray = array('module'=>'default',
                                'controller'=>'photos','action'=>'useralbums','view'=>$evID,'id'=>$evCr);
                } else {
                    $linkArray = array(
                        'module'=>'default','controller'=>'photos','action'=>'showrelevant','id'=>$id);
                    if($start > 0 && $end > 0){
                        $linkArray['start'] = $start;
                        $linkArray['end'] = $end;
                    }
                    if($cid > 0 && $cType != null){
                        $linkArray['task'] = $cType;
                        $linkArray['cid'] = $cid;
                    }
                }
                $link = $this->view->url($linkArray,'default', true);
                break;
            case 'photos':
                if($owner == TRUE)
                    $edit = TRUE;
                $editLink = $this->view->url(array('module'=>'default',
                    'controller'=>'photos','action'=>'create'),'default', true);
                $editTitle = 'Create new album';
                $title = 'Photos';
                $phid = 'id';
                $link = $this->view->url(array('module'=>'default',
                            'controller'=>'photos','action'=>'userphotos','id'=>$creator),'default', true);
                break;
            case 'events':
                if($owner == TRUE)
                    $edit = TRUE;
                $editLink = $this->view->url(array('module'=>'default',
                    'controller'=>'events','action'=>'create'),'default', true);
                $editTitle = 'Create new event';
                $title = 'Events';
                $phid = 'photoid';
                if(count($events) == 1){
                    foreach ($events as $e){
                        $evID = $e->id;
                    }
                    $linkArray = array('module'=>'default',
                                'controller'=>'events','action'=>'show','id'=>$evID);
                } else {
                    $linkArray = array('module'=>'default',
                            'controller'=>'events','action'=>'userevents','id'=>$creator);
                }
                $link = $this->view->url($linkArray,'default', true);
                break;
        }
        $count = $this->_count(count($events));
        $photos = new Default_Model_Photos();
        $i = 0;
        $html = '<div class="n2s-displayarray left">';
        if($edit == TRUE)
            $html .= '<a class="n2simg-button n2lbox.ajax" href="'.$editLink.'"><span class="n2s-displayadd n2s-tooltip" title="'.$this->view->translate($editTitle).'"></span></a>';
        $html .= '<a href="'.$link.'">';
        $html .= '<div class="n2s-displayblock">';
        if($count['count'] == 2)
            $html .= '<div style="position: absolute; width: 150px; left: -20px;">';
        foreach ($events as $event){
            if($i == $count['count'])
                break;
            $i++;
            $evimg = $this->view->baseUrl().'images/no-photo-thumb.png';
            $photo = $photos->getPhotoID($event->$phid);
            if (isset($photo) && file_exists($photo->thumbnail)){
                $evimg = $photo->thumbnail;
            } else {
                if($type == 'friends'){
                    switch ($event->gender){
                        case "m":
                            $gender = "male";
                            break;
                        case "f":
                            $gender = "female";
                            break;
                        default:
                            $gender = "default";
                    }
                    $evimg = $this->view->baseUrl().'images/avatar/default/'.$gender.'.jpg';
                } elseif($type == 'albums'){
                    $photocount = count($photos->getAllAlbumPhotos($event->creator, $event->cid));
                    if($photocount > 0){
                        $photo = $photos->getLastAlbumImg($event["creator"], $event["cid"], '1');
                        if(count($photo) > 0 && file_exists($photo->thumbnail))
                            $evimg = $photo->thumbnail;
                    }
                }
            }
            $html .= '<img  style="float:left;width:'.$count['width'].'height:'.$count['height'].'" src="'.$evimg.'" alt=""/>';
        }
        if($count['count'] == 2)
            $html .= '</div>';
        
        $html .= '';
        if($type == 'friends' && $owner == FALSE){
            $auth = Zend_Auth::getInstance();
            if($auth->hasIdentity() && $auth->getIdentity()->type == 'profil' && $auth->getIdentity()->userid != $creator){
                $friends = new Community_Model_FrRequest();
                $common = $friends->getCommonFriends($creator, $auth->getIdentity()->userid);
                if(count($common) > 0){
                    $html .= '<span style="';
                    $html .= 'position: absolute; top: 30px; left: 5px; font-weight: bold; padding: 1px 5px; border-radius: 4px 4px 4px 4px; background-color: rgb(153, 153, 153); color: rgb(244, 244, 244); border: 1px double rgb(244, 244, 244); box-shadow: 0px 2px 5px rgb(0, 0, 0); text-shadow: 0px 2px 5px rgb(0, 0, 0);';
                    $html .= '">'.$this->view->translate('common').' '.count($common).'</span>';
                }
            }
        }
        $html .= '</div></a><a href="'.$link.'">'.$this->view->translate($title).'</a> <span style="color:#999;">'.count($events).'</span>';
        $html .= '</div>';
        return $html;
    }

    private function _count($count)
    {
        $result = array();
        if($count >= 6){
            $result['count'] = 6;
            $result['width'] = '37px;';
            $result['height'] = '37px;';
        }elseif($count < 6 && $count >= 2){
            $result['count'] = 2;
            $result['width'] = '74px;';
            $result['height'] = '74px;';
        }else{
            $result['count'] = 1;
            $result['width'] = '111px;';
            $result['height'] = '111px;';
        }
        return $result;
    }
}
