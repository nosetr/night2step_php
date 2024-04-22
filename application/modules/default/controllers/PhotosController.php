<?php

/**
 * PhotosController.php
 * Description of PhotosController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 30.10.2012 19:30:49
 * 
 */
class Default_PhotosController extends Zend_Controller_Action
{

    public function init()
    {
        if ($this->_helper->FlashMessenger->hasMessages()) {
            $this->view->flashmessage = $this->_helper->FlashMessenger->getMessages();
        }
        $this->view->headMeta()->setName('robots','noimageindex');
    }
    
    public function indexAction()
    {
        $task = $this->_request->getParam('task');
        $gSearch = (string)$this->_request->getParam('geosearch',null);
        if($task == 'process'){
            $html = $this->view->action('index','radar','default',array('show'=>'photos','geosearch'=>$gSearch));
            $link = $this->view->url(array('module'=>'default',
                            'controller'=>'photos','action'=>'index','geosearch'=>$gSearch),'default', true);
            $textLink = $this->view->translate('Current albums');
            $titel = $this->view->translate('Photos (In Process)');
        } else {
            $html = $this->view->action('index','radar','default',array('show'=>'photos','task'=>'archive','geosearch'=>$gSearch));
            $link = $this->view->url(array('module'=>'default',
                            'controller'=>'photos','action'=>'index','task'=>'process','geosearch'=>$gSearch),'default', true);
            $textLink = $this->view->translate('Albums in process');
            $titel = $this->view->translate('Photos');
        }
        $this->view->headTitle($titel, 'PREPEND');
        $this->view->titel = $titel;
        $this->view->html = $html;
        $this->view->link ='<a id="archiveLink" href="'.$link.'">'.$textLink.' &gt;&gt;</a>';
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
        
        $arLink = array('show'=>'photos','id'=>$id,'start'=>$start,'end'=>$end);
        
        if(isset($task) && $task == 'event' && $cid > 0){
            $events = new Default_Model_Events();
            $event = $events->getEvent($cid);
            if(isset($event)){
                $venLink = $this->view->url(array('module'=>'default','controller'=>'events','action'=>'show','id'=>$cid),'default', true);
                $title = sprintf($this->view->translate('Relevant albums to event %s'),'<a href="'.$venLink.'">'.$event->title.'</a>');
                $arLink['title'] = $title;
            }
        }
        
        $html = $this->view->action('relevant','radar','default',$arLink);
        $this->view->html = $html;
    }
    
