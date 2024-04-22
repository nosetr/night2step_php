<?php

/**
 * ActivitiesController.php
 * Description of ActivitiesController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 20.03.2013 17:42:31
 * 
 */
class Default_ActivitiesController extends Zend_Controller_Action
{
    public function init()
    {
        
    }
    
    public function ajaxAction()
    {
        if($this->_request->isXmlHttpRequest()){
            $act = (string)$this->_request->getParam('act');
            $id = (int)$this->_request->getParam('id',0);
            $html = '';
            $error = TRUE;
            $message = $this->view->translate('Error');
            if($act == 'map'){
                $adresses = new Default_Model_Adresses();
                $location = $adresses->getAdress($id);
                if(isset($location)){
                    $message = FALSE;
                    $error = FALSE;
                    $html = '<iframe class="n2lbox-iframe" scrolling="no" frameborder="0" src="/events/map/static/1/id/'.$id.'/height/200" hspace="0"></iframe>';
                }
            }
            $result = array('error'=>$error,'html'=>$html,'message'=>$message);
            $this->_helper->json($result);
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }
    }
    
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet('/css/activs.css');
        $this->view->jQuery()->addJavascriptFile('/js/n2s.activs.js');
        $html = '';
        $exHtml = '';
        $html .= '<ul id="actstrim">';
        $auth = Zend_Auth::getInstance();
        $task = (string)  $this->_request->getParam('task', 'all');
        if ($auth->hasIdentity() && $task != 'all') {
            $user = $auth->getIdentity()->userid;
            $exHtml .= '<input type="hidden" name="acticid" value=""/>';
            $exHtml .= '<input type="hidden" name="actitarget" value=""/>';
            $html .= '<li>';
            $html .= '<div class="act_tmb">';
            $html .= $this->view->userThumb($user,1,0);
            $html .= '</div>';
            $html .= '<div class="act_cnt" style="padding-bottom: 15px;"><span class="arCL"></span>';
            $html .= '<div class="n2Jh" id="n2Jh-mAr">';
            $html .= '<textarea id="actUPosSt" class="PoAcDy" onfocus="javascript:actvt.tarresize(this);" placeholder="'.$this->view->translate('What\'s on your mind?').'"></textarea>';
            $html .= '<div class="knop">';
            //$html .= '<span onclick="javascript:actvt.addex(\'photo\');" class="n2s-tooltip" title="'.$this->view->translate('Add a photo').'" id="gImGto">img</span>';
            $html .= '<span onclick="javascript:actvt.addex(\'video\');" class="n2s-tooltip" title="'.$this->view->translate('Add a video').'" id="gViGto">video</span>';
            $html .= '<span onclick="javascript:actvt.send();" id="gSeGto">'.$this->view->translate('send').'</span>';
            $html .= '</div>';
            $html .= '<div id="exA"><div class="exACont" id="exACont-photo">';
            $html .= '<div onclick="javascript:actvt.hideex();" class="n2lbox-close"></div>';
            $html .= '<div id="n2s-imgupAc"><a class="n2s-imgup n2lbox.ajax" href="/ajax/imgup/task/usbannchange">';
            $html .= 'Change background photo';
            $html .= '</a></div>';
            $html .= '</div>';
            $html .= '<div class="exACont" id="exACont-video">';
            $html .= '<div onclick="javascript:actvt.hideex();" class="n2lbox-close"></div><div id="vidAA">';
            $html .= '<input class="creator-video-url inputbox hint" type="text" name="videoUrl" value="" size="36" placeholder="'.$this->view->translate('Enter a video link from YouTube hier...').'"/>';
            $html .= '<div id="knAdV"><span onclick="javascript:actvt.getvideo();">'.$this->view->translate('Add a video').'</span></div>';
            //$html .= '<div id="prob"></div>';
            $html .= '</div></div>';
            $html .= '<div id="erhContA"></div>';
            $html .= '<div id="exALoad"><img src="images/ajax/ajax-loader1.gif" alt=""/></div>';
            $html .= '</div></div>';
            $html .= '<div id="exALoad2" class="loM"><img src="images/ajax/ajax-loader1.gif" alt=""/></div>';
            $html .= '</div>';
            $html .= '</li>';
        } else {
            $html .= '<li style="padding: 0px;"></li>';
        }
        $html .= '</ul>';
        $html .= '<div id="n2s-content-loading"><div id="last_msg_loader"><div id="n2s-msg-loader"><b>'.$this->view->translate('Loading...').'</b><img src="images/ajax/ajax-loader3.gif" alt=""/></div></div></div>';
        
