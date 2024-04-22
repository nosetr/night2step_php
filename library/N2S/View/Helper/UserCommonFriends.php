<?php

/**
 * UserCommonFriends.php
 * Description of UserCommonFriends
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 21.01.2013 09:08:10
 * 
 */
class N2S_View_Helper_UserCommonFriends extends Zend_View_Helper_Abstract
{
    function userCommonFriends($userID)
    {
        $html = '';
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity() && $auth->getIdentity()->type == 'profil'){
            if($userID == N2S_User::curuser()){
                $html .= '<div><b>'.$this->view->translate('that is you').'</b></div>';
            } else {
                $profil = new Community_Model_Users();
                $frReqModel = new Community_Model_FrRequest();
                $friends = $frReqModel->getCommonFriends($userID, $auth->getIdentity()->userid);

                if (0 < count($friends)){
                    $list = array();
                    foreach ($friends as $fr){
                        $list[] = $fr;
                    }
                    $friends = $profil->getUsersInList($list,TRUE);
                    
                    if (0 < count($friends)){
                        $html .= '<div>';
                        $html .= '<a class="n2s-userlist n2lbox.ajax black" class="n2s-userlist n2lbox.ajax" href="';
                        $html .= $this->view->url(array("module"=>"community",
                                "controller"=>"userlist",
                                "action"=>"index",
                                "view"=>"commonfriends","id"=>$userID),"default",true);
                        $html .= '">';
                        if (count($friends) == 1){
                            $html .= sprintf($this->view->translate('%d common friend'),count($friends));
                        } else {
                            $html .= sprintf($this->view->langHelper('%d common friends',count($friends)),count($friends));
                        }
                        $html .= ':</a></div>';

                        //$friends = array_slice($friends, 0, 4);//Kürzen bis 4
                        $c = 0;
                        foreach ($friends as $user){
                            if($c == 4)
                                break;
                            $curimg = $this->view->simpleThumb($user->userid);
                            $c++;
                            $html .= '<a href="'.$this->view->userLink($user->userid).'">';
                            $html .= '<img class="thumb-avatar-supersmall n2s-tooltip" title="'.$this->view->toolTip($user->name,$user->userid).'" src="'.$curimg.'" alt=""/></a>';
                        }
                    }
                }
            }
        }
            
        return $html;
    }
}