    public function myphotosAction()
    {
        $this->view->headLink()->appendStylesheet('/css/photos.css');
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $userID = $auth->getIdentity()->userid;
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
        
        if ($userID > 0){
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            if (0 == count($user)) {
                $this->_helper->redirector('notfound','Error','default');
            } else {
                $albhtml = $this->view->action('albums','photos','default',array('id'=>$userID,'page'=>1));
                $imghtml = $this->view->action('images','photos','default',array('id'=>$userID,'standalone'=>FALSE,'page'=>1));
            }
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
        
        $this->view->headTitle($this->view->translate('My Photos'), 'PREPEND');
        $this->view->breadcrumbs = $this->breadcrumbs($user,'all');
        $this->view->albhtml = $albhtml;
        $this->view->imghtml = $imghtml;
        $this->view->curuser = $userID;
    }

    public function userphotosAction()
    {
        $this->view->headLink()->appendStylesheet('/css/photos.css');
        $userID = (int)$this->_request->getParam('id', 0);
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $curuser = $auth->getIdentity()->userid;
        } else {
            $curuser = 0;
        }        
        
        if ($userID == 0)
            $userID = $curuser;
        
        if ($userID > 0){
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            if (!isset($user)) {
                $this->_helper->redirector('notfound','Error','default');
            } else {
                if(isset($user) && $user->deactivated == 1)
                    $this->_forward('removed', 'Error', 'default');
                
                $this->view->jQuery()->addJavascriptFile('/js/n2s.comment.js');
                $albhtml = $this->view->action('albums','photos','default',array('id'=>$userID,'page'=>1));
                $imghtml = $this->view->action('images','photos','default',array('id'=>$userID,'page'=>1,'standalone'=>FALSE));
            }
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
        
        $this->view->breadcrumbs = $this->breadcrumbs($user,'all');
        $this->view->albhtml = $albhtml;
        $this->view->imghtml = $imghtml;
        $this->view->curuser = $curuser;
        
        $this->view->headTitle($user->name.' - '.$this->view->translate('all photos'), 'PREPEND');
    }
    
    public function showdayAction()
    {
        $task = $this->_request->getParam('data');
        $gSearch = (string)$this->_request->getParam('geosearch',null);
        $Datum = new Zend_Date($task);
        $link = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'index','geosearch'=>$gSearch),'default', true);
        $html = '<a id="archiveLink" style="float: left;" href="'.$link.'">&lt;&lt; '.$this->view->translate('All photos').'</a>';
        $html .= '<h1 style="text-align: right; margin-right: 10px;">'.$Datum->get(Zend_Date::DATE_FULL).'</h1>';
        $html .= '<div class="clear"></div>';
        $html .= $this->view->action('list','radar','default',array('show'=>'photos','time'=>$Datum->get(Zend_Date::TIMESTAMP),'geosearch'=>$gSearch));
        $this->view->html = $html;
        $this->view->headTitle($this->view->translate('Photos').' - '.$Datum->get(Zend_Date::DATE_FULL), 'PREPEND');
    }
    
    public function deleteAction()
    {
        $auth = Zend_Auth::getInstance();
        $task = (string)$this->_request->getParam('task');
        if($task && $task=='delete'){
            if (!$auth->hasIdentity() || !$this->_request->isXmlHttpRequest()){
                $result = array('error'=>TRUE,'html'=>  $this->view->translate('Error'));
            } else {
                $userID = $auth->getIdentity()->userid;
                $albID = (int)$this->_request->getParam('id',0);
                $albums = new Default_Model_PhotoAlbums();
                $album = $albums->getComAlbumInfo($albID);
                if(count($album)==0 || $album->creator != $userID || $album->type == 'profimg' || $album->type == 'eventimg'){
                    $result = array('error'=>TRUE,'html'=>  $this->view->translate('Error'));
                } else {
                    @set_time_limit(10 * 60);
                    $photos = new Default_Model_Photos();
                    $photo = $photos->getAlbumPhotos($userID, $albID);
                    $comments = new Default_Model_Comments();
                    $ajaxList = new Default_Model_Ajaxlist();
                    $ajaxList->delList($albID,'photo');
                    foreach ($photo as $ph){
                        $photos->delComPhoto($userID,$ph->id,$albID);
                        $comments->delSetComment($ph->id,'photos');
                        if (file_exists(BASE_PATH.'/'.$ph->image)){
                            unlink(BASE_PATH.'/'.$ph->image);
                        }
                        if (file_exists(BASE_PATH.'/'.$ph->thumbnail)){
                            unlink(BASE_PATH.'/'.$ph->thumbnail);
                        }
                        if (file_exists(BASE_PATH.'/'.$ph->original)){
                            unlink(BASE_PATH.'/'.$ph->original);
                        }
                    }
                    $albums->delAlbum($album->id, $userID);
                    $dir = BASE_PATH.'/albums/'.$album->id;
                    $this->_SureRemoveDir($dir, TRUE);
                    $result = array('error'=>FALSE);
                }
            }
            $this->_helper->FlashMessenger($this->view->translate('Album was deleted.'));
            $this->_helper->json($result);
        } else {
            if (!$auth->hasIdentity() || !$this->_request->isXmlHttpRequest()) 
                $this->_helper->redirector('notfound','Error','default');
            $userID = $auth->getIdentity()->userid;
            $albID = (int)$this->_request->getParam('id',0);
            $albums = new Default_Model_PhotoAlbums();
            $album = $albums->getComAlbumInfo($albID);
            if(count($album)==0 || $album->creator != $userID || $album->type == 'profimg' || $album->type == 'eventimg'){
                $this->_helper->redirector('notfound','Error','default');
            } else {
                $this->_helper->layout()->disableLayout();
                $html = '<div id="n2lbox-title" style="border-bottom: 1px solid rgb(187, 187, 187);">';
                $html .= '<h2 style="margin: 0px;">'.$this->view->translate('Delete album').'</h2>';
                $html .= '</div>';
                $html .= '<div id="n2l-boxdeletearray" style="max-width: 515px;padding: 15px 0px; height: 90px;">';
                $html .= '<div id="confirmdelete">';
                $html .= '<b>'.$this->view->translate('Do you realy want to delete this album?').'</b><br/><br/>';
                $html .= $this->view->translate('If you delete this album it will be deleted all photos, activites and comments from this album too.');
                $html .= '</div>';
                $html .= '<ul id="prozessdelete">';
                $html .= '<li>'.$this->view->translate('Please wait...').'</li>';
                $html .= '</ul>';
                $html .= '</div>';
                $html .= '<div id="chAsub">';
                $html .= '<div id="submit-element">';
                $html .= '<input id="submit" class="button special" type="submit" value="'.$this->view->translate('delete').'" onclick="photos.delalbum('.$albID.');return false;" name="submit"/>';
                $html .= '<input id="cancel" style="margin-left:20px;" class="button special" type="submit" onclick="$.n2lbox.close();return false;" value="'.$this->view->translate('cancel').'" name="cancel"/>';
                $html .= '</div>';
                $html .= '</div>';

                $this->view->html = $html;
            }
        }
    }

    public function createAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity() || !$this->_request->isXmlHttpRequest())
            $this->_helper->redirector('notfound','Error','default');
        $userID = $auth->getIdentity()->userid;
        $this->_helper->layout()->disableLayout();
        //$this->view->headLink()->appendStylesheet($this->view->baseUrl().'css/photos.css');
        //$this->view->jQuery()->addJavascriptFile('/js/n2s.photos.js');
        $form = new Default_Form_AlbumCreate();
        
        $form->setAction($this->view->url(array('module'=>'default','controller'=>'photos','action'=>'create'),'default', true));
        $form->getElement('albname')->setRequired(TRUE)->setValidators(array(array('StringLength',FALSE,array(1,50))));
        if ($this->_request->isPost('albumForm') && $auth->hasIdentity())
        {
            if($form->isValid($_POST)){
                $data = $form->getValues();
                $albums = new Default_Model_PhotoAlbums();
                $albumID = $albums->setComAlbum($userID,0,$data);
                //$this->_helper->redirector('useralbums','photos','default',array('task'=>'edit','view'=>$albumID,'id'=>$userID));
                $result = array('error'=>FALSE,'html'=>  $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'useralbums','task'=>'edit','view'=>$albumID,'id'=>$userID),'default', true));
            } else {
                $result = array('error'=>TRUE,'html'=>(string)$form);
            }
            $this->_helper->json($result);
        } else {
            $html = '<div id="n2lbox-title" style="border-bottom: 1px solid rgb(187, 187, 187);"><h2 style="margin: 0px;">'.$this->view->translate('Create new album').'</h2></div>';
            $this->view->html = $html.'<div id=albformarray>'.$form.'</div>';
        }
    }
    
    protected function _getTitle($sFile)
    {
        // return Titel of web-site
        $sData = file_get_contents($sFile);

        if(preg_match('/<head.[^>]*>.*<\/head>/is', $sData, $aHead))
        {   
            //error_reporting(0);
            $sDataHtml = preg_replace('/<(.[^>]*)>/i', strtolower('<$1>'), $aHead[0]);
            $xTitle = simplexml_import_dom(DomDocument::LoadHtml($sDataHtml));

            return (string)$xTitle->head->title;
        }
        return null;
    }

    public function useralbumsAction()
    {
        /* 
         //return Titel of web-site
        $probTitle = $this->_getTitle('http://nightready.my/events/show/id/113');
        echo $probTitle;
         * 
         */
        
        $this->view->headLink()->appendStylesheet('/css/photos.css');
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $curuser = $auth->getIdentity()->userid;
        } else {
            $curuser = 0;
        }
        $photos = new Default_Model_Photos();
        $addresses = new Default_Model_Adresses();
        $userID = (int)$this->_request->getParam('id', 0);
        $albumID = (int)$this->_request->getParam('view', 0);
        $task = $this->_request->getParam('task', 'album');
        if ($userID == 0){
            $userID = $curuser;
        }
        if ($userID > 0){
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            if (!isset($user)) {
                $this->_helper->redirector('notfound','Error','default');
            } else {
                if($user->deactivated == '1')
                    $this->_forward('removed', 'Error', 'default');
                
                $this->view->headMeta($user->name,'author','name');
                
                $albums = new Default_Model_PhotoAlbums();
                $album = $albums->getComAlbumInfo($albumID);
                if ($album) {
                    if($album->type == 'eventimg' || $album->type == 'profimg'){
                        $albName = $this->view->translate($album->name).' - '.$user->name;
                    } else {
                        $albName = $album->name;
                    }
                } else {
                    $this->_helper->redirector('notfound','Error','default');
                }
                
                if($task == 'edit' && $albumID > 0){
                    
                    $form = new Default_Form_AlbumEdit();
                    
                    $formAlbID = new Zend_Form_Element_Hidden('albumid');
                    $formAlbID->setDecorators(array('ViewHelper'));
                    $formUserID = new Zend_Form_Element_Hidden('userid');
                    $formUserID->setDecorators(array('ViewHelper'));
                    
                    $form->addElements(array($formAlbID,$formUserID));
                    if($album->type == 'eventimg' || $album->type == 'profimg'){
                        $form->getElement('albname')->setAttrib('disabled', 'disabled')->setRequired(FALSE);
                    } else {
                        $form->getElement('albname')->setRequired(TRUE)->setValidators(array(array('StringLength',FALSE,array(1,50))));
                    }
                    if($album->partypics==1 && $album->partydate!=NULL){
                        $date = new Zend_Date($album->partydate);
                        $eventDate = $date->get(Zend_Date::DATE_MEDIUM);
                    } else {
                        $eventDate = '';
                    }
                    $albVals = array('userid'=>$curuser,'permissions'=>$album->permissions,'eventdate'=>$eventDate,'event'=>$album->partypics,'albumid'=>$album->id,'albname'=>$albName,'albdescription'=>$album->description);
                    
                    $address = $addresses->getAdress($album->locid);
                    if(count($address)>0){
                        $albVals['locid']=$address->id;
                        $albVals['loc']=$address->name;
                        $albVals['albaddress']=$address->address;
                        $form->getElement('albaddress')->setAttrib('disabled', 'disabled');
                    }
                    
                    if($album->type == 'eventimg' || $album->type == 'profimg'){
                        $form->removeElement('event');
                        $form->removeElement('eventdate');
                        $form->removeElement('loc');
                        $form->removeElement('albaddress');
                        $form->removeElement('permissions');
                    }                        
                    
                    if ($userID == $curuser && $userID == $album->creator && $curuser == $album->creator){
                        
                        if ($this->_request->isPost('albumForm') && $auth->hasIdentity())
                        {
                            if($form->isValid($_POST)){
                                $data = $form->getValues();
                                $albums->setComAlbum($userID,$albumID,$data);
                                $this->_helper->redirector('useralbums','photos','default',array('view'=>$albumID,'id'=>$userID));
                            }
                        }
                            $form->populate($albVals);
                            
                            $albcover = $photos->getPhotoID($album->photoid);
                            if(isset($albcover) && $albcover->albumid==$album->id && file_exists($albcover->thumbnail)){
                                $albcoverthumb = '<img src="'.$albcover->thumbnail.'" alt=""/>';
                                $thEx = TRUE;
                            } else {
                                if(isset($albcover) && $albcover->albumid != $album->id){
                                    $albums->updateComProfilImgAlbum($album->id,0);
                                }
                                $albcoverthumb = '<img src="images/no-photo-thumb.png" alt=""/>';
                                $thEx = FALSE;
                            }
                            $imghtml = '<div id="albcover" style="position: relative;float: left; margin-top: 10px; width: 135px;"';
                            if($thEx==TRUE)
                                $imghtml .= ' class="thex"';
                            $imghtml .= '><div style="position: relative;"><div id="albcover_link">'.$albcoverthumb.'</div><div id="remthex" class="n2s-transpb" onclick="photos.removeCover(0,'.$album->id.')">'.$this->view->translate('Remove cover').'</div></div>';
                            if($album->type != 'eventimg' && $album->type != 'profimg')
                                $imghtml .= '<a class="red n2simg-button n2lbox.ajax ajaxlink left" style="margin-bottom: 5px; width: 95px;" href="'.$this->view->url(array('module'=>'default','controller'=>'photos','action'=>'delete','id'=>$album->id),'default', true).'">'.$this->view->translate('Delete album').'</a>';
                            $imghtml .= '</div>';
                            $imghtml .= '<div style="float: left; box-shadow: 0px 1px 10px rgba(0, 0, 0, 0.3); padding: 0px 3%; margin: 15px 0px 15px 5%; width: 68%;">';
                            $imghtml .= $form.'</div><div class="clear"></div>';
                            $imghtml .= $this->view->action('imgupload','ajax','default',array('id'=>$userID,'page'=>1));
                            $imghtml .= '<div class="summary"  style="margin: 30px 10px 10px 0pt; border-bottom: 1px solid rgb(218, 225, 232);"><b>'.$this->view->translate('Editing Photos').'</b></div>';
                            $imghtml .= $this->_editAlbum($userID,$albumID);
                            
                            $js = '<script>var defPerm=$("#permissions").children("option").filter(":selected").val();$("#loc").live("change",function(){n2s.edit.checkloc();});if($("#event").is(":checked")){$("#permissions").attr("disabled","disabled");$("#eventdate-label").children().show();$(".tag_eventdate").show();}$("#event").click(function(){if(this.checked){$("#permissions").attr("disabled","disabled").val(0);$("#eventdate-label").children().show();$(".tag_eventdate").show();}else{$("#permissions").removeAttr("disabled").val(defPerm);$("#eventdate-label").children().hide();$(".tag_eventdate").hide();}});$(window).unload(function(){$.post("/photos/ajax/task/runaction",{album:"'.$albumID.'"});});</script>';
                            
                            $this->view->headTitle($this->view->translate('Photos').' - '.$this->view->translate('edit'), 'PREPEND');
                        
                    } else {
                        $this->_helper->redirector('notfound','Error','default');
                    }
                } else {
                    if($curuser != $album->creator){
                        $pOwner = FALSE;
                        $hit = $album->hits+1;
                        $data = array('hits'=>$hit);
                        $albums->updateAlbum($album->id, $data);
                        $permis = Community_Model_Permissions::checkPermissions($album->creator,$album->permissions);
                    } else {
                        $pOwner = TRUE;
                        $permis = TRUE;
                    }
                    if($permis == FALSE)
                        $this->_helper->redirector('noaccess','Error','default');
                    
                    $this->view->headTitle($albName, 'PREPEND');
                    $this->view->headMeta($albName,'og:title','property');
                    $imghtml = '<h1>'.$albName.'</h1>';
                    
                    $bgPos = 695;
                    $dvPos = 665;
                    $issetLoc = FALSE;
                    
                    if($album->locid > 0){
                        $address = $addresses->getAdress($album->locid);
                        if(isset($address)){
                            $issetLoc = TRUE;
                            $panel = $this->view->showPanel($address->creator,'album',$pOwner,$address->id,$album->start,$album->start,$album->id);
                            $bgPos = $panel['width']+20;
                            $dvPos = $panel['width']-10;
                        } else {
                            $updata=array('locid'=>'0','location'=>'','latitude'=>'255','longitude'=>'255');
                            $albums->updateAlbum($album->id, $updata);
                        }
                    }

                    $imghtml .= '<div style="margin:0 10px 10px 0;background: url(/images/bg-line2.png) repeat-y scroll '.$bgPos.'px 0px rgb(250, 250, 250);">';
                    $imghtml .= '<div style="width:'.$dvPos.'px;margin:0 5px;float:left;padding:10px;">';
                    $imghtml .= '<ul style="margin: 0px;">';
                    $imghtml .= '<li><span style="background: url(/images/auge.png) no-repeat scroll 3px 4px transparent;color: #999999;cursor: default;font-weight: bold;padding: 0 5px 0 24px;">'.$album->hits.'</span></li>';
                    if($album->partypics == 1){
                        $partyDate = new Zend_Date($album->start);
                        $imghtml .= '<li><span style="background: url(/images/date.png) no-repeat scroll 6px 1px transparent;color: #999999;cursor: default;font-weight: bold;padding: 0 5px 0 24px;">'.$partyDate->get(Zend_Date::DATE_FULL).'</span></li>';
                        $this->view->headMeta('index,follow','robots','name');
                        $this->view->headMeta($partyDate->get(Zend_Date::ISO_8601),'date','name');
                        $keywords = explode(' ', $album->name);
                        if($issetLoc == TRUE){
                            $keywords[] = $address->name;
                            $keywords[] = $address->address;
                        }
                        $keywords = implode(', ', array_unique($keywords));
                        $this->view->headMeta($keywords,'keywords','name');
                    } else {
                        $this->view->headMeta('noindex,follow','robots','name');
                    }
                    if($issetLoc == TRUE){
                        $locLink = '<a href="';
                        $locLink .= $this->view->url(array('module'=>'default','controller'=>'venues','action'=>'show','id'=>$album->locid),'default', true);
                        $locLink .= '">'.$address->name.'</a>';
                        $imghtml .= '<li><span style="background: url(/images/home.png) no-repeat scroll 6px 2px transparent;color: #999999;cursor: default;font-weight: bold;padding: 0 5px 0 24px;">'.$locLink.'</span></li>';
                    }
                    $imghtml .= '</ul></div>';
                    if($issetLoc == TRUE)
                        $imghtml .= $panel['html'];
                    $imghtml .= '<div class="clear"></div></div>';
                    
                    if($album->permissions == 0)
                        $imghtml .= $this->view->socButtons();
                    
                    if($issetLoc == TRUE)
                        $imghtml .= '<div style="margin:0 10px 20px 0;">'.$this->view->simpleMaps($album->locid).'</div>';
                    
                    if($album->description){
                        $imghtml .= '<div style="border-bottom: 2px solid rgb(170, 170, 170); padding: 0px 5px 10px; margin: 0px 10px 20px 0px;">'.$this->view->shortText($album->description,500,TRUE).'</div>';
                        $this->view->headMeta($album->description,'og:description','property');
                        $ogDesc = $this->view->shortText($album->description,160);
                        $this->view->headMeta($ogDesc,'description','name');
                    } else {
                        $ogDesc = $albName;
                        if($issetLoc == TRUE)
                            $ogDesc = $ogDesc.' @ '.$address->name.' '.$address->address;
                        $this->view->headMeta($ogDesc,'og:description','property');
                        $this->view->headMeta($ogDesc,'description','name');
                    }
                    if($album->photoid > 0){
                        $photo = $photos->getPhotoID($album->photoid);
                        if ($photo && file_exists($photo->image)){
                            $this->view->headMeta($this->view->serverUrl().'/'.$photo->image,'og:image','property');
                        }
                    }
                    $imghtml .= $this->view->action('images','photos','default',array('view'=>$albumID,'id'=>$userID,'page'=>1));
                    $js = '';
                }
            }
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
        
        $this->view->headMeta($this->view->serverUrl().$this->view->url(),'og:url','property');
        
        $this->view->jQuery()->addJavascriptFile('/js/n2s.comment.js');
        $this->view->breadcrumbs = $this->breadcrumbs($user,$task,$albName,$albumID,$curuser);
        $this->view->imghtml = $imghtml;
        $this->view->curuser = $curuser;
        $this->view->js = $js;
        $this->view->comment = $this->view->comments($albumID,'albums',3);
    }
    
    public function commentsAction()
    {
        $this->view->headLink()->appendStylesheet('/css/photos.css');
        $auth = Zend_Auth::getInstance();
        $error = FALSE;
        if ($auth->hasIdentity()) {
            $curuser = $auth->getIdentity()->userid;
        } else {
            $curuser = 0;
        }
        if(!$this->_request->isXmlHttpRequest()) {
            $reqHtml = TRUE;
            $this->view->headScript()->appendFile('/js/n2s.photos.js');
            $this->view->jQuery()->addJavascriptFile('/js/n2s.comment.js');
        } else {
            $reqHtml = FALSE;
        }
        $userID = (int)$this->_request->getParam('id', 0);
        $page = $this->_request->getParam( 'page' , 1 );
        if ($userID == 0){
            $userID = $curuser;
        }
        if ($userID > 0){
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            if (!isset($user)) {
                $this->_helper->redirector('notfound','Error','default');
            } else {
                if($user->deactivated == '1')
                    $this->_forward('removed', 'Error', 'default');
                
                $comments = new Default_Model_Comments();
                $photos = new Default_Model_Photos();
                $permis = Community_Model_Permissions::getPermissions($userID);
                $allphoto = $photos->getAllUserPhotos($userID,$permis);
                if(count($allphoto) > 0){
                    $enters = array();
                    foreach ($allphoto as $ph){
                        $enters[] = $ph->id;
                    }
                    $comment = $comments->getAlbumsComments($enters);
                
                    if (count($comment)>0){
                        $paginator = Zend_Paginator::factory($comment);
                        $paginator->setItemCountPerPage(6);
                        $paginator->setCurrentPageNumber($page);

                        if($reqHtml == TRUE){
                            $html = '<div class="paged-data-container" style="padding-right: 10px;">';
                        } else {
                            $html = '';
                        }
                        $html .= '<ul class="n2s-ajaxpage n2s-imgajaxpage" id="'.($page+1).'" style="margin: 0px;">';
                        foreach ($paginator as $com){
                            $photo = $photos->getPhotoID($com->contentid);
                            $comuser = $profil->getUser($com->post_by);
                            $date = new Zend_Date($com->date);
                            if(count($comuser)>0 && file_exists($photo->thumbnail)){
                                $html .= '<li class="newsfeed-item">';
                                $html .= '<a class="n2s-phBox n2lbox.ajax" href="'.$this->view->url(array('module'=>'default',
                                'controller'=>'photo','action'=>'view','id'=>$photo->id,'embed'=>true),'default', true).'">';
                                $html .= '<img style="float:right;width: 100px;" src="'.$photo->thumbnail.'" alt=""/></a>
                                    <div class="newsfeed-avatar">'.$this->view->userThumb($com->post_by,1,0).'</div><div class="newsfeed-content" style="margin-right: 120px;">
                                        <div class="newsfeed-content-top">
                                        <a href="'.$this->view->userLink($com->post_by).'">'.$comuser->name.'</a></div>
                                            <div class="newsfeed-content-bottom">'.$this->view->shortText($com->comment,200,TRUE).'</div>
                                                    <div class="newsfeed-meta small"><b>'.$date->get(Zend_Date::DATETIME_SHORT).'</b></div></div>
                                        <div class="clear"></div></li>';
                            } else {
                                $comments->delComment($com->id);
                            }
                        }
                        $html .= '</ul>';
                        if($reqHtml == TRUE)
                            $html .= '<div id="n2s-content-loading" style="display: none;"><div id="last_msg_loader"></div></div></div>';
                        $url = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'comments','id'=>$userID));
                        $js = $this->imgscrolljs($url,count($paginator));
                    } else {
                        $html = $this->view->translate('no comments');
                        $js = '';
                    }
                } else {
                    $html = $this->view->translate('this user have no photos');
                    $js = '';
                }
                if($reqHtml == TRUE){
                    $this->view->headTitle($this->view->translate('Photos').' - '.$this->view->translate('comments'), 'PREPEND');
                    $this->view->html = $html.$js;
                    $this->view->curuser = $curuser;
                    $this->view->breadcrumbs = $this->breadcrumbs($user,'comments');
                } else {
                    $result = array('error'=>$error,'html'=>$html);
                    $this->_helper->json($result);
                }
            }
        }
        
    }

    public function ajaxAction()
    {
        $auth = Zend_Auth::getInstance();
        if($this->_request->isXmlHttpRequest() && !$this->_request->isPost()) {
            if($auth->hasIdentity()){
                $userID = $auth->getIdentity()->userid;
                $albumID = (int)$this->_request->getParam('album');
                $last = (int)$this->_request->getParam('last');
                $imghtml = $this->_editAlbum($userID,$albumID,$last);
                $this->_helper->json(array('error'=>FALSE,'html'=>$imghtml));
            }else{
                $this->_helper->json(array('error'=>TRUE,'message'=>'Error'));
            }
        }elseif ($this->_request->isPost() && $auth->hasIdentity()){
            $this->_helper->layout()->disableLayout();
            $userID = $auth->getIdentity()->userid;
            $task = $this->_request->getParam('task');
            $photos = new Default_Model_Photos();
            $albums = new Default_Model_PhotoAlbums();
            $ajax = new Default_Model_Ajaxaction();
            $ajaxList = new Default_Model_Ajaxlist();
            
            if ( $task == 'phdscedit' ){
                $photoID = (int)$this->_request->getPost('photo');
                $albumID = (int)$this->_request->getPost('album');
                $photoTXT = $this->_request->getPost('text');
                $photo = $photos->getEditPhoto($userID,$photoID);
                if (count($photo)> 0){
                    $data = array(
                        'caption'=> $photoTXT
                    );
                    $photos->updateComPhoto($userID, $photoID, $albumID, $data);
                    $this->view->html = $this->view->translate('Description is saved');
                } else {
                    $this->view->html = $this->view->translate('Error');
                }
            } elseif ($task == 'getcover'){
                $this->_helper->layout()->disableLayout();
                $albumID = (int)$this->_request->getPost('album');
                $album = $albums->getComAlbumInfo($albumID);
                if (count($album)> 0){
                    $photo = $photos->getEditPhoto($userID,$album->photoid);
                    $this->view->html = '<img src="'.$photo->thumbnail.'" alt=""/>';
                }
            } elseif ($task == 'phmove'){
                $photoID = (int)$this->_request->getPost('photo');
                $albumID = (int)$this->_request->getPost('album');
                $actAlbumID = (int)$this->_request->getPost('actalbum');
                $photo = $photos->getEditPhoto($userID,$photoID);
                if (count($photo)> 0){
                    $album = $albums->getComAlbumInfo($photo->albumid);
                    if(count($album) > 0 && $album->photocount > 0){
                        $count = $album->photocount - 1;
                        $dataAlb = array('photocount'=>$count);
                        $albums->updateAlbum($photo->albumid, $dataAlb);
                        $ajaxList->updateSpecial($photo->albumid, 'photo', $count);
                    }
                    $album2 = $albums->getComAlbumInfo($albumID);
                    if(count($album2) > 0){
                        $count2 = $album2->photocount + 1;
                        $dataAlb2 = array('photocount'=>$count2);
                        $albums->updateAlbum($albumID, $dataAlb2);
                        $ajaxList->updateSpecial($albumID, 'photo', $count2);
                        $permis = $album2->permissions;
                    
                        $data = array(
                            'albumid'=> $albumID,
                            'permissions'=>$permis
                        );
                        $photos->updateComPhoto($userID, $photoID, $actAlbumID, $data);
                        $jsPost = "'".$photoID."','".$actAlbumID."','".$albumID."'";
                        $this->view->html = '<div class="phRestore" onclick="photos.removeImg('.$jsPost.')" >'.$this->view->translate('Restore image').'</div>';
                    } else {
                        $this->view->html = $this->view->translate('Error');
                    }
                } else {
                    $this->view->html = $this->view->translate('Error');
                }
            } elseif ($task == 'runaction'){
                $actions = $ajax->getActions($userID, 'photo');
                $albumID = (int)$this->_request->getPost('album');
                
                if (count($actions) > 0) {
                    $comments = new Default_Model_Comments();
                    foreach ($actions as $act){
                        $photo = $photos->getEditPhoto($userID,$act->objectid);
                        if ($act->action == '1' && count($photo)> 0){
                            if (file_exists(BASE_PATH.'/'.$photo->image)){
                                unlink(BASE_PATH.'/'.$photo->image);
                            }
                            if (file_exists(BASE_PATH.'/'.$photo->thumbnail)){
                                unlink(BASE_PATH.'/'.$photo->thumbnail);
                            }
                            if (file_exists(BASE_PATH.'/'.$photo->original)){
                                unlink(BASE_PATH.'/'.$photo->original);
                            }
                            
                            $album = $albums->getComAlbumInfo($photo->albumid);
                            if(count($album)>0 && $album->photocount>0){
                                $count = $album->photocount - 1;
                                $dataAlb = array('photocount'=>$count);
                                $albums->updateAlbum($photo->albumid, $dataAlb);
                                $ajaxList->updateSpecial($photo->albumid, 'photo', $count);
                            }
                            $photos->delComPhoto($userID,$act->objectid,$albumID);
                            $comments->delSetComment($photo->id,'photos');
                        }
                    }
                    $ajax->delActions($userID, 'photo');
                }
            } elseif ($task == 'phdel'){
                $photoID = (int)$this->_request->getPost('photo');
                $albumID = (int)$this->_request->getPost('album');
                $photo = $photos->getEditPhoto($userID,$photoID);
                if (count($photo)> 0){
                    $action = $ajax->setAction($userID, $photoID, 'photo');
                    $data = array('published'=> '0');
                    $photos->updateComPhoto($userID, $photoID, $albumID, $data);
                    $jsPost = "'".$photoID."','".$albumID."'";
                    $this->view->html = '<div class="phRestore" onclick="photos.restorImg('.$jsPost.')" >'.$this->view->translate('Restore image').'</div>';
                } else {
                    $this->view->html = 'error';
                }
            } elseif ($task == 'phrestore'){
                $photoID = (int)$this->_request->getPost('photo');
                $albumID = (int)$this->_request->getPost('album');
                $photo = $photos->getEditPhoto($userID,$photoID);
                if (count($photo)> 0){
                    $data = array('published'=> '1');
                    $photos->updateComPhoto($userID, $photoID, $albumID, $data);
                    $action = $ajax->delAction($userID, $photoID, 'photo');
                    $this->view->html = $this->view->translate('Image is restored');
                } else {
                    $this->view->html = $this->view->translate('Error');
                }
            } else {
                $this->view->html = $this->view->translate('Error');
            }
        }
    }

    public function albumsAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $curuser = $auth->getIdentity()->userid;
        } else {
            $curuser = 0;
        }
        if(!$this->_request->isXmlHttpRequest()) {
            $reqHtml = TRUE;
            $this->view->jQuery()->addJavascriptFile('/js/n2s.photos.js');
            //$this->view->jQuery()->addJavascriptFile('/js/n2s.cycle.js');
        } else {
            $reqHtml = FALSE;
        }
        $error = FALSE;
        $userID = (int)$this->_request->getParam('id', 0);
        $page = $this->_request->getParam( 'page' , 1 );
        $rows = $this->_request->getParam( 'rows',null );
        if ($userID == 0 && !isset($rows)){
            $userID = $curuser;
        }
        if ($userID > 0 || $rows){
            if($rows == null){
                $profil = new Community_Model_Users();
                $user = $profil->getUser($userID);
                if (!$user){
                    ($reqHtml == TRUE)?$this->_helper->redirector('notfound','Error','default'):$error=TRUE;
                }
            }
            
                $albums = new Default_Model_PhotoAlbums();
                if(!isset($rows)){
                    $permis = Community_Model_Permissions::getPermissions($userID);
                    $album = $albums->getComAlbum($userID,NULL,FALSE,$permis);
                } else {
                    $album = $albums->getComAlbum(null,$rows);
                }
                if($album){
                    $addresses = new Default_Model_Adresses();
                    $paginator = Zend_Paginator::factory($album);
                    $paginator->setItemCountPerPage(6);
                    $paginator->setCurrentPageNumber($page);
                    
                    if ($page == 1){
                        (count($album) == 1)? $albcount = sprintf($this->view->translate('%d album'),count($album)): $albcount = sprintf($this->view->langHelper('%d albums',count($album)),count($album));
                        $albhtml = '<div class="summary" style="margin:';
                        if($userID == $curuser)
                            $albhtml .= '20px ';
                        $albhtml .= '10px;"><b>'.$albcount;
                        if(count($album) > 0 && !isset($rows)){
                            $albhtml .= ' | <a href="'.$this->view->url(array('module'=>'default','controller'=>'photos','action'=>'comments','id'=>$userID), 'default', true).'">';
                            $albhtml .= $this->view->translate('All photo comments').'</a>';
                        }
                        $albhtml .= '</b>';
                        if($userID == $curuser){
                            $pageN = $this->view->navigation()->findOneBy('label', 'My Photos');
                            if($pageN){$pageN->setActive(TRUE);}
                            $albhtml .= '<a class="red n2simg-button n2lbox.ajax right ajaxlink" style="margin-bottom:5px;" href="'.$this->view->url(array('module'=>'default','controller'=>'photos','action'=>'create'), 'default', true).'">'.$this->view->translate('Create new album').'</a>';
                        }
                        $albhtml .= '</div>';
                        $albhtml .= '<div id="n2s-albcontent">';
                    } else {
                        $albhtml = '';
                    }
                    $albhtml .= '<div class="n2s-ajaxpage" id="'.($page+1).'">';
                    $photos = new Default_Model_Photos();
                    foreach ($paginator as $alb)
                    {
                        $aurl = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'useralbums','view'=>$alb->id,'id'=>$alb->creator), 'default', true);
                        $eurl = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'useralbums','task'=>'edit','view'=>$alb->id,'id'=>$alb->creator), 'default', true);
                        
                        //$photocount = count($photos->getAllAlbumPhotos($alb->creator, $alb->id));
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
                        $albhtml .= '<div class="n2s-albsur"><div class="n2s-albthumb" rel="'.$alb->id.'"><a class="img_link" href="'.$aurl.'">';
                        if ($photocount > 0 && $photo){
                            ($photo->original != NULL && file_exists(BASE_PATH.'/'.$photo->original))?$albimg=$photo->original:$albimg=$photo->image;
                            $width = $photo->width;
                            $height = $photo->height;
                            if($width == 0 && $height == 0){
                                $width = 345;
                                $height= 196;
                            }
                            if($width > $height){
                                $height = ($height*345)/$width;
                                $width = 345;
                                if($height < 196){
                                    $width = ($width*196)/$height;
                                    $height = 196;
                                }
                            } else {
                                $width = ($width*196)/$height;
                                $height = 196;
                                if($width < 345){
                                    $height = ($height*345)/$width;
                                    $width = 345;
                                }
                            }
                            ($height>196)?$top=(196-$height)/4:$top=0;
                            ($width>345)?$left=(345-$width)/2:$left=0;
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
                        $albhtml .= '</a></div>';
                        $albhtml .= '<div class="gdyf"><div class="n2s-albtitle">';
                        $albhtml .= '<div class="fbkl">';
                        $albhtml .= '<a class="img_link n2s-albname" href="'.$aurl.'">';
                        if($alb->type == 'eventimg' || $alb->type == 'profimg'){
                            $albhtml .= $this->view->translate($alb->name);
                        } else {
                            $albhtml .= $alb->name;
                        }
                        $albhtml .= '</a>';
                        $albhtml .= '<div class="n2s-camera">'.$photocount.'</div>';
                        $albhtml .= '</div>';
                        $albhtml .= '<div class="description">'.$albnamehtml.'<div>'.$alb->description.'</div></div>';
                        $albhtml .= '</div></div></div>';
                        if($auth->hasIdentity() && $rows == null && $alb->creator == $curuser){
                            $albhtml .= '<a title="'.$this->view->translate('edit').'" href="'.$eurl.'" class="alb_edit"></a>';
                        }
                        $albhtml .= '</div>';
                    }
                    $albhtml .= '</div>';
                    if ($page == 1){
                        $albhtml .= '</div>';
                        if (count($paginator)>1){
                            $albhtml .= '<div id="n2s-content-start"><b>'.$this->view->translate('view more albums').' ('.count($album).')</b></div>
                                       <div id="n2s-content-loading" style="display: none;"><div id="last_msg_loader"></div></div>';
                        }
                        if(!isset($rows)){
                            $url = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'albums','id'=>$userID));
                        } else {
                            $url = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'albums','rows'=>$rows));
                        }
                        $albhtml .= $this->scrolljs($url,count($paginator));
                    }
                }
        }else{
            ($reqHtml == TRUE)?$this->_helper->redirector('notfound','Error','default'):$error=TRUE;
        }
        
        if($reqHtml == TRUE){
            $this->view->albhtml = $albhtml;
        } else {
            $result = array('error'=>$error,'html'=>$albhtml);
            $this->_helper->json($result);
        }
    }
    
    public function uploadAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $form = new Default_Form_PhotosUpload();
            $albums = new Community_Model_PhotoAlbums();
            if ($this->_request->isPost() && $form->isValid($_POST))
            {

            } else {
                $albumID = $this->_request->getParam('album', 0);
                if($albumID > 0){
                    $album = $albums->getComAlbumInfo($albumID);
                    if(count($album)>0){
                        $albVals = array('albid'=>$album->id,'albname'=>$album->name,'albdescription'=>$album->description);
                        $form->populate($albVals);
                    }
                }
                $html = '<a class="edit-this n2lbox.iframe" href="';
                $html .= $this->view->url(array(
                                            'controller'=>'ajax',
                                            'action'=>'imgselect',
                                            'task'=>'album_select'
                                        ),'default',true);
                $html .= '">Select album</a>';
                $html .= $form;
                $this->view->html = $html;
            }
        } else {
            $this->_helper->redirector('notfound','Error','default');
        }
    }

    public function imagesAction()
    {
        $auth = Zend_Auth::getInstance();
        $error = FALSE;
        if ($auth->hasIdentity()) {
            $curuser = $auth->getIdentity()->userid;
        }
        if(!$this->_request->isXmlHttpRequest()) {
            $reqHtml = TRUE;
            $this->view->headScript()->appendFile('/js/n2s.photos.js');
        } else {
            $reqHtml = FALSE;
        }
        $userID = (int)$this->_request->getParam('id', 0);
        $albumID = (int)$this->_request->getParam('view', 0);
        $page = (int)$this->_request->getParam( 'page' , 1 );
        $standAlone = (bool)$this->_request->getParam( 'standalone' , TRUE );
        if ($userID == 0){
            $userID = $curuser;
        }
        if ($userID > 0){
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            if ($user) {
                $photos = new Default_Model_Photos();
                $comments = new Default_Model_Comments();
                $albums = new Default_Model_PhotoAlbums();
                $permis = Community_Model_Permissions::getPermissions($userID);
                if ($albumID > 0){
                    $album = $albums->getComAlbumInfo($albumID);
                    if($album->creator != $userID)
                        $this->_helper->redirector('notfound','Error','default');
                    $photo = $photos->getAllAlbumPhotos($userID, $albumID,0,NULL,$permis);
                    $show = 'album';
                }else{
                    $photo = $photos->getAllUserPhotos($userID,$permis);
                    $show = 'user';
                }
                if(count($photo) > 0){
                    $paginator = Zend_Paginator::factory($photo);
                    $paginator->setItemCountPerPage(35);
                    $paginator->setCurrentPageNumber($page);
                    
                
                    if ($page == 1){
                        if (!$albumID){
                            $albhtml = '<div class="summary" style="margin: 30px 10px 10px; border-bottom: 1px solid rgb(218, 225, 232);"><b>'.$this->view->translate('Recently uploaded photos').'</b></div>';
                            $albhtml .= '<div id="n2s-imgcontent">';
                        } else {
                            $albhtml = '<div id="n2s-imgcontent">';
                        }
                    } else {
                        $albhtml = '';
                    }
                    $albhtml .= '<div class="n2s-imgajaxpage" id="'.($page+1).'">';
                    
                    foreach ($paginator as $alb)
                    {
                        if (file_exists($alb->thumbnail)){
                            $toggle = "$(this).toggleClass('checked'); photos.dirImg('".$alb->id."','".$alb->albumid."')";
                            ($alb->hits>99)?$hits='>99':$hits=$alb->hits;
                            $commentsCount = count($comments->getComments($alb->id, 'photos'));
                            if($commentsCount>99)
                                $commentsCount='>99';
                            $album = $albums->getComAlbumInfo($alb->albumid);
                            $albhtml .= '<div class="n2s-photo-thumb">';
                            $albhtml .= '<div class="n2s-photo-thumb-text"><div class="n2s-photo-thumb-textinfo"><span class="n2s-photo-thumb-textinfohits">'.$hits.'</span>';
                            $albhtml .= '<span class="n2s-photo-thumb-textinfocomm">'.$commentsCount.'</span>';
                            if($standAlone == FALSE){
                                $albhtml .= '<div><a href="';
                                $albhtml .= $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'useralbums','view'=>$album->id,'id'=>$album->creator),'default', true);
                                $albhtml .= '">'.$this->view->shortText($album->name,40).'</a></div>';
                            }
                            $albhtml .= '</div></div>';
                            $albhtml .= '<a class="n2s-phBox n2lbox.ajax" href="';
                            $albhtml .= $this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$alb->id,'show'=>$show),'default', true);
                            $albhtml .= '"><img src="'.$alb->thumbnail.'" width="134" height="134" alt=""/></a>';
                            if($auth->hasIdentity() && $curuser == $userID && $user->avatar != $alb->id){
                                $albhtml .= '<div id="img_delete'.$alb->id.'" class="img_delete" onclick="'.$toggle.'"></div>';
                            }
                            $albhtml .= '</div>';
                        }
                    }
                    $albhtml .= '</div>';
                    if ($page == 1){
                        $albhtml .= '</div>';
                        if (count($paginator)>1){
                            $albhtml .= '<div class="clear"></div><div id="n2s-content-loading" style="display: none;"><div id="last_msg_loader"></div></div>';
                        }
                    }
                } else {
                    $albhtml = '<div class="summary" style="margin: 10px;"><b>';
                    if($auth->hasIdentity() && $userID == $curuser){
                        $albhtml .= $this->view->translate('You have no photos');
                    } else {
                        $albhtml .= $this->view->translate('This user has no photos');
                    }
                    $albhtml .= '</b></div>';
                    $paginator = '';
                }
            } else {
                ($reqHtml == TRUE)?$this->_helper->redirector('notfound','Error','default'):$error=TRUE;
            }
        }else{
            ($reqHtml == TRUE)?$this->_helper->redirector('notfound','Error','default'):$error=TRUE;
        }
        
        $editjs = '<script>$(window).unload(function() {
                                      $.post("/photos/ajax/task/runaction", { album: "0"} );
                                    });</script>';
        if($reqHtml == TRUE){
            $this->view->albhtml = $albhtml;
            $url = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'images','standalone'=>$standAlone,'view'=>$albumID,'id'=>$userID));
            $js = $this->imgscrolljs($url,count($paginator));
            $this->view->js = $js;
            $this->view->editjs = $editjs;
        } else {
            $result = array('error'=>$error,'html'=>$albhtml);
            $this->_helper->json($result);
        }
    }
    
    protected function _editAlbum($userID,$albumID,$last=0)
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            if ($userID == $auth->getIdentity()->userid) {
                if ($albumID > 0){
                    $photos = new Default_Model_Photos();
                    $albums = new Default_Model_PhotoAlbums();
                    $photo = $photos->getAllAlbumPhotos($userID, $albumID,$last,5);
                    $album = $albums->getComAlbum($userID);
                    $curAlb = $albums->getComAlbumInfo($albumID);

                    $this->view->jQuery()->addJavascriptFile('/js/n2s.photos.js');
                    $html = '';
                    
                    if($last==0)
                        $html .= '<div class="paged-data-container"><ul class="n2s-ajaxpage">';
                    foreach ($photo as $ph) {
                        if (file_exists($ph->thumbnail)){
                            $select = '<div class="n2s-movephoto">
                                        <div class="n2s-movelink">'.$this->view->translate('Move in album').'</div><ul class="showcasten">';
                            foreach ($album as $alb){
                                if($alb->id != $albumID){
                                    $jsMove = "'".$ph->id."','".$alb->id."','".$albumID."'";
                                    $select .= '<li onclick="photos.moveImg('.$jsMove.')">'.$alb->name.'</li>';
                                }
                            }
                            $select .= '</ul></div>';
                            $jsPost = "'".$ph->id."','".$ph->albumid."'";
                            $html .= '<li class="newsfeed-item" id="'.$ph->id.'">';
                            $html .= '<div id="photo_orig_view'.$ph->id.'">';
                            $html .= '<a class="n2s-phBox n2lbox.ajax" href="';
                            $html .= $this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$ph->id,'show'=>'album')).'">';
                            $html .= '<img class="left" width="134" height="134" src="'.$ph->thumbnail.'" alt=""/>';
                            $html .= '</a>';
                            $html .= '<div style="margin-left: 150px;min-height: 135px;">';
                            $html .= '<div id="photo_save_result'.$ph->id.'">'.$this->view->translate('Description').'</div>';
                            $html .= '<div id="photo_save_progress'.$ph->id.'" class="photo_save_progress" style=""></div>';
                            $html .= '<textarea id="photo_caption'.$ph->id.'" class="photo_edit_caption"  onChange="photos.saveDesc('.$jsPost.')" style="overflow: hidden;min-height:40px; height: 40px; width: 550px;">'.$ph->caption.'</textarea>';
                            $html .= $select;
                            $html .= '<a class="n2s-imgup n2lbox.iframe black delPh" href="/ajax/thumb/target/'.$ph->id.'">';
                            $html .= $this->view->translate('Edit thumbnail').'</a>';
                            $html .= '<div id="coverCh'.$ph->id.'" ';
                            if($curAlb->photoid == $ph->id){
                                $html .= 'class="delPh coverd" onclick="photos.removeCover('.$ph->id.','.$ph->albumid.')">'.$this->view->translate('Remove cover');
                            }else{
                                $html .= 'class="delPh" onclick="photos.updateCover('.$ph->id.','.$ph->albumid.')">'.$this->view->translate('Set as cover');
                            }
                            $html .= '</div>';
                            $html .= '<div style="float: left; width: 100%;">';
                            $html .= '<div class="delPh deleter" onclick="photos.delImg('.$jsPost.')">'.$this->view->translate('Delete').'</div>';
                            $html .= '<div class="delPh rotor" onclick="photos.rotImg('.$jsPost.')">'.$this->view->translate('Rotate').'</div>';
                            $html .= '</div>';
                            $html .= '<div class="clear"></div></div></div>';
                            $html .= '<div style="display: none;" id="photo_after_view'.$ph->id.'">'.$this->view->translate('restore').'</div></li>';
                        }
                    }
                    if($last==0){
                        $html .= '</ul></div><div class="clear"></div><div id="n2s-content-loading" style="display: none;"><div id="last_msg_loader"><div id="n2s-msg-loader"><b>Loading...</b><img src="images/ajax/ajax-loader3.gif" alt=""/></div></div></div>';
                        if(count($photo)>0){
                        $html .= '<script  type="text/javascript">';
                        $html .= 'var bSuppressScroll=false;';
                        $html .= '$(function(){';
                        $html .= '$(window).scroll(function(){';
                        $html .= 'if(($(window).scrollTop()>=$("body").height()-$(window).height()-600)&&window.bSuppressScroll==false){';
                        $html .= 'var linkscroll="/photos/ajax",';
                        $html .= 'last=$(".newsfeed-item:last").attr("id");';
                        $html .= 'photos.getlistedit(linkscroll,'.$albumID.',last);';
                        $html .= 'window.bSuppressScroll=true;';
                        $html .= '}';
                        $html .= '});';
                        $html .= '});';
                        $html .= '</script>';
                        }
                    }
                }
                return $html;
            } else {
                $this->_helper->redirector('notfound','Error','default');
            }
        } else {
            $this->_helper->redirector('notfound','Error','default');
        }
    }

    public function breadcrumbs ($user,$activ,$album = '',$albumID = 0,$curuser = 0)
    {
        $img = $this->view->simpleThumb($user->userid);
        $profillink = $this->view->userLink($user->userid);
        $allphotoslink = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'userphotos','id'=>$user->userid), 'default', true);
        $albumlink = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'useralbums','view'=>$albumID,'id'=>$user->userid), 'default', true);
        $editlink = $this->view->url(array('module'=>'default','controller'=>'photos','action'=>'useralbums','task'=>'edit','view'=>$albumID,'id'=>$user->userid), 'default', true);
        $bread = $this->view->ifAdmin($user->userid,$user->type);
        $bread .= '<ul class="brcrmb">';
        $bread .= '<li class="brcrm-u img"><a href="'.$profillink.'">';
        $bread .= '<img class="thumb-avatar-supersmall" src="'.$img.'" alt=""/></a></li>';
        $bread .= '<li class="brcrm-u txt"><a href="'.$profillink.'">'.$user->name.'</a></li>';
        $bread .= ($activ=='all')?('<li class="active">'):'<li><a href="'.$allphotoslink.'">';
        $bread .= $this->view->translate('all photos');
        $bread .= ($activ=='all')?('</li>'):'</a></li>';
        
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

    public function scrolljs($url,$pageCount)
    {
        $js = "<script type=\"text/javascript\">";
        $js .= "var bSuppressScroll = false;";
        $js .= "$(function(){ ";
        /*
        $js .= "$('.n2s-albsur').hover( makeTall,makeShort );";
        $js .= "function makeTall(){ ";
        $js .= "$(this).children('.n2s-albtitle').animate({'margin-top':170-$(this).children('.n2s-albtitle').children('.description').height()},200);}";
        $js .= "function makeShort(){ ";
        $js .= "$(this).children('.n2s-albtitle').animate({'margin-top':170},200);}";
        */
        $js .= "$('#n2s-content-start').click(function(){photos.last_msg_funtion('".$url."');window.bSuppressScroll=true;$('div#n2s-content-start').hide();$(window).scroll(function(){if(($(window).scrollTop()>=$('#n2s-albcontent').height()-$(window).height())&&(parseInt($('.n2s-ajaxpage:last').attr('id'))<=".$pageCount.")&&window.bSuppressScroll==false){photos.last_msg_funtion('".$url."');window.bSuppressScroll=true;}});});});</script>";
        $js .= "<script type=\"text/javascript\">jQuery(function($){photos.cycle();});</script>";
        return $js;
    }

    public function imgscrolljs($url,$pageCount)
    {
        $js = "<script>var aSuppressScroll=false;$(function(){ $(window).scroll(function(){if(($(window).scrollTop()>=$('body').height()-$(window).height()-200)&&(parseInt($('.n2s-imgajaxpage:last').attr('id'))<=".$pageCount.")&&window.aSuppressScroll==false){photos.last_photo_function('".$url."');window.aSuppressScroll=true;}});});</script>";
        return $js;
    }
    
    // $dir = the target directory
    // $DeleteMe = if true delete also $dir, if false leave it alone
    //
    // SureRemoveDir('EmptyMe', false);
    // SureRemoveDir('RemoveMe', true);
    private function _SureRemoveDir($dir, $DeleteMe) {
        if(!$dh = @opendir($dir)) return;
        while (false !== ($obj = readdir($dh))) {
            if($obj=='.' || $obj=='..') continue;
            if (!@unlink($dir.'/'.$obj)) $this->_SureRemoveDir($dir.'/'.$obj, true);
        }

        closedir($dh);
        if ($DeleteMe){
            @rmdir($dir);
        }
    }
}