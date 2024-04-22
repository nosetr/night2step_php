<?php

/**
 * MessagesController.php
 * Description of MessagesController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.10.2012 13:48:21
 * 
 */
class Community_MessagesController extends Zend_Controller_Action
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
            $this->_forward('index', 'index', 'community');
        } else {
            $view = (int)$this->_request->getParam('view',0);
            $this->view->jQuery()->addJavascriptFile('/js/n2s.message.js');
            if($view > 0){
                $userID = $view;
                $link = '/show/'.$view;
            } else {
                $userID = $auth->getIdentity()->userid;
                $link = '';
            }
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            //Not found
            if (0 == count($user)) {
                $this->_forward('notfound', 'Error', 'default');
            }
            $ahtml = '<h1>';
            if($view > 0){
                $ahtml .= '<a href="'.$this->view->userLink($userID).'">';
                $ahtml .= $user->name;
                $ahtml .= '</a> | ';
            }
            $ahtml .= $this->view->translate('Messages').'</h1>';
            $this->view->html = $ahtml;
            $this->view->link = $link;
            $this->view->headTitle($user->name.' - '.$this->view->translate('Messages'), 'PREPEND');
        }
    }
    
    public function readAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_forward('registration', 'Index', 'community');
        } else {
            $curuser = $auth->getIdentity()->userid;
            $this->view->jQuery()->addJavascriptFile('/js/n2s.message.js');
            $adminShow = (int)$this->_request->getParam('show',0);
            $view = (int)$this->_request->getParam( 'view' , 0 );
            
            $changed = FALSE;
            if($view == $curuser){
                $view = $adminShow;
                $adminShow = 0;
                $changed = TRUE;
            }
                
            ($adminShow > 0)?$userID = $adminShow:$userID = $curuser;
            
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            $viewUser = $profil->getUser($view);
            //Not found
            if (!isset($user) || !isset($viewUser)) {
                $this->_helper->redirector('notfound', 'Error', 'default');
            }
            
            $recep = new Community_Model_MsgRecepient();
            $msgarray = $recep->getAll($userID,$view);
            //Not found
            /*if (0 == count($msgarray)) {
                $this->_helper->redirector('notfound', 'Error', 'default');
            }
             * 
             */
            $toName = $viewUser->name;
            $showLink = array("module"=>"community","controller"=>"messages","action"=>"index");
            if($adminShow > 0){
                $showLink["view"]=$userID;
            } elseif ($changed == TRUE) {
                $showLink["view"]=$viewUser->userid;
                $toName = $user->name;
            }
            $ahtml = '<h1 id="h1" style="margin: 16px 0px;">';
            $ahtml .= '<a href="'.$this->view->url($showLink, 'default', true).'">';
            if($adminShow > 0){
                $ahtml .= $user->name.' - ';
            } elseif ($changed == TRUE) {
                $ahtml .= $viewUser->name.' - ';
            }
            $ahtml .= $this->view->translate('Messages').'</a> | '.$toName.'</h1>';
            $ahtml .= '<div id="msgWriter" style="z-index:1;position: inherit; top: 0px; width: 700px; background-color: rgb(255, 255, 255); padding: 10px;box-shadow:-1px 2px 3px rgba(0, 0, 0, 0.3);">';
            $ahtml .= '<div class="newsfeed-avatar">';
            $ahtml .= $this->view->userThumb($auth->getIdentity()->userid,1,0).'</div>';
            $ahtml .= '<div style="float: left; width: 80%;"><textarea id="messText" placeholder="'.$this->view->translate('write a message ...').'" name="comment" style="margin-bottom: 4px; width: 97%; height: 39px;" rows="1" cols="1"></textarea></div>';
            $ahtml .= '<div class="newsfeed-avatar">';
            $ahtml .= $this->view->userThumb($view,1,0).'</div>';
            $ahtml .= '<button id="im_send" onclick="message.sendtxt('.$view.')" style="font-size: inherit; font-weight: bold; padding: 2px 10px; margin-left: 60px;">'.$this->view->translate('send').'</button>';
            $ahtml .= '<img id="secondAjaxload" style="display:none;margin:0px 60px;" src="/images/ajax/ajax-loader1.gif" alt=""/>';
            $ahtml .= '<div class="clear"></div></div><div id="errorMsg"></div>';
            $this->view->html = $ahtml;
            ($adminShow > 0)?$showLink = $view.'/show/'.$adminShow:$showLink = $view;
            $this->view->show = $showLink;
            $this->view->headTitle($this->view->translate('Messages').' - '.$viewUser->name, 'PREPEND');
            if (0 == count($msgarray)){
                $this->view->dataContainer = '<div class="paged-data-container"><ul id="messList"><li></li></ul></div>';
            } else {
                $this->view->dataContainer = '';
            }
        }
    }

    public function listAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_forward('registration', 'Index', 'community');
        } else {
            $adminShow = (int)$this->_request->getParam('show',0);
            ($adminShow > 0)?$userID = $adminShow:
                $userID = $auth->getIdentity()->userid;
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            $curimg = $this->view->simpleThumb($userID);
            //Not found
            if (0 == count($user)) {
                $this->_forward('notfound', 'Error', 'default');
            }
            
            
            $limit = (int)$this->_request->getParam( 'limit' , FALSE );
            $view = (int)$this->_request->getParam( 'view' , 0 );
            $last = (int)$this->_request->getParam( 'last' , FALSE );
            $recep = new Community_Model_MsgRecepient();
            
            if ($limit != FALSE){
                $limCount = ceil($limit/100);
            } else {
                $limCount = $limit;
            }
            
            if ($last == FALSE){
                $ahtml = '<div class="paged-data-container"><ul id="messList">';
            } else {
                $ahtml = '';
            }
            
            if($view == 0){
                $usersList = $this->_request->getParam( 'ulist' , FALSE );
                if ($usersList == FALSE){
                    $uList = FALSE;
                }else{
                    $uList =  $this->int_array(array_unique(array_filter(explode( ',', $usersList ))));
                    //var_dump($uList);
                }
                $admin = FALSE;
                if($auth->getIdentity()->type == 'profil' && $adminShow == 0){
                    $admins = new Community_Model_Admins();
                    $access = $admins->findAllAccess($userID);
                    if(count($access) > 0){
                        $admin = array($userID);
                        foreach ($access as $a){
                            $admin[] = $a->objectid;
                        }
                    }
                }
                $msgarray = $recep->getAll($userID,$view,$last,FALSE,FALSE,$admin);
                //No messages found
                if (count($msgarray) == 0){
                    $result = array('error'=>TRUE,'action'=>'stop');
                    if ($last == FALSE)
                        $result['message'] = '<b>'.$this->view->translate('No messages').'</b>';
                    goto theEnd;
                }
                
                $allenters = array();
                if ($uList != FALSE){
                    $allusers = $uList;
                }else{
                    $allusers = array();
                }
                foreach ($msgarray as $i){
                    if($i->msg_from == $userID){
                        $iUser = (int)$i->msg_to;
                    } else {
                        if($i->msg_from != $userID && $i->msg_to != $userID){
                            if(in_array($i->msg_from, $admin)){
                                $iUser = (int)$i->msg_from;
                            } else {
                                $iUser = (int)$i->msg_to;
                            }
                        } else {
                            $iUser = (int)$i->msg_from;
                        }
                    }
                    $checkUser = $profil->getUser($iUser);
                    if(!isset($checkUser))
                        $recep->delUserAllMsg($iUser);
                    if (isset($checkUser) && $checkUser->deactivated == '0' && !in_array($iUser, $allusers)) {
                        $allenters[] = (int)$i->msg_id;
                        $allusers[] = (int)$iUser;
                    }
                }
                
                if (count($allenters) == 0){
                    $result = array('error'=>TRUE,'action'=>'stop');
                    goto theEnd;
                }
                
                $msg = $recep->getAll($userID,$view,$last,$limCount,$allenters);
                //No messages found
                if (count($msg) == 0){
                    $result = array('error'=>TRUE,'action'=>'stop');
                    goto theEnd;
                }
                $msgUsersArray = '';
                $msgLink = array("module"=>"community","controller"=>"messages");
                if($adminShow > 0)
                    $msgLink["show"] = $adminShow;
                foreach ($msg as $m){
                    $adminMsg = FALSE;
                    $smTo = FALSE;
                    if($m->from == $userID){
                        if($m->msg_from == $userID){
                            $msgUser = $profil->getUser($m->msg_to);
                        } else {
                            $msgUser = $profil->getUser($m->msg_from);
                        }
                    } else {
                        if($m->msg_from != $userID && $m->msg_to != $userID){
                            $adminMsg = TRUE;
                            if(in_array($m->msg_from, $admin)){
                                $msgUser = $profil->getUser($m->msg_from);
                                $smUser = $m->msg_to;
                                $smTo = TRUE;
                            } else {
                                $msgUser = $profil->getUser($m->msg_to);
                                $smUser = $m->msg_from;
                            }
                        } else {
                            $msgUser = $profil->getUser($m->from);
                        }
                    }
                    
                    ($adminMsg == FALSE)?$msgLink["action"] = "read":$msgLink["action"] = "index";
                    $msgLink["view"] = $msgUser->userid;
                        
                    $msgUsersArray = $msgUsersArray.','.(int)$msgUser->userid;
                    
                    $avat = $this->view->userThumb($msgUser->userid,1,0);

                    $ahtml .= '<li id="'.$m->id.'" class="newsfeed-item allMess">';
                    $ahtml .= '<div class="newsfeed-avatar">'.$avat.'</div>';
                    $ahtml .= '<a class="black" href="';

                    $ahtml .= $this->view->url($msgLink, 'default', true);
                    $ahtml .= '">';
                    $ahtml .= '<div class="newsfeed-content"';
                    if ($m->is_read == 0)
                        $ahtml .= ' style="background-color: #EEEEEE;"';
                    ($adminMsg == TRUE)?$title = sprintf($this->view->translate('Message to all %s admins'),'<b>'.$msgUser->name.'</b>'):
                        $title = '<b>'.$msgUser->name.'</b>';
                    $ahtml .= '><div class="newsfeed-content-top">'.$title.'</div>';
                    $ahtml .= '<div class="newsfeed-content-bottom">';
                    if($m->from == $userID || $adminMsg == TRUE){
                        ($adminMsg == TRUE)?$smimg = $this->view->simpleThumb($smUser):$smimg = $curimg;
                        $ahtml .= '<div style="position:relative;float: left;margin: 0 5px 0 15px;">';
                        $ahtml .= '<img class="thumb-avatar-supersmall" src="'.$smimg.'" alt=""/>';
                        if($adminMsg == TRUE && $smTo == TRUE)
                            $ahtml .= '<span style="position: absolute; left: -15px; top: 5px; width: 18px; height: 18px; background: url(/images/to.png) no-repeat scroll 1px 0px transparent;"></span>';
                        $ahtml .= '</div>';
                    }
                    $date = new Zend_Date($m->posted_on);
                    $ahtml .= '<div style="margin-left: 15px;">'.$this->view->shortText($m->body,200).'</div></div><div class="clear"></div>';
                    $ahtml .= '<div class="newsfeed-meta small"><b>'.$date->get(Zend_Date::DATETIME_SHORT).'</b></div></div></a></li>';
                }
            } else {
                $msg = $recep->getAll($userID,$view,$last,$limCount);
                
                if (count($msg) == 0){
                    $result = array('error'=>TRUE,'action'=>'stop');
                    goto theEnd;
                }
                
                foreach ($msg as $m){
                    $rMsg = $recep->getMsg($m->id);
                    $ahtml .= '<li id="'.$m->id.'" class="newsfeed-item">';
                    $ahtml .= '<div  class="newsfeed-avatar">'.$this->view->userThumb($m->from,1,0).'</div>';
                    $date = new Zend_Date($m->posted_on);
                    $ahtml .= '<div class="newsfeed-content"';
                    if ($rMsg->is_read == 0){
                        $text = $this->view->urlReplace($this->view->escape($m->body));
                        $ahtml .= ' style="background-color: #EEEEEE;"';
                        if($m->from != $userID)
                            $recep->setRead($m->id);
                    } else {
                        $text = $this->view->shortText($m->body,400,TRUE);
                    }
                    $ahtml .= '><div class="newsfeed-content-top">';
                    $ahtml .= '<a class="black" href="'.$this->view->userLink($m->from).'">'.$m->from_name.'</a></div>';
                    $ahtml .= '<div class="newsfeed-content-bottom">'.$text.'</div>';
                    $ahtml .= '<div class="clear"></div>';
                    $ahtml .= '<div class="newsfeed-meta small"><b>'.$date->get(Zend_Date::DATETIME_SHORT).'</b></div>';
                    $ahtml .= '</div>';
                    $ahtml .= '</li>';
                }
                
            }
            
            if ($last == FALSE)
                $ahtml .= '</ul></div>';
            
            if($view == 0){
                $result = array('error'=>false,'html'=>$ahtml,'userlist'=>$msgUsersArray);
            } else {
                $result = array('error'=>false,'html'=>$ahtml);
            }
            
            theEnd:
            $this->_helper->json($result);
        }        
    }
    
    public function ajaxAction()
    {
        $auth = Zend_Auth::getInstance();
        $errorMsg = '<div style="text-align: center; margin: 20px; border: 1px solid red; padding: 10px; border-radius: 8px 8px 8px 8px;">'.
                $this->view->translate('Error').'</div>';
        if (!$auth->hasIdentity()) {
            $result = array('error'=>TRUE,'message'=>$errorMsg);
            goto theEnd;
        } else {
            $userID = $auth->getIdentity()->userid;
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            //Not found
            if (0 == count($user)) {
                $result = array('error'=>TRUE,'message'=>$errorMsg);
                goto theEnd;
            }
            
            $task = (string)$this->_request->getParam( 'task' );
            if ($task == 'check'){
                $recep = new Community_Model_MsgRecepient();
                $msg = $recep->getAjaxMsg($userID);
                
                if (count($msg) > 0){
                    $lhtml = '<div id="msgLST" class="LST">';
                    foreach ($msg as $m){
                        $date = new Zend_Date($m->posted_on);
                        $curimg = $this->view->simpleThumb($m->msg_from);
                        $cIMG = '<div style="float:left; margin-top: 5px;"><img width="45" height="45" src="'.$curimg.'" alt=""/></div>';
                        $lhtml .= '<a href="'.$this->view->url(array("module"=>"community","controller"=>"messages","action"=>"read","view"=>$m->from),'default', true).'">';
                        $lhtml .= '<div class="LSTBody"';
                        if($m->ajax_read == 0){
                            $lhtml .= ' style="background-color: #EEEEEE;"';
                            $recep->setAjaxRead($m->id);
                        }
                        $lhtml .= '>'.$cIMG.'<div style="margin: 5px; width: 215px;">';
                        $lhtml .= '<div  style="width:100%;">'.$m->from_name.'</div>';
                        $lhtml .= '<div  style="width:100%;font-weight: normal;">'.$this->view->shortText($this->view->escape($m->body),100).'</div>';
                        $lhtml .= '<div  style="width:100%;"><b>'.$date->get(Zend_Date::DATETIME_SHORT).'</b></div></div></div>';
                        $lhtml .= '</a>';
                    }
                    $lhtml .= '</div>';
                    $result = array('error'=>FALSE,'html'=>$lhtml);
                } else {
                    $result = array('error'=>TRUE,'message'=>'<div id="ERMSG" style="text-align:center;width: 300px;">'.$this->view->translate('You have no new messages.').'</div>');
                }
            }elseif ($task == 'send'){
                $to = (int)$this->_request->getParam( 'user' );
                $msg = (string)$this->_request->getParam( 'msg' );
                $checkuser = $profil->getUser($to);
                if ($msg == '' || $userID==$to || !isset($checkuser) || (isset($checkuser) && $checkuser->deactivated == '1')){
                    $result = array('error'=>TRUE,'message'=>$errorMsg);
                    goto theEnd;
                } else {
                    $recep = new Community_Model_Msg();
                    $msgID = $recep->setMsg($userID, $user->name, $to, $msg);
                    $lastMsg = $recep->getMsg($msgID);
                    if (0 == count($lastMsg)){
                        $result = array('error'=>TRUE,'message'=>$errorMsg);
                        goto theEnd;
                    } else {
                        $date = new Zend_Date($lastMsg->posted_on);
                        $dateMes = $date->get(Zend_Date::DATETIME_SHORT);
                        
                        $curimg = $this->view->simpleThumb($userID);
                        $avatMes = '<img width="54" height="54" src="'.$this->view->serverUrl().$curimg.'" alt="'.$user->name.'" alt=""/>';
                        
                        $bodyMes = $this->view->escape($lastMsg->body);
                        $nameMes = $user->name;
                        $linkMes = $this->view->baseUrl().'community/messages/read/view/'.$lastMsg->from;
                        
                        $param = new Community_Model_UserParams();
                        $langParam = $param->getParam($to, 'lang');
                        if ($langParam != FALSE){
                            $sb = $this->view->translate('New message from %s ...',$langParam);
                        } else {
                            $sb = $this->view->translate('New message from %s ...');
                        }
                        
                        $subject = sprintf($sb,$nameMes);
                        
                        $ahtml = '<li id="'.$lastMsg->id.'" class="newsfeed-item">';
                        $ahtml .= '<div  class="newsfeed-avatar">'.$this->view->userThumb($lastMsg->from,1,0,false).'</div>';
                        $ahtml .= '<div class="newsfeed-content"';
                        if ($lastMsg->is_read == 0)
                            $ahtml .= ' style="background-color: #EEEEEE;"';
                        $ahtml .= '><div class="newsfeed-content-top">';
                        $ahtml .= '<a class="black" href="'.$this->view->userLink($lastMsg->from).'">'.$nameMes.'</a></div>';
                        $ahtml .= '<div class="newsfeed-content-bottom">'.$this->view->urlReplace($this->view->escape($lastMsg->body)).'</div>';
                        $ahtml .= '<div class="clear"></div>';
                        $ahtml .= '<div class="newsfeed-meta small"><b>'.$dateMes.'</b></div>';
                        $ahtml .= '</div>';
                        $ahtml .= '</li>';
                        
                        $result = array('error'=>FALSE,'html'=>$ahtml);
                        $toUser = $profil->getUser($to);
                        if ($toUser->type == 'profil' && $this->view->userIsOnline($to) == FALSE)
                            $this->e_mail($to,$nameMes,$dateMes,$avatMes,$bodyMes,$linkMes,$subject);
                    }
                }
            } else {
                $result = array('error'=>TRUE,'message'=>$errorMsg);
                goto theEnd;
            }
        }
        
        theEnd:
        $this->_helper->json($result);
    }
    
    function int_array($arr) {
        foreach ($arr as &$val)
            $val = (int)$val;

        return $arr;
    }
    
    private function e_mail($to,$nameMes,$dateMes,$avatMes,$bodyMes,$linkMes,$subject)
    {
        $ehtml = '<table cellspacing="0" cellpadding="8" style="width: 620px; border-collapse: collapse;">';
        $ehtml .= '<tbody><tr><td style="padding-top: 30px;">';
        $ehtml .= $subject;
        $ehtml .= '</td></tr><tr>';
        $ehtml .= '<td style="border-bottom: 1px solid rgb(233, 233, 233);">';
        $ehtml .= '<table cellspacing="0" cellpadding="0" style="border-collapse: collapse;width: 605px;">';
        $ehtml .= '<tbody><tr><td valign="top" bgcolor="#FFFFFF" width="60">';
        $ehtml .= '<a style="color:#CC0000;text-decoration:none;font-weight:bold" href="';
        $ehtml .= $linkMes.'">';
        $ehtml .= $avatMes;
        $ehtml .= '</a></td><td>';
        $ehtml .= '<table cellspacing="0" cellpadding="8" style="width: 505px; border-collapse: collapse;">';
        $ehtml .= '<tbody><tr><td><a target="_blank" href="';
        $ehtml .= $linkMes;
        $ehtml .= '" style="color:#CC0000;text-decoration:none;font-weight:bold">';
        $ehtml .= $nameMes;
        $ehtml .= '</a></td><td  align="right" style="padding-right: 5px; color: rgb(153, 153, 153);">';
        $ehtml .= $dateMes;
        $ehtml .= '</td></tr></tbody></table>';
        $ehtml .= '<div style="color: #333333;width:500px;word-wrap:break-word;padding:0 0 7px 7px;">';
        $ehtml .= $bodyMes;
        $ehtml .= '</div></td></tr></tbody></table></td><td></td></tr></tbody></table>';
        $this->view->eMail($to,$subject,$ehtml);
    }
}