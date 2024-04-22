<?php

/**
 * UserMaybeFriends.php
 * Description of UserMaybeFriends
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 10:54:59
 * 
 */
class N2S_View_Helper_UserMaybeFriends extends Zend_View_Helper_Abstract
{
    function userMaybeFriends()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity() && $auth->getIdentity()->type == 'profil') {
            $userID = $auth->getIdentity()->userid;
            $profil = new Community_Model_Users();
            $req = new Community_Model_FrRequest();
            //$fr = $req->getFriendsFriends($userID);
            $fr = $req->getMayBeFriends($userID);
            
            if (isset($fr) && count($fr) > 0) {

                $count = count($fr);

                $request = Zend_Controller_Front::getInstance()->getRequest();
                $action = $request->getActionName();
                $profid = (int)$request->getParam('id', 0);
                if ($action == 'profil' && $profid > 0){
                    $actuser = array($profid);
                    $fr = array_diff($fr, $actuser); // userfriends to array
                }

                shuffle($fr); // Reienfolge zufälig ändern
                $fr = array_slice($fr, 0, 2);   // liefert erste 2

                if (count($fr) > 0){
                    $html  = '<div class="n2Module"><div class="n2s-frList">';
                    $html .= '<h3>'.$this->view->translate('People you might know').':</h3><ul>';

                    foreach ($fr as $f) {
                        $fuser = $profil->getUser($f);
                        $comFr = count($req->getCommonFriends($userID, $f));

                        $html .= '<li class="newsfeed-item">';
                        $html .= '<div class="newsfeed-avatar">'.$this->view->userThumb($f,1,0).'</div>';
                        $html .= '<div class="newsfeed-content">';
                        $html .= '<div clas="newsfeed-content-top"><a class="black" href="'.$this->view->userLink($f).'">'.$fuser->name.'</a></div>';
                        $html .= '<div class="newsfeed-meta small">';
                        $html .= $this->view->friendRequest($f,'margin-bottom: 5px;');
                        $html .= '<a class="n2s-userlist n2lbox.ajax n2s-tooltip black" title="
                            '.$this->view->toolTip('',$f,0,'comFr').'" href="
                                '.$this->view->url(array("module"=>"community",
                                    "controller"=>"userlist",
                                    "action"=>"index",
                                    "view"=>"commonfriends",
                                    "id"=>$f),'default', true).'">';
                        if ($comFr == 1){
                            $html .= sprintf($this->view->translate('%d common friend'), $comFr);
                        } else {
                            $html .= sprintf($this->view->langHelper('%d common friends',$comFr), $comFr);
                        }
                        $html .= '</a>';
                        $html .= '</div></div></li>';
                    }
                    $html .= '</ul><div style="width:100%;float: left;text-align:right;"><a class="n2s-userlist n2lbox.ajax n2s-view" href="
                        '.$this->view->url(array("module"=>"community",
                            "controller"=>"userlist",
                            "action"=>"index",
                            "view"=>"maybefr",
                            "id"=>$userID),'default', true).'">
                                '.$this->view->translate('view all_friends').'&nbsp('.$count.')</a></div>';
                    $html .= '</div></div>';

                    return $html;
                }
            }
        }
    }
}