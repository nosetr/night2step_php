<?php

/**
 * MessageController.php
 * Description of MessageController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.01.2013 20:30:38
 * 
 */
class Default_MessageController extends Zend_Controller_Action
{
    public function init()
    {
        
    }
    
    public function indexAction()
    {
        $this->_helper->layout()->disableLayout();
        $to = (int)$this->_request->getParam( 'id',0 );
        $task = (string)$this->_request->getParam( 'task' );
        $auth = Zend_Auth::getInstance();
        if($to > 0){
            $profil = new Community_Model_Users();
            $user = $profil->getUser($to);
        }
        if ($to == 0 || !isset($user) || ($auth->hasIdentity() && ($auth->getIdentity()->userid == $to || $auth->getIdentity()->type != 'profil'))) {
            $this->_forward('notfound', 'Error', 'default');
        }
        $onclick = '';
        if (!$auth->hasIdentity()) {
            $d = '<a href="'.$this->view->url(array("module"=>"community"), 'default', true).'">';
            $s = '</a>';
            $a = $this->view->translate('you must %1$s login %2$s first');
            $html = '<div class="ajaxlink" style="text-align: center;">'.sprintf($a,$d,$s).'</div>';
        } else {
            $frModel = new Community_Model_FrRequest();
            $checkFr = $frModel->checkIfFriend($auth->getIdentity()->userid,$to);
            if ($task == 'frreq'){
                if($checkFr == TRUE){
                    $html = '<div class="ajaxlink" style="text-align: center;">'.$this->view->translate('already your friend').'</div>';
                } else {
                    $checkReq = $frModel->checkFrRequest($auth->getIdentity()->userid,$to);
                    if (count($checkReq) > 0){
                        $html = '<div class="ajaxlink" style="text-align: center;">'.$this->view->translate('Friendship request is already exist.').'</div>';                        
                    } else {
                        $succMess = 'Your request was successfully delivered.';
                        $placeholder = 'you can also write a message ...';
                        $send = 'send request';
                        $onclick = '$(function(){';
                        $onclick .= '$("#im_send_button").click(function(){';
                        $onclick .= 'var txt = $("#messText").val();';
                        $onclick .= '$("#ajaxload").show();';
                        $onclick .= '$("#n2s-mForm").hide();';
                        $onclick .= '$.getJSON("';
                        $onclick .= $this->view->url(array(
                                        "module"=>"community",
                                        "controller"=>"friends",
                                        "action"=>"ajax",
                                        "task"=>"send"),'default', true);
                        $onclick .= '",{user: '.$to.',msg: txt},';
                        $onclick .= 'function(data){';
                        $onclick .= 'if(data.error){';
                        $onclick .= '$("#ajaxload").hide();';
                        $onclick .= '$("#send-message").empty().show().append(data.message);';
                        $onclick .= 'setTimeout(function(){';
                        $onclick .= 'parent.$.n2lbox.close();';
                        $onclick .= '},1000);';
                        $onclick .= '}else{';
                        $onclick .= '$("#ajaxload").hide();';
                        $onclick .= '$("#send-message").show();';
                        //$onclick .= '$("#sFrRq"+'.$to.').remove();';
                        $onclick .= 'setTimeout(function(){';
                        $onclick .= 'parent.$.n2lbox.close();';
                        $onclick .= 'n2s.noti.changeVal("#sFrRq'.$to.'","<span id=\"unfrMessSuc\"><b>'.$this->view->translate('Your request was successfully delivered.').'</b></span>");';
                        $onclick .= '},1000);';
                        $onclick .= '}';
                        $onclick .= '});';
                        $onclick .= '});';
                        $onclick .= '});';

                        $html = '<div id="send-message" class="ajaxlink" style="display: none;text-align: center;">';
                        $html .= $this->view->translate($succMess).'</div><div id="n2s-mForm"><textarea id="messText" cols="1" rows="1" ';
                        $html .= 'style="margin-bottom: 4px; width: 95%; height: 150px;" ';
                        $html .= 'name="comment" placeholder="'.$this->view->translate($placeholder).'"></textarea>';
                        $html .= '<button id="im_send_button" style="font-size: inherit; font-weight: bold; padding: 2px 10px;">'.$this->view->translate($send).'</button></div>';
                    }
                }
            } elseif ($task == 'unfriend') {
                $succMess = 'Friendship was terminated.';
                
                $onclick = '$(function(){';
                $onclick .= '$("#im_send_button").click(function(){';
                $onclick .= '$("#ajaxload").show();';
                $onclick .= '$("#n2s-mForm").hide();';
                $onclick .= '$.getJSON("';
                $onclick .= $this->view->url(array(
                                "module"=>"community",
                                "controller"=>"friends",
                                "action"=>"ajax",
                                "task"=>"unfriend"),'default', true);
                $onclick .= '",{user: '.$to.'},';
                $onclick .= 'function(data){';
                $onclick .= 'if(data.error){';
                $onclick .= '$("#ajaxload").hide();';
                $onclick .= '$("#send-message").empty().show().append(data.message);';
                $onclick .= 'setTimeout(function(){';
                $onclick .= 'parent.$.n2lbox.close();';
                $onclick .= '},1000);';
                $onclick .= '}else{';
                $onclick .= '$("#ajaxload").hide();';
                $onclick .= '$("#send-message").show();';
                $onclick .= 'setTimeout(function(){';
                $onclick .= 'parent.$.n2lbox.close();';
                $onclick .= 'n2s.noti.changeVal("#sFrRq'.$to.'","<span id=\"unfrMessSuc\"><b>'.$this->view->translate($succMess).'</b></span>");';
                $onclick .= '},1000);';
                $onclick .= '}';
                $onclick .= '});';
                $onclick .= '});';
                $onclick .= '});';
                
                $html = '<div id="send-message" class="ajaxlink" style="display: none;text-align: center;">';
                $html .= $this->view->translate($succMess).'</div><div id="n2s-mForm"><div style="margin:40px 0 15px;text-align: center;">'.$this->view->translate('Du you realy want to terminate friendship?').'</div>';
                $html .= '<div style="text-align: center;"><button id="im_send_button" style="font-size: inherit; font-weight: bold; padding: 2px 10px;">'.$this->view->translate('unfriend').'</button></div></div>';
            } else {
                $this->_forward('notfound', 'Error', 'default');
            }
        }
        
        $this->view->html = $html;
        $this->view->to = $onclick;
    }
            
