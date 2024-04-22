<?php

/**
 * NotificationController.php
 * Description of NotificationController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 15.09.2013 23:08:55
 * 
 */
class Community_NotificationController extends Zend_Controller_Action
{
    public function init()
    {
        if ($this->_helper->FlashMessenger->hasMessages()) {
            $this->view->flashmessage = $this->_helper->FlashMessenger->getMessages();
        }
    }

    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_forward('registration', 'Index', 'community');
        } else {
            $userID = $auth->getIdentity()->userid;
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            //Not found
            if (0 == count($user)) {
                $this->_forward('notfound', 'Error', 'default');
            }
            
            $module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
            $this->view->navigation()->findOneByModule($module)->setActive(TRUE);
            
            $this->view->jQuery()->addJavascriptFile('/js/n2s.globnotification.js');
            
            $ahtml = '<h1>'.$this->view->translate('Global notifications').'</h1>';
            $ahtml .= '<ul id="messList"></ul>';
            $this->view->html = $ahtml;
            $this->view->maybefriends = $this->view->userMaybeFriends($userID);
            $this->view->headTitle($this->view->translate('Global notifications'), 'PREPEND');
        }
    }
    
    public function listAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity() || !$this->_request->isXmlHttpRequest()) {
            $result = array('error'=>true,'message'=>'Error');
        } else {
            $userID = $auth->getIdentity()->userid;
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            //Not found
            if (0 == count($user)) {
                $result = array('error'=>true,'message'=>'Error');
            } else {
                $limit = $this->_request->getParam('limit', 0);
                $last = (int)$this->_request->getParam('last', 0);
                
                if ($limit > 0){
                    $limCount = ceil($limit/60);
                } else {
                    $limCount = 0;
                }
                
                $glNot = new Community_Model_Notifications();
                $allNoti = $glNot->getUserNoti($userID,$limCount,$last);

                if (count($allNoti) == 0){
                    if ($last == 0){
                        $ahtml = '<div>'.$this->view->translate('You have no new notifications.').'</div>';
                        $result = array('error'=>true,'action'=>'stop','message'=>$ahtml);
                    } else {
                        $result = array('error'=>true,'action'=>'stop');
                    }
                } else {
                    $ahtml = '';
                    foreach ($allNoti as $fr)
                    {
                        $date = new Zend_Date($fr->created);

                        $fr_user = $profil->getUser($fr->actor);
                        $title = $this->_title($fr->id);

                        $ahtml .= '<li id="'.$fr->id.'" class="newsfeed-item">';
                        $ahtml .= '<div id="phRestore'.$fr->id.'" class="phRestore" onclick="glnoti.restor('.$fr->id.')" style="display:none;">'.$this->view->translate('restore').'</div>';
                        $ahtml .= '<div id="origItem'.$fr->id.'"><div class="newsfeed-avatar">'.$this->view->userThumb($fr_user->userid,1,1).'</div>';
                        $ahtml .= '<div class="newsfeed-content">';
                        $ahtml .= '<div class="newsfeed-content-top">'.$title.'</div>';
                        $ahtml .= '<div class="newsfeed-meta small"><b>'.$date->get(Zend_Date::DATETIME_SHORT).'</b>';
                        $ahtml .= '<div id="clickDel'.$fr->id.'" onclick="glnoti.del('.$fr->id.')" class="delPh" style="float:right;">'.$this->view->translate('delete').'</div></div>';
                        $ahtml .= '</div></div></li>';
                    }
                    $result = array('error'=>FALSE,'html'=>$ahtml);
                }
            }
        }
        if (isset($result))
            $this->_helper->json($result);
    }

    public function ajaxAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity() || !$this->_request->isXmlHttpRequest()) {
            $result = array('error'=>true,'message'=>'Error');
        } else {
            $userID = $auth->getIdentity()->userid;
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            //Not found
            if (0 == count($user)) {
                $result = array('error'=>true,'message'=>'Error');
            } else {
                $task = (string)$this->_request->getParam('task');
                $id = (int)$this->_request->getParam('id');
                $ajax = new Default_Model_Ajaxaction();
                $req = new Community_Model_Notifications();
                if ($task == 'remove'){
                    $ajax->setAction($userID, $id, 'globnoti');
                    $result = array('error'=>FALSE);
                } elseif ($task == 'restore'){
                    $ajax->delAction($userID, $id, 'globnoti');
                    $result = array('error'=>FALSE);
                } elseif ($task == 'checkcount'){ //For Notification-Helper each 1min
                    $profil->updateLastVisit($userID);

                    $fr = new Community_Model_FrRequest();
                    $frCount = count($fr->getAjaxRead($userID));
                    $msg = new Community_Model_MsgRecepient();
                    $msgCount = count($msg->getAjaxRead($userID));
                    $globCount = count($req->getAjaxRead($userID));
                    
                    if($frCount > 0 || $msgCount > 0 || $globCount > 0){
                        $result = array('newcount'=>TRUE);
                        if ($frCount > 0){
                            $result['frcount'] = $frCount;
                            $result['message'] = $this->view->translate('New friendship request');
                        }
                        if ($msgCount > 0){
                            $result['msgcount'] = $msgCount;
                            $result['message'] = $this->view->translate('New message');
                        }
                        if ($globCount > 0){
                            $result['glcount'] = $globCount;
                            $result['message'] = $this->view->translate('New notification');
                        }
                    } else {
                        $result = array('newcount'=>FALSE);
                    }
                    
                } elseif ($task == 'runaction'){
                    $actions = $ajax->getActions($userID, 'globnoti');
                    if (count($actions) > 0) {
                        foreach ($actions as $act){
                            if ($act->action == '1'){
                                $req->delUserNoti($act->objectid, $userID);
                            }
                        }
                        $ajax->delActions($userID, 'globnoti');
                    }
                } elseif ($task == 'check') {
                    $req_to = $req->getUserNoti($userID,5);
                    if (count($req_to) > 0){
                        $lhtml = '<div id="glLST" class="LST">';
                        foreach ($req_to as $m){
                            $req->setAjaxRead($m->id);
                            $date = new Zend_Date($m->created);
                            //$curuser = $profil->findUser($m->actor);
                            //$curuser = $profil->getUser($m->actor);
                            $curimg = $this->view->userThumb($m->actor,1,0);
                            /*
                            if (file_exists($this->view->escape($curuser->thumb))){
                                $curimg = $this->view->baseUrl().$curuser->thumb;
                            } else {
                                $curimg = $this->view->baseUrl().'images/avatar/default/'.$this->view->userSex($curuser->userid).'_thumb.jpg';
                            }
                             * 
                             */
                            $cIMG = '<div style="float:left; margin-top: 5px;">'.$curimg.'</div>';
                            $lhtml .= '<div class="LSTBody"';
                            if ($m->ajax_read == 0)
                                $lhtml .= ' style="background-color: #EEEEEE;"';
                            $lhtml .= '>'.$cIMG.'<div style="margin: 5px; width: 215px;">';
                            $lhtml .= '<div  style="width:100%;font-weight: normal;">'.$this->_title($m->id).'</div>';
                            $lhtml .= '<div  style="width:100%;"><b>'.$date->get(Zend_Date::DATETIME_SHORT).'</b></div></div></div>';
                        }
                        $lhtml .= '</div>';
                        $result = array('error'=>FALSE,'html'=>$lhtml);
                    }else {
                        $result = array('error'=>TRUE,'message'=>'<div id="ERMSG" style="text-align:center;width: 300px;">'.$this->view->translate('You have no new notifications.').'</div>');
                    }
                } else {
                    $result = array('error'=>true,'message'=>'Error');
                }
            }   
        }
        if (isset($result))
            $this->_helper->json($result);
    }
    
    private function _title($id)
    {
        $notis = new Community_Model_Notifications();
        $noti = $notis->getNoti($id);
        
        if (count($noti) > 0){
            $title = $this->view->translate($noti->title);
            $suche = array();
            $ersetzer = array();
            
            if (strpos($title, '{actor}') !== FALSE && $noti->actor > 0){
                $profil = new Community_Model_Users();
                $fr_user = $profil->getUser($noti->actor);
                $suche[] = '{actor}';
                $ersetzer[] = '<a href="'.$this->view->url(array(
                    "module"=>"community",
                    "controller"=>"index",
                    "action"=>"profil",
                    "id"=>$fr_user->userid), 'default', true).'">'.$fr_user->name.'</a>';
            }
            
            
            if (count($suche) > 0 && count($ersetzer) > 0)
                $title = str_ireplace($suche, $ersetzer, $title);
            
            return $title;
        }
    }
}
