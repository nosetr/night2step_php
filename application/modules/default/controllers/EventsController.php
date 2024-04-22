<?php

/**
 * EventsController.php
 * Description of EventsController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 26.10.2012 10:54:18
 * 
 */
class Default_EventsController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headMeta('index,follow','robots','name');
        if ($this->_helper->FlashMessenger->hasMessages()) {
            $this->view->flashmessage = $this->_helper->FlashMessenger->getMessages();
        }
    }
    
    public function indexAction()
    {
        $task = (string)$this->_request->getParam('task');
        $gSearch = (string)$this->_request->getParam('geosearch',null);
        $larray = array('module'=>'default','controller'=>'events','action'=>'index','geosearch'=>$gSearch);
        $aarray = array('show'=>'events','geosearch'=>$gSearch);
        if($task == 'archive'){
            $aarray['task']='archive';
            $textLink = $this->view->translate('Current events');
            $titel = $this->view->translate('Events (Archive)');
        } else {
            $html = $this->view->action('index','radar','default',array('show'=>'events','geosearch'=>$gSearch));
            $larray['task']='archive';
            $textLink = $this->view->translate('Past events');
            $titel = $this->view->translate('Events');
        }
        $html = $this->view->action('index','radar','default',$aarray);
        $link = $this->view->url($larray,'default', true);
        $this->view->headTitle($titel, 'PREPEND');
        $this->view->headMeta($titel,'og:title','property');
        $this->view->headMeta($this->view->translate('Find numerous events in your near! Try out our radar system!'),'og:description','property');
        $this->view->titel = $titel;
        $this->view->html = $html;
        $this->view->link ='<a href="'.$link.'" id="archiveLink">'.$textLink.' &gt;&gt;</a>';
    }
    
    public function myeventsAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity())
            $this->_helper->redirector('notfound', 'Error', 'default');
        $user = $auth->getIdentity()->userid;
        $html = $this->view->action('userevents','events','default',array('id'=>$user,'page'=>1));
        $this->view->html = $html;
    }
    
    public function createAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity() || !$this->_request->isXmlHttpRequest())
            $this->_helper->redirector('notfound','Error','default');
        $userID = $auth->getIdentity()->userid;
        $this->_helper->layout()->disableLayout();
        $form = new Default_Form_AlbumCreate();
        
        $form->setAction($this->view->url(array('module'=>'default','controller'=>'events','action'=>'create'),'default', true));
        $form->getElement('albname')->setRequired(TRUE)->setValidators(array(array('StringLength',FALSE,array(1,50))));
        if ($this->_request->isPost('albumForm') && $auth->hasIdentity())
        {
            if($form->isValid($_POST)){
                $data = $form->getValues();
                $events = new Default_Model_Events();
                if($auth->getIdentity()->type == 'venue'){
                    $adresses = new Default_Model_Adresses();
                    $adress = $adresses->getAdressWithCreator($userID);
                    $locid = $adress->id;
                } else {
                    $locid = 0;
                }
                $eventID = $events->setEvent($userID, $data['albname'], $data['albdescription'],$locid);
                $result = array('error'=>FALSE,'html'=>  $this->view->url(array('module'=>'default','controller'=>'events','action'=>'edit','id'=>$eventID),'default', true));
            } else {
                $result = array('error'=>TRUE,'html'=>(string)$form);
            }
            $this->_helper->json($result);
        } else {
            $html = '<div id="n2lbox-title" style="border-bottom: 1px solid rgb(187, 187, 187);"><h2 style="margin: 0px;">'.$this->view->translate('Create new event').'</h2></div>';
            $this->view->html = $html.'<div id=albformarray>'.$form.'</div>';
        }
    }

    public function usereventsAction()
    {
        $page = (int)$this->_request->getParam( 'page' , 1 );
        $user = (int)$this->_request->getParam('id',0);
        $task = (string)$this->_request->getParam('task',NULL);
        ($task == 'draft')?$publ = 0:$publ = 1;
        $profil = new Community_Model_Users();
        $userCheck = $profil->getUser($user);
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()){
            $curuser = $auth->getIdentity()->userid;
        } else {
            $curuser = 0;
        }
        if(!isset($userCheck) || ($task == 'draft' && $curuser != $user))
            $this->_helper->redirector('notfound', 'Error', 'default');
        if(isset($userCheck) && $userCheck->deactivated == 1)
            $this->_forward('removed', 'Error', 'default');
        
        $eventsMod = new Default_Model_Events();
        $permis = Community_Model_Permissions::getPermissions($user);
        $events = $eventsMod->getUserEvents($user,$publ,$permis);
        if(count($events)>0){
            $photos = new Default_Model_Photos();
            $adresses = new Default_Model_Adresses();
            $paginator = Zend_Paginator::factory($events);
            $paginator->setItemCountPerPage(10);
            $paginator->setCurrentPageNumber($page);
            
            $lineCount = 0;
            $next = $page+1;
            
            if(!$this->_request->isXmlHttpRequest()){
                ($task == 'draft')?$breadTitle = 'draft':$breadTitle = 'all';
                $html = $this->breadcrumbs($userCheck, $breadTitle, '', 0, $curuser);
                if($task == 'draft'){
                    (count($events) == 1)? $evcount = sprintf($this->view->translate('%d draft'),count($events)): $evcount = sprintf($this->view->langHelper('%d drafts',count($events)),count($events));
                }else{
                    (count($events) == 1)? $evcount = sprintf($this->view->translate('%d event'),count($events)): $evcount = sprintf($this->view->langHelper('%d events',count($events)),count($events));
                }
                $html .= '<div class="summary" style="margin:20px 10px;"><b>'.$evcount.'</b>';
                if($user == $curuser)
                    $html .= '<a class="red n2simg-button n2lbox.ajax ajaxlink right" style="margin-bottom:5px;" href="'.$this->view->url(array('module'=>'default','controller'=>'events','action'=>'create'),'default', true).'">'.$this->view->translate('Create new event').'</a>';
                $html .= '</div>';
                $html .= '<div id="uEvList" style="margin-right: 20px;border-bottom: 1px dotted rgb(153, 153, 153);"><div class="LPage" id="'.$next.'">';
            } else {
                $html = '<div class="LPage" id="'.$next.'">';
            }
            foreach ($paginator as $event){
                $lineCount++;
                if($lineCount == 1)
                    $html .= '<div class="EvLine" style="border-top: 1px dotted rgb(153, 153, 153);">';
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
                $Time = new Zend_Date($event->start);
                $location = $adresses->getAdress($event->locid);
                $html .= '<div class="LBox" style="min-height: 125px;float: left;';
                if($lineCount == 1)
                    $html .= 'border-right: 1px dotted rgb(153, 153, 153);';
                $html .= 'padding: 5px; width: 48%;overflow: hidden;">';
                $now = Zend_Date::now();
                $endTime = new Zend_Date($event->end);
                if(($endTime->isEarlier($now) && $event->published != 2) || $event->published == 0)
                {
                    ($event->published == 0)?$artText = 'DRAFT':$artText = 'ARCHIVE';
                    $html .= '<div class="artOF">'.$this->view->translate($artText).'</div>';                    
                } elseif($event->published == 2) {
                    $html .= '<div class="artOF" style="color: whitesmoke;background:none repeat scroll 0 0 silver;">'.$this->view->translate('Canceled').'</div>';
                }
                $html .= '<div  style="margin-right: 15px;width:100px;height:100px;float:left;">';
                $html .= '<a href="'.$link.'">';
                $html .= '<img  style="width:100px;height:100px;" src="'.$evimg.'" alt=""/>';
                $html .= '</a>';
                $html .= '<div style="text-align: center; color: rgb(255, 255, 255); font-weight: bold; background-color: rgb(51, 51, 51);">'.$Time->get(Zend_Date::TIME_SHORT).'</div></div>';
                $html .= '<div style="margin-left:115px;"><h3><a class="black" href="'.$link.'">'.
                        $this->view->shortText($title,200).'</a></h3>';
                if($location){
                    $formAdress = $location->address;
                    $loclink = $this->view->url(array('module'=>'default',
                                            'controller'=>'venues','action'=>'show',
                                            'id'=>$location->id),
                                            'default', true);
                    $html .= '<div><b>'.$this->view->translate('Start').':</b> '.$Time->get(Zend_Date::DATE_LONG).'<br /><a href="'.$loclink.'">@ '.$this->view->shortText($location->name,40).'</a></div>';
                    $html .= '<div class="TSmal">'.$formAdress.'</div>';
                }
                if($event->gastlist == 1)
                    $html .= $this->view->guestList($event->id);
                $html .= '</div>';
                if($user == $curuser){
                    $pageN = $this->view->navigation()->findOneBy('label', 'My Events');
                    if($pageN){$pageN->setActive(TRUE);}
                    $html .= '<a class="alb_edit" href="'.$this->view->url(array('module'=>'default','controller'=>'events','action'=>'edit','id'=>$event->id),'default', true).'" ';
                    $html .= 'title="'.$this->view->translate('edit').'"></a>';
                }
                $html .= '</div>';
                if($lineCount == 2){
                    $html .= '<div class="clear"></div></div>';
                    $lineCount = 0;
                }
            }
            if($lineCount == 1)
                $html .= '<div class="clear"></div>';
            $html .= '</div>';

            if(!$this->_request->isXmlHttpRequest()){
                $html .= '</div>';
                $html .= '<div id="ajaxload" style="margin: 10px; display: none;"><img src="/images/ajax/ajax-loader1.gif" alt=""/></div>';
                $html .= '<script  type="text/javascript">var bSuppressScroll=true;$(window).scroll(function(){var page=$(".LPage:last").attr("id");if(($(window).scrollTop() >= $("body").height() - $(window).height()-600) && page<='.count($paginator).')events.goToByScroll('.$user.',page,"'.$task.'");});</script>';
                $this->view->html = $html;
                $this->view->jQuery()->addJavascriptFile('/js/n2s.events.js');
            } else {
                if(count($paginator)>0){
                    $result = array('error'=>false,'html'=>$html);
                } else {
                    $result = array('error'=>TRUE);
                }
                $this->_helper->json($result);
            }
        } else {
            if(!$this->_request->isXmlHttpRequest()){
                ($task == 'draft')?$breadTitle = 'draft':$breadTitle = 'all';
                $html = $this->breadcrumbs($userCheck, $breadTitle, '', 0, $curuser);
                $evcount = sprintf($this->view->langHelper('%d events',0),0);
                $html .= '<div class="summary" style="margin:20px 10px;"><b>'.$evcount.'</b>';
                if($user == $curuser)
                    $html .= '<a class="red n2simg-button n2lbox.ajax" href="'.$this->view->url(array('module'=>'default','controller'=>'events','action'=>'create'),'default', true).'"><div class="ajaxlink" style="float:right;margin-bottom:5px;">'.$this->view->translate('Create new event').'</div></a>';
                $html .= '</div>';
                $html .= '<div class="summary" style="margin: 10px;"><b>';
                if($user == $curuser){
                    $html .= $this->view->translate('You have no events.');
                } else {
                    $html .= $this->view->translate('This user has no events.');
                }
                $html .= '</b></div>';
                $this->view->html = $html;
            }
        }
        ($curuser != $user)?$headText = $userCheck->name:$headText = $this->view->translate('My Events');
        $this->view->headTitle($this->view->translate('Events').' - '.$headText, 'PREPEND');
    }
    
    public function showrelevantAction()
    {
        $id = (int)$this->_request->getParam('id',null);
        $start = (int)$this->_request->getParam('start',0);
        $end = (int)$this->_request->getParam('end',0);
        $task = (string)$this->_request->getParam('task');
        $cid = (int)$this->_request->getParam('cid',0);
        
        if($id == NULL)
            $this->_helper->redirector('notfound', 'Error', 'default');
        
        $arLink = array('show'=>'events','id'=>$id,'start'=>$start,'end'=>$end);
        
        if(isset($task) && $task == 'album' && $cid > 0){
            $events = new Default_Model_PhotoAlbums();
            $event = $events->getComAlbumInfo($cid);
            if(isset($event)){
                $venLink = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'useralbums','view'=>$cid,'id'=>$event->creator),'default', true);
                $title = sprintf($this->view->translate('Relevant events to album %s'),'<a href="'.$venLink.'">'.$event->name.'</a>');
                $arLink['title'] = $title;
            }
        }
        
        $html = $this->view->action('relevant','radar','default',$arLink);
        $this->view->html = $html;
    }

    public function showdayAction()
    {
        $gSearch = (string)$this->_request->getParam('geosearch',null);
        $task = $this->_request->getParam('data');
        $Datum = new Zend_Date($task);
        $link = $this->view->url(array('module'=>'default','controller'=>'events','action'=>'index','geosearch'=>$gSearch),'default', true);
        $html = '<a id="archiveLink" style="float:left;" href="'.$link.'">&lt;&lt; '.$this->view->translate('All events').'</a>';
        $html .= '<h1 style="text-align: right; margin-right: 10px;">'.$Datum->get(Zend_Date::DATE_FULL).'</h1>';
        $html .= '<div class="clear"></div>';
        $html .= $this->view->action('list','radar','default',array('show'=>'events','time'=>$Datum->get(Zend_Date::TIMESTAMP),'geosearch'=>$gSearch));
        $this->view->html = $html;
        $this->view->headTitle($this->view->translate('Events').' - '.$Datum->get(Zend_Date::DATE_FULL), 'PREPEND');
    }
    
    public function windowAction()
    {
        if($this->_request->isXmlHttpRequest()){
            $auth = Zend_Auth::getInstance();
            $id = (int)$this->_request->getParam('id',0);
            $task = (string)$this->_request->getParam('task');
            switch ($task){
                case 'delete':
                    $pub = 0;
                    break;
                case 'cancel':
                    $pub = 1;
                    break;
                default :
                    $pub = 2;
            }
            $events = new Default_Model_Events();
            $event = $events->getEvent($id);
            if ($auth->hasIdentity() && $event && $event->creator == $auth->getIdentity()->userid && $event->published == $pub && $pub != 2) {
                $this->_helper->layout()->disableLayout();
                $html = '<div id="evTUF" ';
                if($task == 'delete'){
                    $html .= 'style="width: 100%; text-align: center; margin: 15% 0px;">';
                    $html .= '<div style="margin-bottom: 10px;">'.$this->view->translate('Du you realy want to delete this draft?').'</div>';
                    $send = 'Delete draft';
                } elseif($task == 'cancel') {
                    $Time2 = new Zend_Date($event->end);
                    $now = Zend_Date::now();
                    if($now->isEarlier($Time2)) {
                        $send = 'Cancel event';
                        $html .= '><textarea id="messText" cols="1" rows="1" ';
                        $html .= 'style="margin-bottom: 4px; width: 95%; height: 150px;" ';
                        $html .= 'name="comment" placeholder="'.$this->view->translate('Grund der Absage...').'"></textarea>';
                    } else {
                        $this->_helper->redirector('notfound', 'Error', 'default');
                    }
                }
                
                $onclick = '$(function () {';
                $onclick .= '$("#im_send_button").click(function(){';
                $onclick .= 'var txt = $("#messText").val();';
                $onclick .= '$("#ajaxload").show();';
                $onclick .= '$("#evTUF").hide();';
                $onclick .= '$.getJSON("/events/ajax/act/delete",{id: '.$id.',msg: txt},';
                $onclick .= 'function(data){';
                $onclick .= 'if(data.error){';
                $onclick .= '$("#ajaxload").hide();';
                $onclick .= '$("#evTUF").empty().show().append(data.message);';
                $onclick .= '}else{';
                $onclick .= 'window.location.replace("/events/myevents");';
                $onclick .= '}});});});';
                
                $html .= '<button id="im_send_button" style="font-size: inherit; font-weight: bold; padding: 2px 10px;">'.$this->view->translate($send).'</button>';
                $html .= '<button onclick="parent.$.n2lbox.close();" style="font-size: inherit; font-weight: bold;margin-left:15px; padding: 2px 10px;">'.$this->view->translate('Cancel').'</button>';
                $html .= '</div>';
                    
                $this->view->html = $html;
                $this->view->to = $onclick;
            } else {
                $this->_helper->redirector('notfound', 'Error', 'default');
            }
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }
    }
    
    public function ajaxAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            die($this->_helper->json(array('error'=>true,'message'=>'Error')));
        } else {
            $user = $auth->getIdentity()->userid;
            $act = (string)$this->_request->getParam('act');
            if($act == "posit" && $this->_request->isXmlHttpRequest()) //Position ändern
            {
                $top = (int)$this->_request->getParam('top',0);
                $target = (int)$this->_request->getParam('target',0);
                $storage = (string)$this->_request->getParam('for');
                if($storage == 'venue'){
                    $venues = new Default_Model_Adresses();
                    $venue = $venues->getAdress($target);
                    if(isset($venue)){
                        $checkAdmin = new Community_Model_Admins();
                        $curUserID = $checkAdmin->getCuruser($venue->creator, 'venue');
                        $user = $curUserID;
                    }
                }
                $banners = new Default_Model_Background();
                $banner = $banners->getImg($target, $storage);
                if(!isset($banner) || $banner->creator != $user){
                    die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                } else {
                    $banners->updatePosit($banner->id, $user, $top);
                    $result = array('error'=>FALSE,'message'=>  $this->view->translate('Position updated'));
                    $this->_helper->json($result);
                }
            } elseif($act == "glemail" && $this->_request->isXmlHttpRequest()) {
                $eventID = (int)$this->_request->getParam('id',0);
                $events = new Default_Model_Events();
                $event = $events->getEvent($eventID);
                if($user == $event->creator && isset($event)){
                    $send = (int)$this->_request->getParam('send',1);
                    if($send == 0 || $send == 1){
                        $data = array('glistSendEMail'=>$send);
                        $events->updateEvent($eventID, $data);
                    }
                }
                //$.post("/events/ajax/act/glemail",{send:window.semglist});
            } elseif($act == "delete" && $this->_request->isXmlHttpRequest()) {
                $eventID = (int)$this->_request->getParam('id',0);
                $events = new Default_Model_Events();
                $event = $events->getEvent($eventID);
                if($user == $event->creator && isset($event) && $event->published < 2){
                    if($event->published == 1){
                        $End = new Zend_Date($event->end);
                        $now = Zend_Date::now();
                        if($now->isEarlier($End)){
                            $msg = trim((string)$this->_request->getParam('msg'));
                            if($msg == '')
                                $msg = NULL;
                            $ajaxModel = new Default_Model_Ajaxlist();
                            $ajaxModel->delList($eventID, 'event');
                            $data = array('published'=>2,'cancelreason'=>$msg);
                            $events->updateEvent($eventID, $data);
                            $result = array('error'=>FALSE);
                            $this->_helper->json($result);
                        } else {
                            die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                        }
                    } else {
                        $banners = new Default_Model_Background();
                        $banners->delImg($eventID, 'event');
                        $events->delEventsDraft($user, $eventID);
                        $result = array('error'=>FALSE);
                        $this->_helper->json($result);
                    }
                } else {
                    die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                }
            } elseif($act == "glist" && $this->_request->isXmlHttpRequest()) {
                $eventID = (int)$this->_request->getParam('id',0);
                $task = (string)$this->_request->getParam('task');
                $events = new Default_Model_Events();
                $event = $events->getEvent($eventID);
                $Start = new Zend_Date($event->glistStartDate);
                $End = new Zend_Date($event->glistEndDate);
                $now = Zend_Date::now();
                if($user != $event->creator && isset($event) && $now->isEarlier($End) && $Start->isEarlier($now) && $event->published == 1){
                    $glist = new Default_Model_EventsGlist();
                    $gcount = count($glist->getMembers($eventID));
                    if($task == 'set'){
                        if($event->gastlistcount == 0 || $gcount < $event->gastlistcount){
                            $member = $glist->setMember($eventID, $user);
                            $result = array('error'=>$member['error'],'message'=>  $this->view->translate($member['message']));
                        } else {
                            die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                        }
                    } elseif($task == 'del') {
                        $member = $glist->delMember($eventID, $user);
                        $result = array('error'=>$member['error'],'message'=>  $member['message']);
                    } else {
                        die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                    }
                    $this->_helper->json($result);
                } else {
                    die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                }
            } elseif(($act == "join" || $act == "maybe" || $act == "deljoin") && $this->_request->isXmlHttpRequest()){
                $eventID = (int)$this->_request->getParam('id',0);
                $events = new Default_Model_Events();
                $event = $events->getEvent($eventID);
                $Time = new Zend_Date($event->end);
                $now = Zend_Date::now();
                if(isset($event) && $now->isEarlier($Time) && $event->published == 1){
                    $members = new Default_Model_EventsMembers();
                    if($act == "join" || $act == "maybe" ){
                        ($act == "join")?$status='1':$status='0';
                        $member = $members->setMember($eventID, $status, $user);
                    } else {
                        $member = $members->delMember($eventID, $user);
                    }
                    $membCount = count($members->getMembers($eventID));
                    ($act == "deljoin")?$mte = $member['message']:$mte = $this->view->translate($member['message']);
                    $result = array('error'=>$member['error'],'message'=>  $mte,'count'=>$membCount);
                    $this->_helper->json($result);
                } else {
                    die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                }
            } elseif(($act == "upbanner" || $act == "avatar") && !$this->_request->isXmlHttpRequest()) {  //Bannerphoto upload
                if ($this->_request->isPost())
                {
                    $profilAlbum = FALSE;
                    $eventID = (int)$this->_request->getPost('id',0);
                    $storage = (string)$this->_request->getPost('for');
                    if($storage == 'event'){
                        $events = new Default_Model_Events();
                        $event = $events->getEvent($eventID);
                        $creator = $event->creator;
                        $checkUser = $user;
                    } elseif ($storage == 'venue') {
                        if($auth->getIdentity()->type == 'venue')
                            $profilAlbum = TRUE;
                        $events = new Default_Model_Adresses();
                        $event = $events->getAdress($eventID);
                        $creator = $event->creator;
                        $checkAdmin = new Community_Model_Admins();
                        $checkUser = $checkAdmin->getCuruser($event->creator, 'venue');
                    } elseif ($storage == 'profil') {
                        $profilAlbum = TRUE;
                        $events = new Community_Model_Users();
                        $event = $events->getUser($eventID);
                        $creator = $event->userid;
                        $checkUser = $user;
                    }
                    if(isset($event) && $creator == $checkUser){
                        $albums = new Default_Model_PhotoAlbums();
                        if($profilAlbum == TRUE){
                            $album = $albums->getComProfilImgAlbum($user);
                        } else {
                            $album = $albums->getComEventsImgAlbum($user);
                        }
                        if(!isset($album)){
                            if($profilAlbum == TRUE){
                                $albID = $albums->setComProfilImgAlbum($user);
                            } else {
                                $albID = $albums->setComEventsImgAlbum($user);
                            }
                        } else {
                            $albID = $album->id;
                        }
                        //$photo = $this->_uploadPhoto($albID);
                        
                        $upload = new N2S_Photo_Upload(array('albumID'=>$albID));
                        $photo = $upload->upload();
                        
                        $photo['error'] = FALSE;
                        
                        $photos = new Default_Model_Photos();
                        $image = $photos->setPhoto($user,$albID,$photo['img'],$photo['thumb'],$photo['orig'],$photo['title'],$photo['width'],$photo['height'],1);

                        if($act == "upbanner"){
                            $banners = new Default_Model_Background();
                            $banners->setImg($user,$storage, $eventID, $image);

                            list($width, $height) = getimagesize($photo['orig']);
                            $p = 740/$width;
                            $h = $height*$p;

                            $result = array('error'=>FALSE,'img'=>$photo['orig'],'height'=>$h);
                        }elseif($act == "avatar"){
                            if($storage == 'event'){
                                $events->eventImgUpdate($eventID, $image);
                            } elseif ($storage == 'venue') {
                                $users = new Community_Model_Users();
                                $users->updateAvatar($creator, $image);
                                $events->adressImgUpdate($eventID, $image);
                            } elseif ($storage == 'profil') {
                                $events->updateAvatar($eventID, $image);
                            }
                            $html = '<div id="avSn2s">';
                            $html .= '<a class="n2s-phBox n2lbox.ajax" href="';
                            $html .= $this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$image),'default', true);
                            $html .= '">';
                            $html .= '<img id="n2simg-avat_surround" src="'.$photo['img'].'" alt=""/>';
                            $html .= '</a></div>';
                            $result = array('error'=>FALSE,'html'=>$html);
                        }
                        $this->_helper->json($result);
                    } else {
                        die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                    }
                }
            } else {
                die($this->_helper->json(array('error'=>true,'message'=>'Error')));
            }
        }
    }

    public function showAction()
    {
        $this->view->headLink()->appendStylesheet('/css/events.css');
        $this->view->jQuery()->addJavascriptFile('/js/n2s.events.js');
        
        $id = (int)$this->_request->getParam('id');
        $public =  $this->_request->getParam('pub',FALSE);
        $events = new Default_Model_Events();
        $event = $events->getEvent($id);
        if (!isset($event))
            $this->_helper->redirector('notfound', 'Error', 'default');
        $permis = Community_Model_Permissions::getPermissions($event->creator);
        if ($event->permission > $permis)
            $this->_helper->redirector('notfound', 'Error', 'default');
        $showAdminPanel = FALSE;
        $curuser = 0;
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $curuser = $auth->getIdentity()->userid;
            if($event->creator == $curuser)
                $showAdminPanel = TRUE;
            if($public == TRUE && $this->_request->isXmlHttpRequest())
                $curuser = 0;
        }
        
        $adresses = new Default_Model_Adresses();
        $location = $adresses->getAdress($event->locid);
        $photos = new Default_Model_Photos();
        $photo = $photos->getPhotoID($event->photoid);
        if ($photo && file_exists($photo->image)){
            $evimg = $photo->image;
            $thumbexist = 1;
            $permis = Community_Model_Permissions::getPermissions($photo->creator);
        } else {
            $evimg = $this->view->baseUrl().'images/no-photo.png';
            $thumbexist = 0;
        }
        
        if($thumbexist > 0 && $photo->permissions > $permis)
            $thumbexist = 0;
        
        $thumb = '<div id="n2s-useravatar" style="z-index:2;box-shadow:-1px 2px 3px rgba(0, 0, 0, 0.3);border: 1px solid rgb(153, 153, 153); width: 238px; margin-bottom: 10px; min-height: 225px; background-color: rgb(255, 255, 255);">';
        $thumb .= '<div id="avSn2s">';
        if($thumbexist > 0){
            $thumb .= '<a class="n2s-phBox n2lbox.ajax" href="';
            $thumb .= $this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$event->photoid),'default', true);
            $thumb .= '">';
        }
        $thumb .= '<img id="n2simg-avat_surround" itemprop="image" ';
        $thumb .= 'src="'.$evimg.'" alt=""/>';
        if($thumbexist > 0)
            $thumb .= '</a>';
        $thumb .= '</div>';
        if( $curuser == $event->creator)
            $thumb .= '<a class="n2s-transpb n2s-imgup n2lbox.ajax" style="padding: 10px 0px; top: 1px; left: 0px; width: 240px; text-align: center;" href="/ajax/imgup/task/avatarchange/target/avatar">'.$this->view->translate('Change avatar').'</a>';
        $thumb .= '</div>';
        
        $html = '<div id="editFont"></div>';
        $owner = FALSE;
        if($event->published == 2){
            $this->view->cancel = '<div style="background-color: rgb(68, 68, 68); border: 2px solid rgb(204, 0, 0); color: rgb(255, 255, 255); margin-bottom: 10px; padding: 10px; box-shadow: 0px 2px 3px rgb(0, 0, 0);"><h3 style="text-align:center;">';
            $this->view->cancel .= $this->view->translate('Event is canceled!').'</h3>';
            if($event->cancelreason != NULL)
                $this->view->cancel .= '<div>'.$this->view->urlReplace($this->view->escape($event->cancelreason)).'</div>';
            $this->view->cancel .= '</div>';
        } else {
            $this->view->cancel = '';
        }
        if( $curuser == $event->creator){
            $owner = TRUE;
            
            $Time2 = new Zend_Date($event->end);
            $now = Zend_Date::now();
        } else {
            $hits = $event->hits + 1;
            $data = array('hits'=>$hits);
            $events->updateEvent($id, $data);
        }
        $startDt = new Zend_Date($event->start);
        $this->view->mTitle = '<div class="wwRt"><div class="blogDate">'.$startDt->get(Zend_Date::DAY).'<span>'.$startDt->get(Zend_Date::MONTH_NAME_SHORT).'</span></div>';
        $this->view->mTitle .= '<div class="wwRtD"><h1';
        $this->view->mTitle .= ' id="_name1" itemprop="name">'.$event->title.'</h1>';
        if($event->published == 0)
            $this->view->mTitle .= ' ('.$this->view->translate('Draft').')';
        $headTitle = $event->title;
        $this->view->mTitle .= '<div class="evDtS">';
        $this->view->mTitle .= '<span id="tmStrtWkd">'.$startDt->get(Zend_Date::WEEKDAY).'</span>';
        $this->view->mTitle .= ' • <span itemprop="startDate" content="'.$startDt->get(Zend_Date::ISO_8601).'" id="tmStrt">'.$startDt->get(Zend_Date::DATETIME_SHORT).'</span>';
        if($location){
            $this->view->mTitle .= ' • <span itemprop="name" id="lcNm">'.$location->name.'</span>';
            $this->view->mTitle .= ' • '.$this->view->addressSchemaHtml($location->address);
        }
        $this->view->mTitle .= '</div></div></div>';
        
        $backGrBanner = $this->view->userBanner('event',$id,$owner);
        if($location && $backGrBanner['map']==FALSE){
            $headTitle = $headTitle.' @ '.$location->name;
            if($this->_request->isXmlHttpRequest()){
                $map = '<iframe class="n2lbox-iframe" scrolling="no" frameborder="0" src="/events/map/static/1/id/'.$event->locid.'" hspace="0"></iframe>';
            } else {
                $map = $this->view->simpleMaps($event->locid);
            }
        } else { $map = ''; }
        
        $bgPos = 705;
        $dvPos = 675;

        ($curuser != $event->creator)?$pOwner = FALSE:$pOwner = TRUE;
        if(isset($location)){
            $panel = $this->view->showPanel($location->creator,'event',$pOwner,$location->id,$event->start,$event->end,$event->id);
            $bgPos = $panel['width']+30;
            $dvPos = $panel['width'];
        }
        
        $html = '<div style="background: url(/images/bg-line2.png) repeat-y scroll '.$bgPos.'px 0px rgb(250, 250, 250);">';
        $html .= '<div style="width:'.$dvPos.'px;margin:10px 15px;float:left;">';
        if($event->start > 0){
            $Time = new Zend_Date($event->start);
            $t1 = $Time->toString('yyyy-MM-dd');
            $Time2 = new Zend_Date($event->end);
            $t2 = $Time2->toString('yyyy-MM-dd');
            $html .= '<div style="float: left;"><ul style="margin: 0px;">';
            $now = Zend_Date::now();
            if($Time2->isEarlier($now) && $event->published == 1)
            {
                $html .= '<li><b><span style="background-color: rgb(204, 0, 0); color: rgb(255, 255, 255); padding: 0px 5px; cursor: default;">'.$this->view->translate('Event is expired').'</span></b></li>';                    
            }
            $html .= '<li><span class="n2s-tooltip infospan INFO_hits" title="'.$this->view->translate('hits').'">'.$event->hits.'</span></li>';
            $html .= '<li>';
            $html .= '<div class="n2s-tooltip infospan INFO_date" title="'.$this->view->translate('Time').'">';
            if ($t1 == $t2){
                $html .= '<div>'.$Time->get(Zend_Date::DATE_FULL).'</div>';
                $html .= $Time->toString('HH:mm').' - '.$Time2->toString('HH:mm');
            } else {
                $html .= '<div>'.$Time->get(Zend_Date::DATE_FULL);
                $html .= ' '.$Time->toString('HH:mm').'<br/>';
                $html .= '- '.$Time2->get(Zend_Date::DATE_FULL).' '.$Time2->toString('HH:mm').'</div>';
            }
            $html .= '</div>';
            $html .= '</li>';
            if(isset($location)){
                $html .= '<li>';
                $html .= '<span class="n2s-tooltip infospan INFO_hometown" title="'.$this->view->translate('location').'">';
                $html .= '<a href="';
                $html .= $this->view->url(array('module'=>'default','controller'=>'venues','action'=>'show','id'=>$event->locid),'default', true);
                $html .= '">'.$location->name.'</a>';
                $html .= '</span>';
                $html .= '</li>';
            }
            $html .= '</ul></div>';
            if($event->published == 1){
                $members = new Default_Model_EventsMembers();
                
                $joinM = $members->getJoinMembers($id);
                $join = count($joinM);
                
                $maybeM = $members->getMaybeMembers($id);
                $maybe = count($maybeM);
                
                $summe = $join + $maybe;
                
                $joinFrCount = '';
                $maybeFrCount = '';
                $joinFr = 0;
                $maybeFr = 0;
                if($curuser > 0){
                    $friends = new Community_Model_FrRequest();
                    //Join
                    if($join > 0){
                        $joinList = array();
                        foreach ($joinM as $j){
                            $joinList[] = $j->memberid;
                        }
                        $joinFr = count($friends->getCheckInList($curuser, $joinList));
                        if ($joinFr == 1){
                            $joinFrCount = sprintf($this->view->translate('%d friend'), $joinFr);
                        } else {
                            $joinFrCount = sprintf($this->view->langHelper('%d friends',$joinFr), $joinFr);
                        }
                    }                    
                    //Maybe
                    if($maybe > 0){
                        $maybeList = array();
                        foreach ($maybeM as $m){
                            $maybeList[] = $m->memberid;
                        }
                        $maybeFr = count($friends->getCheckInList($curuser, $maybeList));
                        if ($maybeFr == 1){
                            $maybeFrCount = sprintf($this->view->translate('%d friend'), $maybeFr);
                        } else {
                            $maybeFrCount = sprintf($this->view->langHelper('%d friends',$maybeFr), $maybeFr);
                        }
                    }
                }
                $html .= '<div id="n2s-evJoin">';
                $html .= '<div id="n2s-evJoin-count">'.$summe.'</div>';
                if($summe > 0){
                    $html .= '<div id="n2s-evJoin-list-array">';
                    $html .= '<div id="n2s-evJoin-list"><div id="notifArrow"></div><div>';
                    $html .= '<div class="n2s-evJoin-nList"><span>';
                    if($join > 0){
                        $html .= '<a class="n2s-userlist n2lbox.ajax" href="';
                        $html .= $this->view->url(array('module'=>'default','controller'=>'events','action'=>'userlist','id'=>$id,'task'=>'going'),'default', true);
                        $html .= '">';
                    }
                    $html .= $this->view->translate('Going').' ('.$join.')';
                    if($join > 0)
                        $html .= '</a>';
                    if($joinFr > 0)
                        $html .= '<strong style="font-size: 9px; margin-left: 5px;">'.$joinFrCount.'</strong>';
                    $html .= '</span>';
                    if($join > 0 && $curuser > 0)
                        $html .= '<div style="padding: 15px 10px 0;">'.$this->view->userThumbList($joinList, 5).'</div>';
                    $html .= '</div></div><div>';
                    $html .= '<div class="n2s-evJoin-nList"><span>';
                    if($maybe > 0){
                        $html .= '<a class="n2s-userlist n2lbox.ajax" href="';
                        $html .= $this->view->url(array('module'=>'default','controller'=>'events','action'=>'userlist','id'=>$id,'task'=>'maybe'),'default', true);
                        $html .= '">';
                    }
                    $html .= $this->view->translate('Maybe').' ('.$maybe.')';
                    if($maybe > 0)
                        $html .= '</a>';
                    if($maybeFr > 0)
                        $html .= '<strong style="font-size: 9px; margin-left: 5px;"> '.$maybeFrCount.'</strong>';
                    $html .= '</span>';
                    if($maybe > 0)
                        $html .= '<div style="padding: 15px 10px 0;">'.$this->view->userThumbList($maybeList, 5).'</div>';
                    $html .= '</div></div></div></div>';
                }
                $html .= '</div>';
                $html .= '<div style="float: left;"><ul style="margin: 0px;">';
                if($auth->hasIdentity() && $curuser != $event->creator)
                    $html .= '<li style="margin-top: 5px;">'.$this->view->messager($event->creator).'</li>';
                $html .= '</ul></div>';
            }
        }
        $html .= '</div>';
        if(isset($location))
            $html .= $panel['html'];
        $html .= '<div class="clear"></div></div>';
        $html .= '<div class="clear"></div>';
        if($event->gastlist == 1 && $event->published == 1){
            $GLStart = new Zend_Date($event->glistStartDate);
            $GLEnd = new Zend_Date($event->glistEndDate);
            $glist = new Default_Model_EventsGlist();
            $gcount = count($glist->getMembers($id));
            $html .= '<div id="EventGList">';
            if($owner == TRUE || (($event->gastlistcount == 0 || $gcount < $event->gastlistcount) && $now->isEarlier($GLEnd) && $GLStart->isEarlier($now)))
                $html .= '<div id="EventGList-desc">'.$this->view->urlReplace($this->view->escape($event->glistdescription)).'</div>';
            
            $html .= '<div id="EventGList-buttons">';
            if($owner == FALSE &&
                    ($event->gastlistcount == 0 || $gcount < $event->gastlistcount) &&
                    $now->isEarlier($GLEnd) && $GLStart->isEarlier($now)){
                if($auth->hasIdentity() && $curuser > 0){
                    $GLCheck = count($glist->checkMember($id, $curuser));
                    $html .= '<div id="EventGList-loading"><img src="images/ajax/ajax-loader1.gif" alt=""/></div>';
                    $html .= '<div id="EventGList-text"';
                    if($GLCheck == 0)
                        $html .= ' style="display:none;"';
                    $html .= '><b>';
                    if($GLCheck > 0)
                        $html .= $this->view->translate('You\'re on the guest list');
                    $html .= '</b><span>';
                    $html .= '<a onclick="javascript:events.glist('.$id.',\'del\');" href="javascript:void(0);"><img id="resetLocButton" class="n2s-tooltip" title="'.$this->view->translate('Click here to reset').'" style="" src="/images/reset.png" alt=""/></a></span></div>';
                    $html .= '<a class="n2s-GList-button"';
                    if($GLCheck > 0)
                        $html .= ' style="display:none;"';
                    $html .= ' onclick="javascript:events.glist('.$id.',\'set\');" href="javascript:void(0);">'.$this->view->translate('write you on the guest list').'</a>';
                } else {
                    
                        $html .= '<div id="archiveLink" class="n2s-tooltip" title="'.$this->view->translate('Log in for guest list registration').'" style="cursor: default;color: #999999;font-weight: bold;margin: 3px 0px; float: left; background: url(/images/glist2.png) no-repeat scroll 0 0 transparent; padding: 0 10px 0 21px;">';
                        $html .= $this->view->translate('write you on the guest list');
                        $html .= '</div>';
                }
            } elseif ($owner == TRUE) {
                $html .= '<div style="margin-top:7px;">';
                $html .= '<b><a href="';
                $html .= $this->view->url(array('module'=>'default',
                                        'controller'=>'events','action'=>'glist','id'=>$id),
                                        'default', true);
                $html .= '">'.$this->view->translate('Guest list count').':</a> '.$gcount;
                if($event->gastlistcount > 0)
                    $html .= ' '.$this->view->translate('from').' '.$event->gastlistcount;
                $html .= '</b>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= '<div class="clear"></div></div>';
        }
        $html .= '<div id="mapTest" style="margin: 15px 0;">'.$map.'</div>';
        
        $html2 = '';
        if($event->description)
            $html2 .= '<div><b>'.$this->view->translate('Description').':</b><br/><span id="_description11" itemprop="description">'.$this->view->urlReplace($this->view->escape($event->description),TRUE).'</span></div>';
        if($event->specials)
            $html2 .= '<div style="margin-top:15px;"><b>'.$this->view->translate('Specials').':</b><br/>'.$this->view->urlReplace($this->view->escape($event->specials),TRUE).'</div>';
        if($event->published == 1 && $event->permission == 0)
            $html2 .= $this->view->socButtons();
        $html2 .= '<input type="hidden" value="event" name="active-show"/>
            <input type="hidden" value="'.$id.'" name="active-id"/>';
        
        $profil = new Community_Model_Users();
        $creator = $profil->getUser($event->creator);
        if($this->_request->isXmlHttpRequest()){
            $this->view->breadcrumbs = '';
        } else {
            $this->view->breadcrumbs = '<div style="margin-bottom: 20px;">'.$this->breadcrumbs($creator, 'album', $event->title, $id, $curuser).'</div>';
        }
        $this->view->userbanner = $backGrBanner['html'];
        $this->view->html = $html;
        $this->view->html2 = $html2;
        $this->view->thumb = $thumb;
        $this->view->activity = $this->view->action('index','activities','default',array('task'=>'event','cid'=>$id));
        //$this->view->memberslist = $this->view->eventsmembers($id,'event');
        $this->view->memberslist = '';
        $this->view->jQuery()->addJavascriptFile('/js/n2s.comment.js');
        ($event->published == 0)?$comments = '':$comments = $this->view->comments($id,'events',3);
        $this->view->comment = $comments;
        ($event->start > 0)?$hTime = ' - '.$Time->get(Zend_Date::DATE_FULL):$hTime = '';
        $this->view->headTitle($headTitle.$hTime, 'PREPEND');
        $this->view->headMeta($headTitle.$hTime,'og:title','property');
        $this->view->headMeta($this->view->serverUrl().$this->view->url(),'og:url','property');
        if($event->description || $event->specials){
            if($event->description){
             $ogDesc = $event->description;
            } else {
             $ogDesc = $event->specials;
            }
            $this->view->headMeta($ogDesc,'og:description','property');
            $ogDesc = $this->view->shortText($ogDesc,160);
            $this->view->headMeta($ogDesc,'description','name');
        }
        if($thumbexist > 0)
            $this->view->headMeta($this->view->serverUrl().'/'.$evimg,'og:image','property');
        
        $keywords = explode(' ', $event->title);
        $keywords[] = $location->name;
        $keywords[] = $location->address;
        $keywords = implode(', ', array_unique($keywords));
        $this->view->headMeta($keywords,'keywords','name');
        $this->view->headMeta($creator->name,'author','name');
        $created = new Zend_Date($event->created);
        $this->view->headMeta($created->get(Zend_Date::ISO_8601),'date','name');
        
        $this->view->schema = '<meta itemscope itemtype="http://schema.org/Event" itemref="_name1 tmStrt _location3 n2simg-avat_surround _description11"> <meta id="_location3" itemprop="location" itemscope itemtype="http://schema.org/Place" itemref="lcNm lcAddrs">';
        
        $join = '';
        $showJoin = FALSE;
        
        if($curuser > 0 && $event->published == 1){
            $joinCheck = $members->checkMember($id, $curuser);
            $join = '<div id="joinEvent">';
            if($Time2->isEarlier($now) && $joinCheck){
                $showJoin = TRUE;
                $join .= '<div style="text-align:center;"><b>'.$this->view->translate(($joinCheck->status == 1)?'You have joined this event':'You will maybe join this event').'</b></div>';
            } elseif($now->isEarlier($Time2)) {
                $showJoin = TRUE;
                $join .= '<div id="joinEvent-loading"><img src="images/ajax/ajax-loader1.gif" alt=""/></div>';
                $join .= '<div id="joinEvent-text"';
                if(count($joinCheck) == 0)
                    $join .= ' style="display:none;"';
                $join .= '><b>';
                if(count($joinCheck) > 0)
                    $join .= $this->view->translate(($joinCheck->status == 1)?'You have joined this event':'You will maybe join this event');
                $join .= '</b>';
                $join .= '<span><a href="javascript:void(0);" onclick="javascript:events.join('.$id.',\'deljoin\');"><img id="resetLocButton" title="'.$this->view->translate('Click here to reset').'" class="n2s-tooltip" src="/images/reset.png" style="" alt=""/></a></span>';
                $join .= '</div>';
                $join .= '<div id="joinEvent-buttons"';
                if(count($joinCheck) > 0)
                    $join .= ' style="display:none;"';
                $join .= '>';
                $join .= '<a class="ajaxlink n2s-evjoin-true" href="javascript:void(0);" onclick="javascript:events.join('.$id.',\'join\');">'.$this->view->translate('Join').'</a>';
                $join .= '<a class="ajaxlink n2s-evjoin-maybe" href="javascript:void(0);" onclick="javascript:events.join('.$id.',\'maybe\');">'.$this->view->translate('Maybe').'</a>';
                $join .= '</div><div class="clear"></div>';
            }
            $join .= '</div>';
        }
        $joinHtml = '';
        if($showJoin == TRUE)
            $joinHtml = $join;
        $this->view->join = $joinHtml;
        
        $pubStand = '';
        $pubview = '';
        if($showAdminPanel == TRUE){
            if($public == TRUE && $this->_request->isXmlHttpRequest()){
                $pubview = '<div onclick="javascript:n2s.publicview();" class="viewNotButton"><h3 style="text-align:center;">';
                $pubview .= $this->view->translate('Close public view').'</h3>';
                $pubview .= '</div>';
                $pubStand = 'var pubview = 0;';
            } else {
                $pubview = '<a class="ajaxlink adpan-top left" onclick="javascript:n2s.publicview();" href="javascript:void(0);">'.$this->view->translate('public view').'</a>';
                
                
                ($event->published == 0)?$edLink='Edit draft':$edLink='Edit event';
                $pubview .= '<a class="ajaxlink adpan-top left" href="'.$this->view->url(array('module'=>'default','controller'=>'events','action'=>'edit','id'=>$event->id), 'default', true).'">'.$this->view->translate($edLink).'</a>';
                if($event->published == 0)
                    $pubview .= '<a class="n2s-message n2lbox.ajax ajaxlink adpan-top left" href="'.$this->view->url(array('module'=>'default','controller'=>'events','action'=>'window','id'=>$event->id,'task'=>'delete'), 'default', true).'">'.$this->view->translate('Delete draft').'</a>';
                
                if($event->published == 1 && $owner == TRUE && $now->isEarlier($Time2))
                    $pubview .= '<a class="n2s-message n2lbox.ajax ajaxlink adpan-top left" href="'.$this->view->url(array('module'=>'default','controller'=>'events','action'=>'window','id'=>$event->id,'task'=>'cancel'), 'default', true).'">'.$this->view->translate('Cancel event').'</a>';
                
                $pubStand = 'var pubview = 1;';
            }
        }
        
        $this->view->public = $pubStand;
        $this->view->pubview = $pubview;
        if($this->_request->isXmlHttpRequest())
            $this->_helper->layout()->disableLayout();
    }
    
    public function glistAction()
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $curuser = $auth->getIdentity()->userid;
            $id = (int)$this->_request->getParam('id',0);
            $events = new Default_Model_Events();
            $event = $events->getEvent($id);
            if($event && $event->creator == $curuser && $id > 0){
                $page = $this->_request->getParam( 'page' , 1 );
                $html = '';
                if(!$this->_request->isXmlHttpRequest()){
                    $html .= '<h1>'.$this->view->translate('Guest list from event').' <a href="';
                    $html .= $this->view->url(array('module'=>'default',
                                            'controller'=>'events','action'=>'show','id'=>$id),
                                            'default', true);
                    $html .= '">"'.$event->title.'"</a></h1>';
                }
                if($event->gastlist == 1){
                    if(!$this->_request->isXmlHttpRequest()){
                        $start = new Zend_Date($event->glistStartDate);
                        $end = new Zend_Date($event->glistEndDate);
                        $now = Zend_Date::now();
                        $html .= '<div style="margin-bottom: 20px;">';
                        if($end->isEarlier($now)){
                            $html .= '<b>'.$this->view->translate('Show is ended').'</b><br/><br/>';
                            $this->view->js = '';
                        } else {
                            $check1 = $this->view->translate('E-mail with guest list will be send after show to you.');
                            $check0 = $this->view->translate('E-mail with guest list will be not send after show to you.');
                            ($event->glistSendEMail == 1)?$checkbox = $check1:$checkbox = $check0;
                            ($event->glistSendEMail == 1)?$checked = 'checked ':$checked = '';
                            $html .= '<input id="sendemail" type="checkbox" '.$checked.'name="sendemail"/><b style="margin-left: 10px;" id="sendemailNoti">'.$checkbox.'</b><br />';
                            $html .= '<input id="sendGList" type="hidden" value="'.$event->glistSendEMail.'" name="sendGList"/>';
                            $this->view->js = '<script charset="utf-8" type="text/javascript">var semglist="'.$event->glistSendEMail.'";';
                            $this->view->js .= '$("#sendemail").change(function(){var c=this.checked?"'.$check1.'":"'.$check0.'";window.semglist=this.checked?"1":"0";$("#sendemailNoti").html(c);});';
                            $this->view->js .= '$(window).unload(function(){if(window.semglist!==$("#sendGList").val())$.post("/events/ajax/act/glemail",{send:window.semglist,id:'.$id.'});});';
                            $this->view->js .= '</script>';
                        }
                        $html .= '<b>'.$this->view->translate('Start show').':</b> '.$start->get(Zend_Date::DATETIME);
                        $html .= '<br/>';
                        $html .= '<b>'.$this->view->translate('End show').':</b> '.$end->get(Zend_Date::DATETIME);

                        $html .= '</div>';
                    }
                    $members = new Default_Model_EventsGlist();
                    $member = $members->getMembers($id);
                    if(count($member) > 0){
                        $profil = new Community_Model_Users();
                        if(!$this->_request->isXmlHttpRequest())
                            $html .= '<ul id="messList">';
                        $paginator = Zend_Paginator::factory($member);
                        $paginator->setItemCountPerPage(10);
                        $paginator->setCurrentPageNumber($page);
                        foreach ($paginator as $fr)
                        {
                            $fr_user = $profil->getUser($fr->memberid);
                            $commonFr = $this->view->userCommonFriends($fr->memberid);
                            $html .= '<li class="newsfeed-item">';
                            $html .= '<div class="newsfeed-avatar">'.$this->view->userThumb($fr_user->userid,1,0).'</div>';
                            $html .= '<div style="float:right;width:230px;"><div style="float:left;">';
                            $html .= $this->view->messager($fr_user->userid);
                            $html .= $this->view->friendRequest($fr_user->userid,'margin-top:5px;');
                            $html .= '</div></div>';
                            $html .= '<div class="newsfeed-content" style="margin-right:240px;">';
                            $html .= '<div class="newsfeed-content-top">
                                <a class="black" href="'.$this->view->userLink($fr_user->userid).'">
                                '.$fr_user->name.'</a></div>';
                            $html .= '<div>'.$commonFr.'</div>';
                            $html .= '</div></li>';
                        }
                        if(!$this->_request->isXmlHttpRequest()){
                            $this->view->jQuery()->addJavascriptFile('/js/n2s.events.js');
                            $html .= '</ul>';
                            $html .= '<div id="ajaxload" style="margin: 10px; display: none;"><img src="/images/ajax/ajax-loader1.gif" alt=""/></div>';
                            $html .= '<script  type="text/javascript">';
                            $html .= 'var bSuppressScroll=true, page=2;';
                            $html .= '$(window).scroll(function(){';
                            //$html .= 'var page=$(".LPage:last").attr("id");';
                            $html .= 'if(($(window).scrollTop() >= $("body").height() - $(window).height()-600) && window.page<='.count($paginator).'){';
                            $html .= 'events.goToByScrollGList('.$id.');}});';
                            $html .= '</script>';
                        }
                    } else {
                        $html .= '<div><b>'.$this->view->translate('There are no members in this list').'</b></div>';
                    }
                } else {
                    $html .= '<div><b>'.$this->view->translate('You have not set up a guest list for this event').'</b></div>';
                }
                if(!$this->_request->isXmlHttpRequest()){
                    $this->view->html = $html;
                } else {
                    if(count($paginator)>0){
                        $result = array('error'=>false,'html'=>$html);
                    } else {
                        $result = array('error'=>TRUE);
                    }
                    $this->_helper->json($result);
                }
            } else {
                $this->_helper->redirector('notfound', 'Error', 'default');
            }
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }
    }

    public function userlistAction()
    {
        $id = (int)$this->_request->getParam('id',0);
        $task = (string)$this->_request->getParam('task');
        $show = (string)$this->_request->getParam('show');
        $page = $this->_request->getParam( 'page' , 1 );
        if($this->_request->isXmlHttpRequest() && $id > 0 && isset($task)){
            $this->_helper->layout()->disableLayout();
            $html = '';
            
            $members = new Default_Model_EventsMembers();

            switch ($task){
                case 'going':
                    $joinM = $members->getJoinMembers($id);
                    $join = count($joinM);
                    break;
                case 'maybe':
                    $joinM = $members->getMaybeMembers($id);
                    $join = count($joinM);
                    break;
                default:
                    $this->_helper->redirector('notfound','Error','default');
            }
            
            if($join > 0){
                $joinList = array();
                foreach ($joinM as $j){
                    $joinList[] = $j->memberid;
                }
                $listHtml = $this->_memberslist($joinList,5,$page);
            } else {
                $listHtml = $this->view->translate('There are no members.');
            }

            if($show !== 'ajax'){
                ($task == 'going')?$title = 'Guests going':$title = 'Guests may be going';
                $title = $this->view->translate($title);
                $html .= '<div class="n2Module"><h3>'.$title.' ('.$join.')</h3>';
                $html .= '<div id="n2s-listin-box">';
            }
            $html .= $listHtml;
            if($show !== 'ajax')
                $html .= '</div></div>';
            if($show === 'ajax'){
                $result = array('error'=>FALSE,'html'=>$html);
                $this->_helper->json($result);
            } else {
                $this->view->html = $html;
            }
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }
    }
    
    public function _memberslist($list,$count,$page)
    {
        $auth = Zend_Auth::getInstance();
        
        $users = new Community_Model_Users();
        $req = new Community_Model_FrRequest();
        $user = $users->getUsersInList($list);
        $html = '<ul>';
        if(count($user) > 0){
            $paginator = Zend_Paginator::factory($user);
            $paginator->setItemCountPerPage($count);
            $paginator->setCurrentPageNumber($page);
            $html .= $this->view->paginationControl($paginator, 'Sliding', '_partials/ajaxpagination.phtml');
            foreach ($paginator as $u)
            {
                
                $html .= '<li class="newsfeed-item"><div class="newsfeed-avatar">';
                $html .= $this->view->userThumb($u->userid,1,0);
                $html .= '</div>';
                $html .= '<div class="newsfeed-content">';
                $html .= '<div clas="newsfeed-content-top"><a class="black" href="'.$this->view->userLink($u->userid).'">'.$u->name.'</a></div>';
                if ($auth->hasIdentity()) {
                    $userID = $auth->getIdentity()->userid;
                    $html .= '<div class="newsfeed-meta small">';
                    if($userID != $u->userid){
                        $html .= $this->view->friendRequest($u->userid,'margin-bottom: 5px;');
                        $html .= '<div>'.$this->view->userCommonFriends($u->userid).'</div>';
                    } else {
                        $html .= $this->view->translate('that is you');
                    }
                    $html .= '</div>';
                }
                $html .= '</div>';
                $html .= '</li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }

    public function mapAction()
    {
        $sessionlang = new Zend_Session_Namespace('userlanguage');
        (isset($sessionlang->language))?$lang = '&language='.$sessionlang->language:$lang = '';
        $this->_helper->layout()->disableLayout();
        $static = $this->_request->getParam('static',FALSE);
        $html = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false'.$lang.'"></script>';
        if($static == TRUE){
            $id = (int)$this->_request->getParam('id',0);
            $height = (int)$this->_request->getParam('height',150);
            if($id > 0){
                $html .= '<style type="text/css"><!-- body{margin: 0 !important;} --></style>';
                $html .= '<link type="text/css" rel="stylesheet" media="screen" href="/css/global.css"></link>';
                $html .= '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>';
                //$html .= '<script src="/js/jquery/addons/lightbox/source/jquery.n2lbox.pack.js?v=2.0.4" type="text/javascript"></script>';
                $html .= '<script>';
                $html .= '/* <![CDATA[ */';
                $html .= '$(document).ready(function(){$(".mapLink_to").live("click",function(e){e.preventDefault();window.parent.location.href=this.href;});  $("#view_L-map").live("click",function(e){e.preventDefault();parent.$.n2lbox({href:this.href,type:"iframe",maxWidth:800,maxHeight:600,fitToView:false,width:"70%",height:"70%",autoSize:true,closeClick:false,openEffect:"elastic",openSpeed:550,closeEffect:"elastic",closeSpeed:550});});});';
                $html .= '/* ]]> */';
                $html .= '</script>';
                $html .= $this->view->simpleMaps($id,$height,TRUE);
            }
        } else {
            $lat = $this->_request->getParam('lat');
            $long = $this->_request->getParam('long');
            $loc = $this->_request->getParam('loc');
            $html .= $this->view->googleMaps($lat,$long,$loc,'100%','100%');
        }
        $this->view->html = $html;
    }

    public function breadcrumbs ($user,$activ,$album = '',$albumID = 0,$curuser = 0)
    {
        $img = $this->view->simpleThumb($user->userid);
        $profillink = $this->view->userLink($user->userid);
        $alleventslink = $this->view->url(array('module'=>'default','controller'=>'events','action'=>'userevents','id'=>$user->userid), 'default', true);
        $alldraftslink = $this->view->url(array('module'=>'default','controller'=>'events','action'=>'userevents','task'=>'draft','id'=>$user->userid), 'default', true);
        $albumlink = $this->view->url(array('module'=>'default','controller'=>'events','action'=>'show','id'=>$albumID), 'default', true);
        $editlink = $this->view->url(array('module'=>'default','controller'=>'events','action'=>'edit','id'=>$albumID), 'default', true);
        $bread = $this->view->ifAdmin($user->userid,$user->type);
        $bread .= '<ul class="brcrmb">';
        $bread .= '<li class="brcrm-u img"><a href="'.$profillink.'">';
        $bread .= '<img class="thumb-avatar-supersmall" src="'.$img.'" alt=""/></a></li>';
        $bread .= '<li class="brcrm-u txt"><a href="'.$profillink.'">'.$user->name.'</a></li>';
        $bread .= ($activ=='all')?('<li class="active">'):'<li><a href="'.$alleventslink.'">';
        $bread .= $this->view->translate('all events');
        $bread .= ($activ=='all')?('</li>'):'</a></li>';
        if(($activ == 'all' || $activ == 'draft') && $curuser == $user->userid){
            if($activ == 'all')
                $bread .= '<li><a href="'.$alldraftslink.'">'.$this->view->translate('drafts').'</a></li>';
            if($activ == 'draft')
                $bread .= '<li class="active">'.$this->view->translate('drafts').'</li>';
        }
        
        if ($activ != 'all' && $album != ''){
            $bread .= (($activ == 'album')?('<li class="active">'):('<li><a href="'.$albumlink.'">'));
            $bread .= $album;
            $bread .= ($activ=='album')?('</li>'):'</a></li>';
            if($user->userid == $curuser && $albumID != 0){
                $bread .= (($activ == 'edit')?('<li class="active">'):('<li><a href="'.$editlink.'">'));
                $bread .= $this->view->translate('edit');
                $bread .= ($activ=='edit')?('</li>'):'</a></li>';
            }
        }
        
        if ($activ == 'comments'){
            $bread .= '<li class="active">'.$this->view->translate('comments').'</li>';
        }
        
        $bread .= '</ul>';
        return $bread;
    }
    
    private function _updateEvent($curuser,$id,$data)
    {
        $events = new Default_Model_Events();
        $event = $events->getEvent($id);
        if(isset($event) && $event->creator == $curuser){
            if($data['glist'] === '0'){
                $data['glistcount'] = '0';
                $data['glistdescription'] = '';
                $data['glistduration'] = '';
                $data['glistendduration'] = '';
                $data['glistemail'] = '';
                //$data['glistSendEMail'] = '0';
                //$data['glistEMailSended'] = '0';
            } else {
                $glistduration = new Zend_Date($data['glistduration']);
                $glistendduration = new Zend_Date($data['glistendduration']);
                $data['glistduration'] = $glistduration->get(Zend_Date::TIMESTAMP);
                $data['glistendduration'] = $glistendduration->get(Zend_Date::TIMESTAMP);
            }
            $message = FALSE;
            $addresses = new Default_Model_Adresses();
            if(isset($data['locid']) && $data['locid'] !== '0'){
                $address = $addresses->getAdress($data['locid']);
                if($address){
                    $data['latitude']=$address->latitude;
                    $data['longitude']=$address->longitude;
                }
            } elseif(isset($data['locid']) && $data['locid'] === '0' &&
                    isset($data['loc']) && !empty($data['loc']) &&
                    isset($data['albaddress']) && !empty($data['albaddress'])) {
                $address = $addresses->setAdress($data['loc'], $data['albaddress'],$curuser);
                $data['locid']=$address['id'];
                $data['latitude']=$address['lat'];
                $data['longitude']=$address['lng'];
                if($address['new']){
                    $comMes = '<a href="'.$this->view->url(array("module"=>"default","controller"=>"venues","action"=>"show","id"=>$address['id']),'default', true).'">'.trim($data['loc']).'</a>';
                    $message = sprintf($this->view->translate('New location %s was created.'), $comMes);
                }
            }
            $today = Zend_Date::now();
            $duration = new Zend_Date($data['duration']);
            $endduration = new Zend_Date($data['endduration']);
            $upData = array(
                'updated'=>$today->get(Zend_Date::TIMESTAMP),
                'title'=>trim($data['albname']),
                'locid'=>$data['locid'],
                'photoid'=>$data['photoid'],
                'latitude'=>$data['latitude'],
                'longitude'=>$data['longitude'],
                'description'=>trim($data['albdescription']),
                'specials'=>trim($data['specdescription']),
                'start'=>$duration->get(Zend_Date::TIMESTAMP),
                'end'=>$endduration->get(Zend_Date::TIMESTAMP),
                'permission'=>$data['permissions'],
                'published'=>$data['published'],
                'gastlist'=>$data['glist'],
                'gastlistcount'=>$data['glistcount'],
                'glistdescription'=>trim($data['glistdescription']),
                'glistStartDate'=>$data['glistduration'],
                'glistEndDate'=>$data['glistendduration']
                );
            if($data['glistemail'] != ''){
                $upData['glistEMail'] = $data['glistemail'];
                $upData['glistSendEMail'] = '1';
                if(($data['published'] == 1 && $event->published == 0)||
                        ($event->glistEndDate != NULL && $event->glistEndDate < $data['glistendduration']))
                    $upData['glistEMailSended'] = '0';
            } else {
                $upData['glistSendEMail'] = '0';
                $upData['glistEMail'] = NULL;
            }
            $events->updateEvent($id, $upData);
            
            if($data['published'] == 1 && $event->published == 0){
                $activ = new Community_Model_Activities();
                $actData = array(
                    'actor'=>$curuser,
                    'title'=>'{actor} has added a new event {cid}.',
                    'app'=>'events',
                    'action'=>'created',
                    'cid'=>$id,
                    'locid'=>$data['locid'],
                    'comment'=>'events',
                    'permission'=>$data['permissions']
                );
                $activ->setActiv($actData);
            }
                
            $ajaxList = new Default_Model_Ajaxlist();
            if($data['permissions'] > 0 || $data['published'] == 0){
                $ajaxList->delList($id, 'event');
            } else {
                $ajaxList->setList($id, $curuser, $duration->toString('YYYY'),
                        $duration->toString('M'),
                        $duration->toString('d'),
                        $duration->get(Zend_Date::TIMESTAMP),
                        'event', $data['latitude'], $data['longitude'],$data['glist']);
            }
            return array('error'=>FALSE,'message'=>$message);
        } else {
            return array('error'=>TRUE);
        }
    }

    public function editAction()
    {
        $auth = Zend_Auth::getInstance();
        $id = (int)$this->_request->getParam('id',0);
        if ($auth->hasIdentity() && $id > 0) {
            $curuser = $auth->getIdentity()->userid;
            $profil = new Community_Model_Users();
            $user = $profil->getUser($curuser);
            if (0 == count($user)) {
                $this->_helper->redirector('notfound', 'Error', 'default');
            } else {
                $events = new Default_Model_Events();
                $event = $events->getEvent($id);
                if($event->creator != $curuser){
                    $this->_helper->redirector('notfound','Error','default');
                } else {
                    $request = $this->getRequest();
                    $form = new Default_Form_EventEdit();
                    $adresses = new Default_Model_Adresses();
                    $startDatum = new Zend_Date($event->start);
                    $endDatum = new Zend_Date($event->end);
                    $now = Zend_Date::now();
                    if($startDatum->isEarlier($now) && $event->published == 1)
                    {
                        $submit1 = $form->getElement('submit');
                        $submit1->setLabel('publish new event');
                        $message = '<div style="background-color: rgb(153, 153, 153); border: 2px solid rgb(204, 0, 0); color: rgb(255, 255, 255); margin-bottom: 10px; padding: 10px; box-shadow: 0px 2px 3px rgb(0, 0, 0);">'.$this->view->translate('Aktuelles Event ist schön vergangen, oder läuft bereits. Deswegen ist es nicht möglich es zu aktuallisieren.<br/>Alle Änderungen werden als Entwurf gespeichert.').'</div>';
                    } else {
                        $message = '';
                    }
                    
                    if($now->isEarlier($startDatum) && $event->published == 1)
                    {
                        $submit1 = $form->getElement('submit');
                        $submit1->setLabel('update');
                    }
                    
                    if($event->published == 0)
                    {
                        $submit1 = $form->getElement('cancel');
                        $submit1->setLabel('update draft');
                    }
                    
                    if($user->type != 'venue')
                        $form->getElement('loc')
                            ->setRequired(TRUE)
                            ->setAllowEmpty(false)
                            ->setAttrib('onchange', 'n2s.edit.checkloc();')
                            ->setAttrib('onfocus', 'this.select();')
                            ->addValidator(new N2S_Validate_Field2Confirmation(),true);
                    
                    if ($request->isPost() && $form->isValid($_POST))
                    {
                        //Post bearbeiten
                        $data = $form->getValues();
                        $data['photoid']=$event->photoid;
                        $bgModule = new Default_Model_Background();
                        $newID = $id;
                        if($request->getPost('cancel')){ //Als Entwurf speichern
                            if($event->published == 1){
                                $newID = $events->setEvent($curuser, trim($data['albname']));
                                $bgModule->dubbleImg('event', $id, $newID);
                                $flash = 'New draft was created.';
                            } else {
                                $flash = 'Draft was updated.';
                            }
                            $data['published'] = 0;
                        } else { //Publish Event
                            $data['published'] = 1;
                            if($startDatum->isEarlier($now)){
                                $startDurat = $form->getElement('duration');
                                $startDurat->setAllowEmpty(false)
                                        ->addValidator(new N2S_Validate_EventStartDateConfirmation,true);
                                if($form->isValid($_POST)){
                                    if($event->published != 0){
                                        $newID = $events->setEvent($curuser, trim($data['albname']));
                                        $bgModule->dubbleImg('event', $id, $newID);
                                    }
                                    $flash = 'New event was published.';
                                } else {
                                    goto JUMP;
                                }
                            }else{
                                $flash = 'Event was updated.';
                            }
                        }
                        
                        $flash = $this->view->translate($flash);
                        $update = $this->_updateEvent($curuser,$newID,$data);
                        
                        if($update['error'] == FALSE){
                            if($update['message'] != FALSE)
                                $flash = $flash.'<br/>'.$update['message'];
                            $this->_helper->FlashMessenger($flash);
                            //var_dump($data);
                            $this->_helper->redirector('show','events','default', array('id' => $newID));
                        }
                    }
                    
                    JUMP:
                    
                    $this->view->breadcrumbs = $this->breadcrumbs($user, 'edit', $event->title, $event->id, $curuser);
                    $this->view->jQuery()->addJavascriptFile('/js/jquery/addons/jquery.timepicker.js');
                    $adress = $adresses->getAdress($event->locid);

                    if ($request->isPost()){
                        $evVals = $form->getValues();
                    }else{
                        if($event->gastlist == 1){
                            $GLstartDatum = new Zend_Date($event->glistStartDate);
                            $GLendDatum = new Zend_Date($event->glistEndDate);
                            $prGLstartDatum = $GLstartDatum->get(Zend_Date::DATES).$GLstartDatum->toString(' HH:mm');
                            $prGLendDatum = $GLendDatum->get(Zend_Date::DATES).$GLendDatum->toString(' HH:mm');
                        } else {
                            $prGLstartDatum = '';
                            $prGLendDatum = '';
                        }

                        $evVals = array(
                            'permissions'=>$event->permission,
                            'albdescription'=>$event->description,
                            'specdescription'=>$event->specials,
                            'albname'=>$event->title,
                            'duration'=>$startDatum->get(Zend_Date::DATES).$startDatum->toString(' HH:mm'),
                            'endduration'=>$endDatum->get(Zend_Date::DATES).$endDatum->toString(' HH:mm'),
                            'glist'=>$event->gastlist,
                            'glistcount'=>$event->gastlistcount,
                            'glistdescription'=>$event->glistdescription,
                            'glistduration'=>$prGLstartDatum,
                            'glistendduration'=>$prGLendDatum
                        );
                        
                        if($event->glistEMail != NULL){
                            $evVals['glistemail'] = $event->glistEMail;
                        } else {
                            if($user->type != 'profil'){
                                $gcuser = N2S_User::curuser();
                                $guser = $profil->getUser($gcuser);
                                $gEm = $guser->email;
                            } else {
                                $gEm = $user->email;
                            }
                            $evVals['glistemail'] = $gEm;
                        }

                        if($startDatum->isEarlier($now) && $event->published == 0)
                        {
                            $g1 = $now->get(Zend_Date::DATES).$now->toString(' HH:mm');
                            $d1 = $now->addDay(1)->get(Zend_Date::DATES).$now->toString(' HH:mm');
                            $d2 = $now->get(Zend_Date::DATES).$now->addHour(5)->toString(' HH:mm');
                            $evVals['duration'] = $d1;
                            $evVals['endduration'] = $d2;
                            if($prGLstartDatum != '' && $prGLendDatum != ''){
                                $evVals['glistduration'] = $g1;
                                $evVals['glistendduration'] = $d1;
                            }
                        }
                    }
                    
                    if ($request->isPost() && isset($data['locid']) && $data['locid'] !== '0'){
                        $nWadress = $adresses->getAdress($data['locid']);
                        if(isset($nWadress))
                            $adress = $nWadress;
                    }
                    
                    if(isset($adress)){
                        $evVals['locid']=$adress->id;
                        $evVals['loc']=$adress->name;
                        $evVals['albaddress']=$adress->address;
                        if($user->type == 'venue')
                            $form->getElement('loc')->setAttrib('disabled', 'disabled');
                        $form->getElement('albaddress')->setAttrib('disabled', 'disabled');
                    }
                    $form->setDefaults($evVals);
                    $form = '<div style="float: left; box-shadow: 0px 1px 10px rgba(0, 0, 0, 0.3); padding: 0px 3%; margin: 15px 0px 15px 5%; width: 85%;">'.$form.'</div>';
                    $this->view->html = $message.$form;
                    $this->view->headTitle($this->view->translate('Edit event').' - '.$event->title, 'PREPEND');
                }            
            }
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }
    }
}