    public function sendAction()
    {
        $auth = Zend_Auth::getInstance();
        $userTo = (int)$this->_request->getParam('uid',0);
        if ($auth->hasIdentity() && $userTo > 0 && $this->_request->isXmlHttpRequest()) {
            $this->_helper->layout()->disableLayout();
            if($auth->getIdentity()->userid == $userTo){
                $onclick = '';
                $html = '<div style="margin-top:15%;text-align:center;"><b>';
                $html .= $this->view->translate('You can not send a message to himself.');
                $html .= '</b></div>';
            } else {
                $profil = new Community_Model_Users();
                $user = $profil->getUser($userTo);
                if(!isset($user) || (isset($user) && $user->deactivated == 1)){
                    $onclick = '';
                    $html = '<div style="margin-top:15%;text-align:center;"><b>';
                    $html .= $this->view->translate('User you requested was not found.');
                    $html .= '</b></div>';
                } else {
                    $succMess = 'Your message was successfully delivered.';
                    $placeholder = 'write a message ...';
                    $send = 'send';
                    $onclick = '$(function () {';
                    $onclick .= '$("#im_send_button").click(function(){';
                    $onclick .= 'var txt = $("#messText").val();';
                    $onclick .= 'if (txt == ""){';
                    $onclick .= '$("#messText").focus();';
                    $onclick .= '} else {';
                    $onclick .= '$("#ajaxload").show();';
                    $onclick .= '$("#n2s-mForm").hide();';
                    $onclick .= '$.getJSON("/community/messages/ajax/task/send",{user: '.$userTo.',msg: txt},';
                    $onclick .= 'function(data){';
                    $onclick .= 'if(data.error){';
                    $onclick .= '$("#ajaxload").hide();';
                    $onclick .= '$("#send-message").empty().show().append(data.message);';
                    $onclick .= '} else {';
                    $onclick .= '$("#ajaxload").hide();';
                    $onclick .= '$("#send-message").show();';
                    $onclick .= 'setTimeout(function(){';
                    $onclick .= 'parent.$.n2lbox.close();';
                    $onclick .= '},1000);}});}});});';
                    $html = '<div id="send-message" class="ajaxlink" style="display: none;text-align: center;">';
                    $html .= $this->view->translate($succMess).'</div><div id="n2s-mForm"><textarea id="messText" cols="1" rows="1" ';
                    $html .= 'style="margin-bottom: 4px; width: 95%; height: 150px;" ';
                    $html .= 'name="comment" placeholder="'.$this->view->translate($placeholder).'"></textarea>';
                    $html .= '<button id="im_send_button" style="font-size: inherit; font-weight: bold; padding: 2px 10px;">'.$this->view->translate($send).'</button></div>';
                }
            }
            $this->view->html = $html;
            $this->view->to = $onclick;
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }
    }
}