<?php

/**
 * FriendRequest.php
 * Description of FriendRequest
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 19.01.2013 22:20:51
 * 
 */
class N2S_View_Helper_FriendRequest extends Zend_View_Helper_Abstract
{
    function friendRequest($userID,$style = NULL,$unfriend = FALSE)
    {
        $html = '';
        $users = new Community_Model_Users();
        $user = $users->getUser($userID);
        if(isset($user) && $user->type == 'profil'){
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity() && $auth->getIdentity()->type == 'profil') {
                $curuser = $auth->getIdentity()->userid;
                if($userID != $curuser){
                    $frReqModel = new Community_Model_FrRequest();
                    $IfFriend = $frReqModel->checkIfFriend($userID, $curuser);
                    if($IfFriend == FALSE){
                        $html .= '<div id="sFrRq'.$userID.'">';
                        $frRequest = $frReqModel->checkFrRequest($userID, $curuser);
                        if(count($frRequest) > 0){
                            $html .= '<div class="fr-deliv" style="'.$style.'">';
                            $html .= $this->view->translate('friendship request was delivered').'</div>';
                        } else {
                            $linkArray = array("module"=>"default",
                                                "controller"=>"message",
                                                "action"=>"index",
                                                "id"=>$userID,
                                                "task"=>"frreq");
                            $html .= '<a class="red n2s-message n2lbox.ajax ajaxlink" href="';
                            $html .= $this->view->url($linkArray,'default', true);
                            $find   = 'padding';
                            $pos = strpos($style, $find);
                            if ($pos === false) {
                                $style = $style.'padding: 2px 10px;';
                            }
                            $html .= '" style="'.$style.'">';
                            $html .= $this->view->translate('friendship request').'</a>';
                        }
                        $html .= '</div>';
                    } else{
                        if($unfriend == TRUE) {
                            $linkArray = array("module"=>"default",
                                                "controller"=>"message",
                                                "action"=>"index",
                                                "id"=>$userID,
                                                "task"=>"unfriend");
                            $html .= '<div id="sFrRq'.$userID.'"><a class="red n2s-message n2lbox.ajax" href="';
                            $html .= $this->view->url($linkArray,'default', true);
                            $html .= '"><div class="ajaxlink" style="'.$style.'padding: 2px 10px;">';
                            $html .= $this->view->translate('friendship deny').'</div></a></div>';
                        } else {
                            $html .= '<div class="fr-deliv" style="'.$style.'">';
                            $html .= $this->view->translate('your friend').'</div>';
                        }
                    }
                    $html .= '<div class="clear"></div>';
                }
            }
        }
        return $html;
    }
}
