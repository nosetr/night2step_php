<?php

/**
 * FriendsController.php
 * Description of FriendsController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 17.10.2012 16:38:49
 * 
 */
class Community_FriendsController extends Zend_Controller_Action
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
            $userID = $auth->getIdentity()->userid;
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            
            //Not found
            if (!isset($user)) {
                $this->_helper->redirector('notfound', 'Error', 'default');
            }
            $this->view->jQuery()->addJavascriptFile('/js/n2s.friends.js');
            
            $check = new Default_Model_Ajaxaction();
            $req = new Community_Model_FrRequest();
            $req_to = $req->getFrRequest_to($userID);
            $req_from = $req->getFrRequest_from($userID);
            $friends = $req->getFriendsList($userID);
            
            $ahtml = '';
            if (count($req_to) > 0){
                $vhtml = '<ul id="messList">';
                $vcount = 0;
                foreach ($req_to as $to)
                {
                    $checkIT = $check->getDel($to->connection_id, 'friendreject');
                    if(!isset($checkIT)){
                        $req->setAjaxRead($to->connection_id);
                        $to_user = $profil->getUser($to->connect_from);
                        if($to_user->deactivated != '1'){
                            $vcount++;
                            $commonFr = $this->view->userCommonFriends($to->connect_from);
                            $date = new Zend_Date($to->created);
                            $vhtml .= '<li class="newsfeed-item">';
                            $vhtml .= '<div style="display:none;" id="phRestore'.$to->connection_id.'" class="phRestore" onclick="friends.restorRq('.$to->connection_id.')">'.$this->view->translate('restore').'</div>';
                            $vhtml .= '<div id="origItem'.$to->connection_id.'"><div class="newsfeed-avatar">'.$this->view->userThumb($to->connect_from,1,0).'</div>';

                            $vhtml .= '<div style="float:right;width:230px;">';
                            $vhtml .= '<div id="controllRq'.$to->connection_id.'">';
                            $vhtml .= '<a id="addReq'.$to->connection_id.'" class="red ajaxlink left" style="margin-bottom:5px;padding:2px 10px;" href="javascript:friends.addFr('.$to->connection_id.');">';
                            $vhtml .= $this->view->translate('Add to friends').'</a><div class="clear"></div>';
                            $vhtml .= '<a id="rejectReq'.$to->connection_id.'" class="red ajaxlink left" style="margin-bottom:5px;padding:2px 10px;" href="javascript:friends.rejectReq('.$to->connection_id.');">';
                            $vhtml .= $this->view->translate('Reject').'</a><div class="clear"></div>';
                            $vhtml .= '</div>';
                            $vhtml .= $this->view->messager($to->connect_from,'float:left;').'</div>';

                            $vhtml .= '<div class="newsfeed-content" style="margin-right:240px;">';
                            $vhtml .= '<div class="newsfeed-content-top">';
                            $vhtml .= '<a class="black" href="'.$this->view->userLink($to->connect_from).'">';
                            $vhtml .= $to_user->name.'</a></div>';
                            $vhtml .= '<div class="newsfeed-content-bottom">';
                            $vhtml .= '<div id="msgReqSuc'.$to->connection_id.'" class="phRestore" style="cursor: auto;display:none;">'.$this->view->translate('to friends added').'</div>';
                            $vhtml .= '<div id="msgReqTXT'.$to->connection_id.'" style="margin: 0px 0px 5px 15px;">'.$to->msg.'</div>';

                            $vhtml .= '<div>'.$commonFr.'</div>';
                            $vhtml .= '<div class="newsfeed-meta small"><b>'.$date->get(Zend_Date::DATETIME_SHORT).'</b></div>';
                            $vhtml .= '</div><div class="clear"></div>';
                            $vhtml .= '</div>';
                            $vhtml .= '</div></li>';
                        }
                    }
                }
                $vhtml .= '</ul>';
                if($vcount > 0){
                    $ahtml .= '<h1>'.  $this->view->translate('Friendship request to you').'('.$vcount.')</h1>';
                    $ahtml .= $vhtml;
                }
            }
            
            if (count($req_from) > 0){
                $curimg = $this->view->simpleThumb($user->userid);
                $bhtml = '<ul id="messList">';
                $bcount = 0;
                foreach ($req_from as $from)
                {
                    $checkIT = $check->getDel($from->connection_id, 'friendreq');
                    if(!isset($checkIT)){
                        $from_user = $profil->getUser($from->connect_to);
                        if($from_user->deactivated != '1'){
                            $bcount++;
                            $commonFr = $this->view->userCommonFriends($from->connect_to);
                            $date = new Zend_Date($from->created);
                            $bhtml .= '<li class="newsfeed-item">';
                            $bhtml .= '<div style="display:none;" id="phRestore'.$from->connection_id.'" class="phRestore" onclick="friends.restorAc('.$from->connection_id.')">'.$this->view->translate('restore').'</div>';
                            $bhtml .= '<div id="origItem'.$from->connection_id.'"><div class="newsfeed-avatar">'.$this->view->userThumb($from->connect_to,1,0).'</div>';
                            $bhtml .= '<div style="float:right;width:230px;"><div style="float:left;width:100%;">';
                            $bhtml .= '<a id="delReq'.$from->connection_id.'" style="padding: 2px 10px;margin-bottom:5px;" class="red ajaxlink left" href="javascript:friends.delReq('.$from->connection_id.');">';
                            $bhtml .= $this->view->translate('Delete request').'</a><div class="clear"></div>';
                            $bhtml .= $this->view->messager($from->connect_to, 'float:left;').'</div></div>';
                            $bhtml .= '<div class="newsfeed-content" style="margin-right:240px;">';
                            $bhtml .= '<div class="newsfeed-content-top">';
                            $bhtml .= '<a class="black" href="'.$this->view->userLink($from->connect_to).'">';
                            $bhtml .= $from_user->name.'</a></div>';
                            if ($from->msg != ''){
                                $bhtml .= '<div class="newsfeed-content-bottom">';
                                $bhtml .= '<div style="float: left;margin: 0 5px 0 15px;">';
                                $bhtml .= '<img class="thumb-avatar-supersmall" src="'.$curimg.'" alt=""/></div>';
                                $bhtml .= '<div style="margin-left: 15px;">'.$from->msg.'</div>';
                                $bhtml .= '</div><div class="clear"></div>';
                            }
                            $bhtml .= '<div>'.$commonFr.'</div>';
                            $bhtml .= '<div class="newsfeed-meta small"><b>'.$date->get(Zend_Date::DATETIME_SHORT).'</b></div>';
                            $bhtml .= '</div>';
                            $bhtml .= '</div></li>';
                        }
                    }
                }
                $bhtml .= '</ul>';
                if($bcount > 0){
                    $ahtml .= '<h1>'.  $this->view->translate('Friendship request from you').'('.$bcount.')</h1>';
                    $ahtml .= $bhtml;
                }
            }
            
            $ahtml .= '<h1>'.$this->view->translate('Friends');
            
            $count = 0;
            if (count($friends) > 0){
                $zhtml = '<ul id="messList">';
                foreach ($friends as $fr)
                {
                    $fr_user = $profil->getUser($fr->connect_from);
                    if(isset($fr_user) && $fr_user->deactivated != '1'){
                        $count++;
                        $commonFr = $this->view->userCommonFriends($fr_user->userid);
                        $zhtml .= '<li class="newsfeed-item">';
                        $zhtml .= '<div class="newsfeed-avatar">'.$this->view->userThumb($fr_user->userid,1,0).'</div>';
                        $zhtml .= '<div style="float:right;width:230px;"><div style="float:left;">';
                        $zhtml .= $this->view->messager($fr_user->userid);
                        $zhtml .= '</div></div>';
                        $zhtml .= '<div class="newsfeed-content" style="margin-right:240px;">';
                        $zhtml .= '<div class="newsfeed-content-top">';
                        $zhtml .= '<a class="black" href="'.$this->view->userLink($fr_user->userid).'">';
                        $zhtml .= $fr_user->name.'</a></div>';
                        $zhtml .= '<div>'.$commonFr.'</div>';
                        $zhtml .= '</div></li>';
                    }
                }
                $zhtml .= '</ul>';
            }
            if($count > 0){
                $ahtml .= '('.$count.')</h1>';
                $ahtml .= $zhtml;
            } else {
                $ahtml .= '</h1>';
                $ahtml .= '<div>'.$this->view->translate('You have no friends.').'</div>';
            }
            $this->view->html = $ahtml;
            $this->view->headTitle($this->view->translate('Friends'), 'PREPEND');
        }
    }

    public function showAction()
    {
        $userID = (int)$this->_request->getParam('id',0);
        if($userID == 0)
            $this->_helper->redirector('notfound', 'Error', 'default');
        $auth = Zend_Auth::getInstance();
        ($auth->hasIdentity())?$curuser = $auth->getIdentity()->userid:$curuser = 0;
        $viewself = TRUE;
        $pageN = $this->view->navigation()->findOneBy('label', 'Friends');
        if($curuser == $userID){
            if($pageN)
                $pageN->setActive(TRUE);
            
            $ahtml = $this->view->action('index','friends','community');
            $viewself = FALSE;
        } else {
            if($pageN)
                $pageN->setActive(FALSE);
            $this->view->navigation()->findOneBy('label', 'Community')->setActive(TRUE);
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            
            //Not found
            if (!isset($user))
                $this->_helper->redirector('notfound', 'Error', 'default');
            
            if(isset($user) && $user->deactivated == 1)
                $this->_forward('removed', 'Error', 'default');
            
            $this->view->jQuery()->addJavascriptFile('/js/n2s.friends.js');
            
            $req = new Community_Model_FrRequest();
            $friends = $req->getFriendsList($userID);
                        
            $ahtml = '';
            $ahtml .= '<h1><a href="'.$this->view->userLink($user->userid).'">'.$user->name.'</a> | '.$this->view->translate('Friends').'</h1>';
            if (count($friends) == 0){
                $ahtml .= '<div>'.$this->view->translate('No friends.').'</div>';
            } else {
                $ahtml .= '<ul id="messList">';
                foreach ($friends as $fr)
                {
                    $fr_user = $profil->getUser($fr->connect_from);
                    $commonFr = $this->view->userCommonFriends($fr_user->userid);
                    $ahtml .= '<li class="newsfeed-item">';
                    $ahtml .= '<div class="newsfeed-avatar">'.$this->view->userThumb($fr_user->userid,1,0).'</div>';
                    $ahtml .= '<div style="float:right;width:230px;"><div style="float:left;">';
                    $ahtml .= $this->view->friendRequest($fr_user->userid,'margin-bottom:5px;float:left;');
                    $ahtml .= '<div style="float:left;">'.$this->view->messager($fr_user->userid).'</div>';
                    $ahtml .= '</div></div>';
                    $ahtml .= '<div class="newsfeed-content" style="margin-right:240px;">';
                    $ahtml .= '<div class="newsfeed-content-top">
                        <a class="black" href="'.$this->view->userLink($fr_user->userid).'">
                        '.$fr_user->name.'</a></div>';
                    $ahtml .= '<div>'.$commonFr.'</div>';
                    $ahtml .= '</div></li>';
                }
                $ahtml .= '</ul>';
            }
            $this->view->headTitle($user->name.' - '.$this->view->translate('Friends'), 'PREPEND');
        }
        $this->view->html = $ahtml;
        $this->view->viewself = $viewself;
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
                $req = new Community_Model_FrRequest();
                if ($task == 'remove'){
                    $ajax->setAction($userID, $id, 'friendreq');
                    $result = array('error'=>FALSE);
                } elseif ($task == 'send'){
                    $toID = (int)$this->_request->getParam('user');
                    $toMSG = (string)$this->_request->getParam('msg');
                    $checkReq = $req->checkFrRequest($userID, $toID);
                    if (count($checkReq) > 0){
                        $result = array('error'=>true,'message'=>$this->view->translate('Friendship request is already exist.'));
                    } else {
                        $checkReq = $req->checkFrRequest($userID, $toID, 1);
                        if (count($checkReq) == 0){
                            $nRQ = $req->setFrRequest($userID, $toID, $toMSG);
                            if ($nRQ > 0){
                                $curReq = $req->getFrRequest($nRQ);
                                $result = array('error'=>FALSE);
                                if ($this->view->userIsOnline($toID) == FALSE){
                                    $curimg = $this->view->simpleThumb($user->userid);
                                    $avatMes = '<img width="54" height="54" src="'.$curimg.'" alt="'.$user->name.'" alt=""/>';
                                    $nameMes = $user->name;
                                    $linkMes = $this->view->baseUrl().'community/friends';
                                    $date = new Zend_Date($curReq->created);
                                    $dateMes = $date->get(Zend_Date::DATETIME_SHORT);
                                    $param = new Community_Model_UserParams();
                                    $langParam = $param->getParam($toID, 'lang');
                                    if ($langParam != FALSE){
                                        $sb = $this->view->translate('%s wants to be friends with you on night2step.',$langParam);
                                    } else {
                                        $sb = $this->view->translate('%s wants to be friends with you on night2step.');
                                    }

                                    $subject = sprintf($sb,$nameMes);
                                    $this->e_mail($toID,$nameMes,$dateMes,$avatMes,$this->view->escape($toMSG),$linkMes,$subject);
                                }
                            }
                        } else {
                            $result = array('error'=>true,'message'=>$this->view->translate('already your friend'));
                        }
                    }
                } elseif ($task == 'unfriend'){
                    $toID = (int)$this->_request->getParam('user');
                    $checkReq = $req->checkFrRequest($userID, $toID, 1);
                    if (count($checkReq) == 0){
                        $result = array('error'=>true,'message'=>'Error');
                    } else {
                        $req->delFriendship($userID, $toID);
                        $result = array('error'=>FALSE);
                        
                        $noti = new Community_Model_Notifications();
                        $content = '{actor} has removed you from friends.';
                        $noti->setUserNoti($userID, $toID, $content, 'frrequest');
                        
                        if ($this->view->userIsOnline($toID) == FALSE){
                            $curimg = $this->view->simpleThumb($user->userid);
                            $avatMes = '<img width="54" height="54" src="'.$curimg.'" alt="'.$user->name.'" alt=""/>';
                            $nameMes = $user->name;
                            $linkMes = $this->view->baseUrl().'community/index/profil/id/'.$userID;
                            $date = new Zend_Date();
                            $dateMes = $date->get(Zend_Date::DATETIME_SHORT);
                            $param = new Community_Model_UserParams();
                            $langParam = $param->getParam($toID, 'lang');
                            if ($langParam != FALSE){
                                $sb = $this->view->translate('%s have removed you from friends on night2step.',$langParam);
                            } else {
                                $sb = $this->view->translate('%s have removed you from friends on night2step.');
                            }

                            $subject = sprintf($sb,$nameMes);
                            $this->e_mail($toID,$nameMes,$dateMes,$avatMes,'',$linkMes,$subject);
                        }
                    }
                } elseif ($task == 'add'){
                    $nRQ = 0;
                    $actRq = $req->getFrRequest($id);
                    if (count($actRq)>0 && $actRq->connect_to == $userID){
                        $nRQ = $req->setFrRequest($actRq->connect_to, $actRq->connect_from, 0, 1, 1, $actRq->created);
                        $req->setStatusOK($id);
                        $recepMsg = new Community_Model_Msg();
                        $user = $profil->getUser($actRq->connect_from);
                        
                        if ($actRq->msg != '')
                            $recepMsg->setMsg($actRq->connect_from, $user->name, $userID, $actRq->msg, $actRq->created,1,1);
                        if ($nRQ > 0){
                            //$activeModel = new Community_Model_ActivitiesSave();
                            //$activeModel->setActiveFriends($actRq->connect_from, $actRq->connect_to);
                            if ($this->view->userIsOnline($actRq->connect_from) == FALSE){
                                $date = new Zend_Date();
                                $userFROM = $profil->getUser($userID);
                                $dateMes = $date->get(Zend_Date::DATETIME_SHORT);
                                $curimg = $this->view->simpleThumb($userID);
                                $avatMes = '<img width="54" height="54" src="'.$curimg.'" alt="'.$userFROM->name.'"/>';
                                $linkMes = $this->view->baseUrl().'community/index/profil/id/'.$userID;
                                $param = new Community_Model_UserParams();
                                $langParam = $param->getParam($actRq->connect_from, 'lang');
                                if ($langParam != FALSE){
                                    $sb = $this->view->translate('%s has confirmed you as a friend on night2step.',$langParam);
                                } else {
                                    $sb = $this->view->translate('%s has confirmed you as a friend on night2step.');
                                }
                                $subject = sprintf($sb,$userFROM->name);
                                
                                $this->e_mail($actRq->connect_from,$userFROM->name,$dateMes,$avatMes,'',$linkMes, $subject);
                            }
                            $result = array('error'=>FALSE);
                        } else {
                            $result = array('error'=>true,'message'=>'Error');
                        }
                    } else {
                        $result = array('error'=>true,'message'=>'Error');
                    }
                } elseif ($task == 'restore'){
                    $ajax->delAction($userID, $id, 'friendreq');
                    $result = array('error'=>FALSE);
                } elseif ($task == 'reject'){
                    $ajax->setAction($userID, $id, 'friendreject');
                    $result = array('error'=>FALSE);
                } elseif ($task == 'restorereject'){
                    $ajax->delAction($userID, $id, 'friendreject');
                    $result = array('error'=>FALSE);
                } elseif ($task == 'runaction'){
                    $actions = $ajax->getActions($userID, 'friendreq');
                    $acR = $ajax->getActions($userID, 'friendreject');
                    if (count($acR) > 0) {
                        $noti = new Community_Model_Notifications();
                        foreach ($acR as $act){
                            if ($act->action == '1'){
                                $curReq = $req->getFrRequest($act->objectid);
                                $content = '{actor} has reject your friendship request.';
                                $noti->setUserNoti($userID, $curReq->connect_from, $content, 'frrequest');
                                $req->delFrRequest($act->objectid);
                            }
                        }
                        $ajax->delActions($userID, 'friendreject');
                    }
                    if (count($actions) > 0) {
                        foreach ($actions as $act){
                            if ($act->action == '1'){
                                $req->delFrRequest($act->objectid);
                            }
                        }
                        $ajax->delActions($userID, 'friendreq');
                    }
                } elseif ($task == 'check') {
                    $req_to = $req->getFrRequest_to($userID);
                    if (count($req_to) > 0){
                        $lhtml = '<div id="frLST" class="LST">';
                        foreach ($req_to as $m){
                            $date = new Zend_Date($m->created);
                            $curuser = $profil->getUser($m->connect_from);
                            $curimg = $this->view->simpleThumb($curuser->userid);
                            $cIMG = '<div style="float:left; margin-top: 5px;"><img width="45" height="45" src="'.$curimg.'" alt=""/></div>';
                            $lhtml .= '<a href="'.$this->view->url(array("module"=>"community","controller"=>"friends","action"=>"index"),'default', true).'">';
                            $lhtml .= '<div class="LSTBody"';
                            if($m->ajax_read == 0){
                                $lhtml .= ' style="background-color: #EEEEEE;"';
                                $req->setAjaxRead($m->connection_id);
                            }
                            $lhtml .= '>'.$cIMG.'<div style="margin: 5px; width: 215px;">';
                            $lhtml .= '<div  style="width:100%;">'.$curuser->name.'</div>';
                            $lhtml .= '<div  style="width:100%;font-weight: normal;">'.$this->view->shortText($this->view->escape($m->msg),100).'</div>';
                            $lhtml .= '<div  style="width:100%;" class="small">'.$date->get(Zend_Date::DATETIME_SHORT).'</div></div></div>';
                            $lhtml .= '</a>';
                        }
                        $lhtml .= '</div>';
                        $result = array('error'=>FALSE,'html'=>$lhtml);
                    }else {
                        $result = array('error'=>TRUE,'message'=>'<div id="ERMSG" style="text-align:center;width: 300px;">'.$this->view->translate('You have no friendship requests.').'</div>');
                    }
                } else {
                    $result = array('error'=>true,'message'=>'Error');
                }
            }   
        }
        if (isset($result))
            $this->_helper->json($result);
    }
    
    private function e_mail($to,$nameMes,$dateMes,$avatMes,$bodyMes,$linkMes,$subject)
    {
        $ehtml = '<table cellspacing="0" cellpadding="8" style="width: 620px; border-collapse: collapse;"><tbody><tr><td style="padding-top: 30px;">';
        $ehtml .= $subject;
        $ehtml .= '</td></tr><tr><td style="border-bottom: 1px solid rgb(233, 233, 233);"><table cellspacing="0" cellpadding="0" style="border-collapse: collapse;width: 605px;"><tbody><tr><td valign="top" bgcolor="#FFFFFF" width="60"><a style="color:#CC0000;text-decoration:none;font-weight:bold" href="';
        $ehtml .= $linkMes.'">';
        $ehtml .= $avatMes;
        $ehtml .= '</a></td><td><table cellspacing="0" cellpadding="8" style="width: 505px; border-collapse: collapse;"><tbody><tr><td><a target="_blank" href="';
        $ehtml .= $linkMes;
        $ehtml .= '" style="color:#CC0000;text-decoration:none;font-weight:bold">';
        $ehtml .= $nameMes;
        $ehtml .= '</a></td><td  align="right" style="padding-right: 5px; color: rgb(153, 153, 153);">';
        $ehtml .= $dateMes;
        $ehtml .= '</td></tr></tbody></table><div style="color: #333333;width:500px;word-wrap:break-word;padding:0 0 7px 7px;">';
        $ehtml .= $bodyMes;
        $ehtml .= '</div></td></tr></tbody></table></td><td></td></tr></tbody></table>';
        $this->view->eMail($to,$subject,$ehtml);
    }
}
