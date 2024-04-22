<?php

/**
 * UserWriteMessage.php
 * Description of UserWriteMessage
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 14.11.2013 18:22:22
 * 
 */
class N2S_View_Helper_UserWriteMessage extends Zend_View_Helper_Abstract
{
    function userWriteMessage()
    {
        $html = '';
        $userID = N2S_User::curuser();
        $frReqModel = new Community_Model_FrRequest();
        $friends = $frReqModel->getFriendsList($userID);
        
        if(count($friends) > 0){
            $profil = new Community_Model_Users();
            $html .= '<div class="n2Module"><div class="n2s-frList">';
            $html .= '<h3>'.$this->view->translate('Friends').':</h3><ul style="max-height: 600px; overflow: auto;">';
            foreach ($friends as $f) {
                $fUser = $profil->getUser($f->connect_from);
                if(isset($fUser) && $fUser->deactivated == '0'){
                    $html .= '<li class="newsfeed-item">';
                    $html .= '<div class="newsfeed-avatar">'.$this->view->userThumb($f->connect_from,1,0).'</div>';
                    $html .= '<div class="newsfeed-content">';
                    $html .= '<div clas="newsfeed-content-top"><a class="black" href="'.$this->view->userLink($f->connect_from).'">'.$fUser->name.'</a></div>';
                    $html .= '<div class="newsfeed-meta small">';
                    $html .= '<a class="red ajaxlink" style="float:left;background: url(/images/mail.png) no-repeat scroll 7px 3px #ffffff; padding: 2px 10px 2px 25px;" href="';
                    $html .= $this->view->url(array("module"=>"community",
                                    "controller"=>"messages",
                                    "action"=>"read",
                                    "view"=>$f->connect_from),'default', true);
                    $html .= '">';
                    $html .= $this->view->translate('send a message').'</a>';
                    $html .= '</div></div></li>';
                }
            }
            $html .= '</ul></div></div>';
        } else {
            $html .= $this->view->userMaybeFriends();
        }
        
        return $html;
    }
}