        if($task == 'all'){
            $this->view->link = '';
        } else {
            $cid = (int)$this->_request->getParam('cid',0);
            $this->view->link = '/task/'.$task.'/cid/'.$cid;
            $exHtml .= '<input type="hidden" value="'.$task.'" name="active-task"/>';
            $exHtml .= '<input type="hidden" value="'.$cid.'" name="active-cid"/>';
        }
        $this->view->html = $html.$exHtml;
    }

    public function showAction()
    {
        if(!$this->_request->isXmlHttpRequest())
            $this->_helper->redirector('notfound','Error','default');
            
        $wHeight = (int)$this->_request->getParam('count', 1300);
        $last = (int)  $this->_request->getParam('last', NULL);
        $first = (bool)  $this->_request->getParam('first', FALSE);
        $page = (int)  $this->_request->getParam('page', 0);
        $cid = (int)$this->_request->getParam('cid',0);
        $page++;
        $task = (string)  $this->_request->getParam('task', 'all');
        $mCount = floor($wHeight / 130);
        if ($mCount < 5){
            $mCount = 5;
        } elseif ($mCount > 20) {
            $mCount == 20;
        }
        
        $chFr = false;
        $fr = null;
        $noCheck = FALSE;
        
        if($last != NULL && $task == 'all'){
            $fr = (string)  $this->_request->getParam('showusers', FALSE);
            if($fr != FALSE && $fr != "false"){
                $fr = explode(',', $fr);
                if(is_array($fr) && count($fr) > 0){
                    $noCheck = TRUE;
                } else {
                    $c = 0;
                    goto END;
                }
            }
        }
        
        $ahtml = '';
        $app = null;
        $users = new Community_Model_Users();
        switch ($task){
            case 'all':
                $cid = 0;
                if($noCheck == FALSE){
                    $curuser = N2S_User::curuser();
                    $frs = new Community_Model_FrRequest();
                    $fr = $frs->getFriendsActivities($curuser);
                    if(!is_array($fr) || count($fr) == 0){
                        $c = 0;
                        goto END;
                    }
                    if($fr['task'] == 'activs' && $last == null){
                        $fr = $fr['users'];
                        $usAr = implode(',', $fr);
                        $chFr = true;
                    } else {
                        $fr = $fr['users'];
                    }
                }
                break;
            case 'profil':               
                $user = $users->getUser($cid);
                if(isset($user) && $user->type == 'profil'){
                    $fr = array($cid);
                } else {
                    $c = 0;
                    goto END;
                }
                break;
            case 'event':
                $model = new Default_Model_Events();
                $event = $model->getEvent($cid);
                if(isset($event)){
                    $app = 'events';
                } else {
                    $c = 0;
                    goto END;
                }
                break;
            case 'venue':
                $model = new Default_Model_Events();
                $model = new Default_Model_Adresses();
                $venue = $model->getAdress($cid);
                if(isset($venue)){
                    $app = 'venues';
                } else {
                    $c = 0;
                    goto END;
                }
                break;
            default :
                $c = 0;
                goto END;
        }
        
        START:
        
        $activs = new Community_Model_Activities();
        $activ = $activs->getActivs($last,$fr,$cid,$app,$first);
        
        if($chFr == TRUE){
            $ahtml .= '<li class="act_strLi"><div class="act_cnt">';
            $ahtml .= '<h3>'.$this->view->translate('Add friends to see what they share').'</h3>';
            foreach ($fr as $f){
                $ahtml .= '<div class="adfract left">';
                $ahtml .= $this->view->userThumb($f);
                $ahtml .= '<div class="frract">'.$this->view->friendRequest($f,'float:left;').'</div>';
                $ahtml .= '</div>';
            }
            $ahtml .= '<div class="clear"></div></div></li>';
        }
        
        $c = 0;
        if(count($activ) > 0){
            foreach ($activ as $act)
            {
                $permis = Community_Model_Permissions::getPermissions($act->actor);
                if ($act->permission <= $permis){
                    $delete = FALSE;
                    $show = TRUE;
                    $user = $users->getUser($act->actor);
                    if(!isset($user)){
                        $activs->delActivs($act->id);
                    } elseif($user->deactivated == '0') {
                        $titleHtml = '';
                        if(isset($act->title) && $act->title != ''){
                            if($act->action == 'post'){
                                $tt = $this->view->shortText($act->title,200,TRUE);
                                $titel = array(
                                    'html' => $tt,
                                    'error'=> FALSE,
                                    'show' => TRUE
                                );
                            } else {
                                $titel = $this->_title($act,$permis);
                            }
                            $titleHtml = '<div class="act_t">'.$titel['html'].'</div>';
                            $delete = $titel['error'];
                            $show = $titel['show'];
                        }
                        if($delete == FALSE && $show == TRUE){
                            $contentHtml = '';
                            $content = '_'.$act->app.ucfirst($act->action);
                            if(method_exists($this, $content) || $act->action == 'post'){
                                if($act->action == 'post' && $act->content != NULL){
                                    $cont = $this->postcontent($act,$permis);
                                    $contentHtml= '<div class="act_cont">'.$cont['html'].'</div>';
                                    $delete = $cont['error'];
                                    $show = $cont['show'];
                                } elseif(method_exists($this, $content)){
                                    $cont = $this->$content($act,$permis);
                                    $contentHtml= '<div class="act_cont">'.$cont['html'].'</div>';
                                    $delete = $cont['error'];
                                    $show = $cont['show'];
                                }
                            }
                        }
                    }
                    if($delete == TRUE){
                        $activs->delActivs($act->id);
                    } elseif($show == TRUE) {
                        $Time = new Zend_Date($act->created);
                        $time = $Time->toString('HH:mm');
                        if($Time->isToday() || $Time->isYesterday()){
                            if($Time->isYesterday())
                                $time = $this->view->translate('yesterday').' '.$time;
                        } else {
                            $time = $Time->get(Zend_Date::DATE_FULL).' '.$time;
                        }

                        $html = '<li class="act_strLi" id="'.$act->id.'">';
                        $html .= '<div class="act_tmb">';
                        $html .= $this->view->userThumb($act->actor,1,0);
                        $html .= '</div><div class="act_cnt">';
                        $html .= '<span class="arCL"></span>';
                        $html .= '<div class="act_inf">';
                        $html .= '<div class="act_intop">';
                        $html .= '<span class="aITs">';
                        $html .= '<a class="n2s-tooltip" title="'.$this->view->toolTip($user->name,$user->userid).'" href="'.$this->view->userLink($act->actor).'">';
                        $html .= $user->name.'</a></span>';
                        $html .= '<span class="aITs">'.$time.'</span>';
                        $html .= '<span class="aITs">•</span><span class="aITs">';
                        ($act->permission > 0)?$html .= $this->view->translate('restricted'):$html .= $this->view->translate('public');
                        $html .= '</span></div>';
                        if($act->action == 'post'){
                            $shFro = FALSE;
                            switch ($act->app){
                                case 'profile':
                                    if($act->target != $act->actor && $cid != $act->target){
                                        $shUser = $users->getUser($act->target);
                                        if(isset($shUser)){
                                            $link = $this->view->url(array("module"=>"community","controller"=>"index","action"=>"profil","id"=>$act->target), 'default', true);
                                            $tit = $shUser->name;
                                            $shFro = TRUE;                                        
                                        }
                                    }
                                    break;
                                case 'events':
                                    if($task != 'event'){
                                        $events = new Default_Model_Events();
                                        $result = $events->getEvent($act->cid);
                                        if(isset($result)){
                                            $link = $this->view->url(array("module"=>"default","controller"=>"events","action"=>"show","id"=>$act->cid), 'default', true);
                                            $tit = $result->title;
                                            $shFro = TRUE;
                                        }
                                    }
                                    break;
                                case 'venues':
                                    if($task != 'venue'){
                                        $venues = new Default_Model_Adresses();
                                        $result = $venues->getAdress($act->cid);
                                        if(isset($result)){
                                            $link = $this->view->url(array("module"=>"default","controller"=>"venues","action"=>"show","id"=>$act->cid), 'default', true);
                                            $tit = $result->name;
                                            $shFro = TRUE;
                                        }
                                    }
                                    break;
                            }
                            
                            if($shFro == TRUE){
                                $html .= '<div class="shFrom">'.$this->view->translate('shFrom_'.$act->app).':&nbsp;<a href="'.$link.'">'.$tit.'</a></div>';
                            }
                        }
                        $html .= $titleHtml;
                        $html .= '</div>';
                        $html .= $contentHtml;
                        if($act->locid > 0){
                            $html .= '<div class="act_inf_bot">';
                            $html .= '<span id="mp'.$act->id.'" class="opac INFO_map" onclick="javascript:actvt.getMap('.$act->locid.','.$act->id.')">';
                            $html .= $this->view->translate('Map this');
                            $html .= '</span>';
                            $html .= '</div>';
                        }
                        $html .= '</div>';
                        if($act->comment != NULL){
                            $html .= '<div class="act_comm">';
                            $html .= $this->view->comments($act->cid,$act->comment,3,FALSE);
                            $html .= '</div>';
                        }
                        $html .= '</li>';
                        $c++;
                        $ahtml .= $html;
                    }
                    if($c == $mCount)
                        break;
                }
            }
        } elseif($task == 'all' && $last == null && $chFr == false) {
            $chFr = true;
            $fr = array();
            $frs = $users->getMostActiv();
            if(count($frs) > 0){
                foreach ($frs as $f){
                    $fr[] = $f->userid;
                }
                goto START;
            }
        }
        
        END:
        if($c == 0){
            $result = array('error'=>TRUE,'action'=>'stop');
        } else {
            if($page == 10)
                $ahtml .= '<div id="actdone" onclick="javascript:actvt.scroll.done()">'.$this->view->translate('More').'</div>';
            $result = array('error'=>FALSE,'html'=>$ahtml,'page'=>$page);
            if($task == 'all' && $chFr == TRUE && isset($usAr) && $last == null){
                $result['users'] = $usAr ;
            }
        }
        $this->_helper->json($result);
    }

    private function _friendsCreated($act,$permis)
    {
        $error = TRUE;
        $html = '';
        $users = new Community_Model_Users();
        $actor = $users->getUser($act->actor);
        $target = $users->getUser($act->target);
        if(isset($actor) && isset($target)){
            $curuser = N2S_User::curuser();
            if($curuser == $act->target){
                $target = $actor;
            } elseif($curuser != $act->actor) {
                $frs = new Community_Model_FrRequest();
                $fr = $frs->checkIfFriend($curuser, $act->actor);
                if($fr == FALSE)
                    $target = $actor;
            }
            $error = FALSE;
            $images = new Default_Model_Background();
            $img = $images->getImg($target->userid, 'profil');
            (isset($img))?$backG = $img->image:$backG = NULL;
            $link = $this->view->userLink($target->userid);
            $html .= '<div class="act_contSur">'.$this->_page($link,$target->avatar,$backG,$target->type,$target->gender);
            $html .= '<div class="infPAc';
            if($backG == NULL)
                $html .= ' margPAc';
            $html .= '"><h3><a href="'.$link.'">'.$target->name.'</a></h3><ul>';
            $permission = Community_Model_Permissions::getPermissions($target->userid);
            $aModel = new Community_Model_UserAbout();
            $rA = $aModel->getAllAbout($target->userid,$permission);
            $abCount = 0;
            foreach ($rA as $r){
                if($r->value != null){
                    $abCount++;
                    if($r->name == 'birthdate'){
                        $val = new Zend_Date($r->value);
                        $value = $val->get(Zend_Date::DATE_LONG);
                    } else {
                        $value = $r->value;
                    }

                    $html .= '<li><span class="n2s-tooltip infospan INFO_'.$r->name.'" title="'.$this->view->translate('INFO_'.$r->name).'">';
                    $html .= $this->view->shortText($this->view->escape($value),90,TRUE,FALSE);
                    $html .= '</span></li>';
                    if($abCount == 4)
                        break;
                } else {
                    $aModel->delAbout($target->userid, $r->param_id);
                }
            }
            $html .= '<li>'.$this->view->friendRequest($target->userid,'float:left;').'</li>';
            $html .= '</ul></div>';
            $html .= '<div class="clear"></div></div>';
        }
        return array('error'=>$error,'html'=>$html,'show'=>TRUE);
    }
    
    private function _venuesCreated($act,$permis)
    {
        $error = TRUE;
        $html = '';
        $venues = new Default_Model_Adresses();
        $venue = $venues->getAdress($act->cid);
        if(isset($venue)){
            $error = FALSE;
            $images = new Default_Model_Background();
            $img = $images->getImg($act->cid, 'venue');
            (isset($img))?$backG = $img->image:$backG = NULL;
            $link = $this->view->url(array("module"=>"default","controller"=>"venues","action"=>"show","id"=>$act->cid), 'default', true);
            $html .= '<div class="act_contSur">'.$this->_page($link,$venue->photoid,$backG,null,null,'images/no-photo-marker-thumb.png');
            $html .= '<div class="infPAc';
            if($backG == NULL)
                $html .= ' margPAc';
            $html .= '"><ul>';
            $html .= '<li><span class="n2s-tooltip infospan INFO_map" title="'.$this->view->translate('Address').'">'.$venue->address.'</li>';
            $html .= '</ul></div>';
            $html .= '<div class="clear"></div></div>';
        }
        return array('error'=>$error,'html'=>$html,'show'=>TRUE);
    }

    private function _eventsCreated($act,$permis)
    {
        $error = TRUE;
        $show = TRUE;
        $html = '';
        $events = new Default_Model_Events();
        $event = $events->getEvent($act->cid);
        if(isset($event)){
            $error = FALSE;
            if($event->permission > $permis){
                $show = FALSE;
            } else {
                $images = new Default_Model_Background();
                $img = $images->getImg($act->cid, 'event');
                (isset($img))?$backG = $img->image:$backG = NULL;
                $link = $this->view->url(array("module"=>"default","controller"=>"events","action"=>"show","id"=>$act->cid), 'default', true);
                $html .= '<div class="act_contSur">'.$this->_page($link,$event->photoid,$backG);
                $html .= '<div class="infPAc';
                if($backG == NULL)
                    $html .= ' margPAc';
                $html .= '"><ul>';
                if($event->start > 0){
                    $Time = new Zend_Date($event->start);
                    $html .= '<li><span class="n2s-tooltip infospan INFO_date" title="'.$this->view->translate('Beginning').'">';
                    $html .= $Time->get(Zend_Date::DATE_FULL).' '.$Time->toString('HH:mm');
                    $html .= '</span></li>';
                }
                $adresses = new Default_Model_Adresses();
                $location = $adresses->getAdress($event->locid);
                if(isset($location)){
                    $html .= '<li>';
                    $html .= '<span class="n2s-tooltip infospan INFO_hometown" title="'.$this->view->translate('location').'">';
                    $html .= '<a href="';
                    $html .= $this->view->url(array('module'=>'default','controller'=>'venues','action'=>'show','id'=>$event->locid),'default', true);
                    $html .= '">'.$location->name.'</a>';
                    $html .= '</span>';
                    $html .= '</li>';
                }
                if($event->description || $event->specials){
                    ($event->description)?$tD = $event->description:$tD = $event->specials;
                    $html .= '<li><span class="n2s-tooltip infospan INFO_about" title="'.$this->view->translate('description').'">';
                    $html .= $this->view->shortText($tD,90,TRUE);
                    $html .= '</span></li>';
                }
                $html .= '</ul></div>';
                $html .= '<div class="clear"></div></div>';
            }
        }
        return array('error'=>$error,'html'=>$html,'show'=>$show);
    }
    
    private function _page($link, $avatar = null, $bg = null, $profil = null, $gender = null, $thumb = null)
    {
        $photos = new Default_Model_Photos();
        $html = '';
        if($bg != null){
            $html .= '<a href="'.$link.'">';
            $html .= '<img class="act_bgA" src="'.$bg.'" alt=""/></a>';
        }
        if($avatar != NULL){
            $photo = $photos->getPhotoID($avatar);
            if (isset($photo) && file_exists($photo->thumbnail)){
                $evimg = $photo->thumbnail;
            } else {
                $evimg = $this->view->baseUrl().'images/no-photo-thumb.png';
                if($thumb != null && file_exists($thumb))
                    $evimg = $this->view->baseUrl().$thumb;
                if($profil == 'profil'){
                    switch ($gender){
                        case "m":
                            $gender = "male";
                            break;
                        case "f":
                            $gender = "female";
                            break;
                        default:
                            $gender = "default";
                    }
                    $evimg = 'images/avatar/default/'.$gender.'.jpg';
                } elseif($profil == 'venue') {
                    $evimg = $this->view->baseUrl().'images/no-photo-marker-thumb.png';
                }
            }
            $html .= '<div class="act-uAavatar';
            if($bg != null){
                $html .= ' right';
            } else {
                $html .= ' left';
            }
            $html .= '">';
            $html .= '<a href="'.$link.'">';
            $html .= '<img class="act-Aavat_surround" ';
            $html .= 'src="'.$evimg.'" alt=""/></a>';
            $html .= '</div>';
        }
        return $html;
    }
    
    private function _albumsCreated($act,$permis)
    {
        $error = FALSE;
        $show = TRUE;
        $html = '';
        $photos = new Default_Model_Photos();
        $albums = new Default_Model_PhotoAlbums();
        $album = $albums->getComAlbumInfo($act->cid);
        if(!isset($album)){
            $error = TRUE;
        } elseif($album->photocount > 0 && $album->permissions <= $permis) {
            $photocount = $album->photocount;
            if($album->photoid > 0){
                $photo = $photos->getPhotoID($album->photoid);
                if($photo->albumid != $album->id || !file_exists(BASE_PATH.'/'.$photo->original)){
                    $albums->updateComProfilImgAlbum($album->id, 0);
                    if($photocount > 0){
                        $photo = $photos->getLastAlbumImg($album->creator, $album->id, 1);
                    }
                }
            }elseif($photocount > 0){
                $photo = $photos->getLastAlbumImg($album->creator, $album->id, 1);
            }

            if(isset($photo) && (file_exists($photo->original) || file_exists($photo->image))){
                if(file_exists($photo->original)){
                    $ph = $photo->original;
                    $width = $photo->width;
                    $height = $photo->height;
                } else {
                    $ph = $photo->image;
                    list($width, $height) = getimagesize($ph);
                }

                $hght = 370;
                if($height >= $width){
                    if($height < 370)
                        $hght = $height;
                } else {
                    if($width > 568){
                        $shght = 568 * $height / $width;
                        if($shght < 370)
                            $hght = $shght;
                    } else {
                        if($height < 370)
                            $hght = $height;
                    }
                }
                if($hght < 250)
                    $hght = 250;
                
                $lPhotos = $photos->getLastAlbumImg($album->creator, $album->id, 5, $photo->id);
                $lHtml = '';
                $lGet = '';
                if(count($lPhotos) > 0){
                    (count($lPhotos) == 1)? $count = sprintf($this->view->translate('%d photo'),$photocount): $count = sprintf($this->view->langHelper('%d photos',$photocount),$photocount);
                    $lHtml .= '<div><a href="'.$this->view->url(array('module'=>'default','controller'=>'photos','action'=>'useralbums','view'=>$album->id,'id'=>$album->creator),'default', true).'">';
                    $lHtml .= $count.'</a></div>';
                    foreach ($lPhotos as $l){
                        if (file_exists($l->thumbnail)){
                            $lHtml .= '<a class="n2s-phBox n2lbox.ajax" href="'.$this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$l->id,'show'=>'album'),'default', true).'">';
                            $lHtml .= '<img src="'.$l->thumbnail.'" width="134" height="134" alt=""/></a>';
                        }
                    }
                    $lHtml .= '<div class="clear"></div>';
                    $lGet = '50';
                }
                
                $locHtml = '';
                if($act->locid > 0){
                    $adresses = new Default_Model_Adresses();
                    $location = $adresses->getAdress($act->locid);
                    if(isset($location)){
                        $locHtml .= '<div><span class="infospan INFO_hometown">';
                        $locHtml .= '<a href="';
                        $locHtml .= $this->view->url(array('module'=>'default','controller'=>'venues','action'=>'show','id'=>$act->locid),'default', true);
                        $locHtml .= '">'.$location->name.'</a>';
                        $locHtml .= '</span></div>';
                        $lGet = '67';
                    }
                }

                $link = $this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$photo->id,'show'=>'album'),'default', true);
                $html .= '<div style="height:'.$hght.'px;" class="act_img oneAIMG">';
                $html .= '<a class="n2s-phBox n2lbox.ajax" href="'.$link.'">';
                $html .= '<img src="'.$ph.'" alt=""/></a>';
                $users = new Community_Model_Users();
                $user = $users->getUser($act->actor);
                $html .= '<div class="dbLt mxhDbLt'.$lGet.'"><div class="ghtZ">'.$locHtml.$lHtml.'<a href="';
                $html .= $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'userphotos','id'=>$act->actor),'default', true);
                $html .= '">';
                $html .= sprintf($this->view->translate('More photos from %s'), '<b>'.$user->name.'</b>');
                $html .= '</a></div></div>';
                $html .= '<div class="jsd jsdT"></div><div class="jsd jsdB"></div>';
                $html .= '</div>';
            }
        } else {
            $show = FALSE;
        }
        return array('error'=>$error,'html'=>$html,'show'=>$show);
    }
    
    private function _albumsUploaded($act,$permis)
    {
        /*
        $albums = new Default_Model_PhotoAlbums();
        $album = $albums->getComAlbumInfo($act->cid);
        $permis = Community_Model_Permissions::getPermissions($event->creator);
        if ($event->permission > $permis)
         * 
         */
        $photos = new Default_Model_Photos();
        $error = FALSE;
        $html = '';
        $params = new N2S_Params();
        $param = $params->get($act->params);
        if(isset($param['photoid']) && isset($param['count']) && $param['count'] > 0){
            $phList = explode(',',$param['photoid']);
            $imgCount = 0;
            $rowHtml = '';
            $minH = NULL;
            $rowH = NULL;
            $rowW = 0;
            $count = 0;
            ($param['count'] > 4)?$maxCount = 4:$maxCount = $param['count'];
            $rowCount = 0;
            $rHght = 150;
            foreach ($phList as $ph){
                $photo = $photos->getPhotoID($ph);
                if(isset($photo) && $photo->albumid == $act->cid  && file_exists($photo->original)){
                    $imgCount++;
                    list($width, $height) = getimagesize($photo->original);
                    
                    if($rowH == NULL){
                        $rowH = $height;
                        $rowW = $width;
                    } else {
                        $sWidth = $width * $rowH / $height;
                        $rowW = $rowW + $sWidth;
                    }
                    
                    $linkArray = array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$photo->id);
                    if($param['count'] > 1){
                        $linkArray['show'] = 'album';
                        $linkArray['act'] = $act->id;
                    }
                    $link = $this->view->url($linkArray,'default', true);
                    $rowHtml .= '<a class="n2s-phBox n2lbox.ajax" href="'.$link.'">';
                    $rowHtml .= '<img src="'.$photo->original.'" alt=""/></a>';
                    
                    if($minH == NULL || ($minH =! NULL && $height < $minH))
                        $minH = $height;
                    
                    if($width > $height && $param['count'] > 3){
                        $count = $count + 3;
                    } else {
                        $count++;
                    }
                }
                if($count >= $maxCount){
                    $rHght = 568 * $rowH / $rowW;
                    $html .= '<div class="roit" style="height:'.floor($rHght).'px;">'.$rowHtml.'</div>';
                    $maxCount = $maxCount+2;
                    $rowCount++;
                    $onePHHtml = $rowHtml;
                    $rowHtml = '';
                    $minH = NULL;
                    $rowH = NULL;
                    $rowW = 0;
                    $count = 0;
                    if($rowCount == 3 || $rHght >= 370)
                        break;
                }
            }
            if($imgCount == 0){
                $error = TRUE;
            } elseif ($imgCount == 1) {
                if($rHght < 150){
                    $rHght = 150;
                } elseif ($rHght > 370) {
                    $rHght = 370;
                }
                $html = '<div style="height:'.$rHght.'px;" class="act_img oneAIMG mvdbLt">';
                $html .= $onePHHtml;
                $users = new Community_Model_Users();
                $user = $users->getUser($act->actor);
                $html .= '<div class="dbLt mxhDbLt"><div class="ghtZ"><a href="';
                $html .= $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'userphotos','id'=>$act->actor),'default', true);
                $html .= '">';
                $html .= sprintf($this->view->translate('More photos from %s'), '<b>'.$user->name.'</b>');
                $html .= '</a></div></div>';
                $html .= '<div class="jsd jsdT"></div><div class="jsd jsdB"></div>';
                $html .= '</div>';
            }
        } else {
            $error = TRUE;
        }
        return array('error'=>$error,'html'=>$html,'show'=>TRUE);
    }

    private function _profileAvatar($act,$permis)
    {
        $photos = new Default_Model_Photos();
        $photo = $photos->getPhotoID($act->cid);
        $error = FALSE;
        $show = TRUE;
        $html = '';
        if(isset($photo) && (file_exists($photo->original) || file_exists($photo->image))){
            if($photo->permissions > $permis){
                $show = FALSE;
            } else {
                if(file_exists($photo->original)){
                    $ph = $photo->original;
                    $width = $photo->width;
                    $height = $photo->height;
                } else {
                    $ph = $photo->image;
                    list($width, $height) = getimagesize($ph);
                }

                $hght = 370;
                if($height >= $width){
                    if($height < 370)
                        $hght = $height;
                } else {
                    if($width > 568){
                        $shght = 568 * $height / $width;
                        if($shght < 370)
                            $hght = $shght;
                    } else {
                        if($height < 370)
                            $hght = $height;
                    }
                }
                if($hght < 150)
                    $hght = 150;

                $link = $this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$photo->id),'default', true);
                $html .= '<div style="height:'.$hght.'px;" class="act_img oneAIMG mvdbLt">';
                $html .= '<a class="n2s-phBox n2lbox.ajax" href="'.$link.'">';
                $html .= '<img src="'.$ph.'" alt=""/></a>';
                $users = new Community_Model_Users();
                $user = $users->getUser($act->actor);
                $html .= '<div class="dbLt mxhDbLt"><div class="ghtZ"><a href="';
                $html .= $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'userphotos','id'=>$act->actor),'default', true);
                $html .= '">';
                $html .= sprintf($this->view->translate('More photos from %s'), '<b>'.$user->name.'</b>');
                $html .= '</a></div></div>';
                $html .= '<div class="jsd jsdT"></div><div class="jsd jsdB"></div>';
                $html .= '</div>';
            }
        } else {
            $error = TRUE;
        }
        return array('error'=>$error,'html'=>$html,'show'=>$show);
    }
    
    private function postcontent($act,$permis)
    {
        $suche = array();
        $ersetze = array();
        $error = FALSE;
        $show = TRUE;
        $title = $act->content;
        
        $params = new N2S_Params();
        $p = $params->get($act->params);
        
        if(strpos($title, '{video}') !== FALSE && $error == FALSE && $show == TRUE)
        {
            if(isset($p['video']) && strpos($title, '{cid}') !== FALSE){
                $videos = new Default_Model_Videos();
                $movie = $videos->getMovie($p['video']);
                if(!isset($movie)){
                    $error = TRUE;
                } else {
                    $oSearch = array('{video}','{/video}');
                    $title = str_ireplace($oSearch, '', $title);
                    $suche[] = '{cid}';
                    $eHtml = '<div class="vtitA">'.$movie->title.'</div>';
                    $eHtml .= '<div class="n2s-vidimg zoom_w">';
                    $eLink = '<a class="n2s-video n2lbox.iframe" href="http://www.youtube.com/embed/'.$movie->video_id.'?autoplay=1">';
                    $eHtml .= '<span class="timestamp">'.$eLink.$this->view->duration($movie->duration).'</a></span>';
                    $eHtml .= $eLink.'<img src="'.$movie->thumb.'" alt=""/>';
                    $eHtml .= '<i class="zoom_dec"></i>';
                    $eHtml .= '</a>';
                    $eHtml .= '</div>';
                    $ersetze[] = $eHtml;
                }
            } else {
                $error = TRUE;
            }
        }
        
        if(count($suche) > 0 && $error == FALSE && $show == TRUE){
            $title = str_ireplace($suche, $ersetze, $title);
            $title = '<div class="dsteb">'.$title.'</div>';
        }
        
        return array('error'=>$error,'html'=>$title,'show'=>$show);
    }

    private function _title($act,$permis)
    {
        $users = new Community_Model_Users();
        $title = $this->view->translate($act->title);
        
        $suche = array();
        $ersetze = array();
        $error = FALSE;
        $show = TRUE;
        
        if(strpos($title, '{actor}') !== FALSE && $error == FALSE && $show == TRUE)
        {
            $user = $users->getUser($act->actor);
            if(isset($user)){
                $suche[] = '{actor}';
                $html = '<a href="'.$this->view->userLink($act->actor).'">'.$user->name.'</a>';
                $ersetze[] = $html;
            } else {
                $error = TRUE;
            }
        }
        
        if(strpos($title, '{target}') !== FALSE && $error == FALSE && $show == TRUE)
        {
            $user = $users->getUser($act->target);
            if(isset($user)){
                $suche[] = '{target}';
                $html = '<a href="'.$this->view->userLink($act->target).'" ';
                $html .= 'class="n2s-tooltip" title="'.$this->view->toolTip($user->name,$user->userid);
                $html .= '">'.$user->name.'</a>';
                $ersetze[] = $html;
            } else {
                $error = TRUE;
            }
        }
        
        if(strpos($title, '{cid}') !== FALSE && $error == FALSE && $show == TRUE)
        {
            switch ($act->app){
                case 'albums':
                    $albums = new Default_Model_PhotoAlbums();
                    $result = $albums->getComAlbumInfo($act->cid);
                    if(isset($result)){
                        if($result->permissions > $permis){
                            $show = FALSE;
                        } else {
                            $link = $this->view->url(array("module"=>"default","controller"=>"photos","action"=>"useralbums","view"=>$act->cid,"id"=>$result->creator), 'default', true);
                            $tit = $result->name;
                        }
                    } else {
                        $error = TRUE;
                    }
                    break;
                case 'events':
                    $events = new Default_Model_Events();
                    $result = $events->getEvent($act->cid);
                    if(isset($result) && $result->published == 1){
                        if($result->permission > $permis){
                            $show = FALSE;
                        } else {
                            $link = $this->view->url(array("module"=>"default","controller"=>"events","action"=>"show","id"=>$act->cid), 'default', true);
                            $tit = $result->title;
                        }
                    } else {
                        $error = TRUE;
                    }
                    break;
                case 'venues':
                    $venues = new Default_Model_Adresses();
                    $result = $venues->getAdress($act->cid);
                    if(isset($result)){
                        $link = $this->view->url(array("module"=>"default","controller"=>"venues","action"=>"show","id"=>$act->cid), 'default', true);
                        $tit = $result->name;
                    } else {
                        $error = TRUE;
                    }
                    break;
            }
            
            if(isset($result) && $error == FALSE){
                if($show == TRUE){
                    $suche[] = '{cid}';
                    $html = '<a href="'.$link.'"><b>'.$tit.'</b></a>';
                    $ersetze[] = $html;
                }
            } else {
                $error = TRUE;
            }
        }
        
        if(strpos($title, '{multiple}') !== FALSE && $error == FALSE && $show == TRUE)
        {
            $params = new N2S_Params();
            $p = $params->get($act->params);
            if(isset($p['count'])){
                if($p['count'] > 1){
                    $title = preg_replace('/\{single\}(.*?)\{\/single\}/i', '', $title);
                    $regexp = '/\{multiple\}(.*?)\{\/multiple\}/i'; 
                    preg_match_all($regexp, $title, $ergebnis);
                    $ergebnis = $this->view->langHelper($ergebnis[1][0],$p['count']);
                    $title = preg_replace($regexp, $ergebnis, $title);
                    $title = str_ireplace('{count}', $p['count'], $title);
                } else {
                    $title = preg_replace('/\{multiple\}(.*?)\{\/multiple\}/i', '', $title);
                    $oSearch = array('{single}','{/single}');
                    $title = str_ireplace($oSearch, '', $title);
                }
            }
        }
        
        if(count($suche) > 0 && $error == FALSE && $show == TRUE)
            $title = str_ireplace($suche, $ersetze, $title);
        
        return array('error'=>$error,'html'=>$title,'show'=>$show);
    }
}
