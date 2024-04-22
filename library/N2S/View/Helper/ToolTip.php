<?php

/**
 * ToolTip.php
 * Description of ToolTip
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 17.10.2012 19:02:22
 * 
 */
class N2S_View_Helper_ToolTip extends Zend_View_Helper_Abstract
{
    function toolTip($title,$userID, $bild = 0, $action = 'profil')
    {
        if ($action != 'profil'){
            $html = $this->toolTipCommonFriends($userID);
        }else{
            $auth = Zend_Auth::getInstance();
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);

            $photos = new Default_Model_Photos();
            $thumb = $photos->getPhotoID($user->avatar);
            
            if(($bild == 0) && $user->avatar > 0 && (file_exists($this->view->escape($thumb->thumbnail)))){
                $html = '<div class="tTip-info">';
            } else {
                $html = '';
            }
            $html .= '<b>'.$title.'</b><br/>';
            
            if ($auth->hasIdentity()){
                if ($userID != $auth->getIdentity()->userid){
                    $friends = $this->commonFriends($userID);


                    $html .= $this->friendship($userID);
                    if (0 < count($friends)){
                        $count = count($friends);

                        if ($count == 1){
                            $html .= sprintf($this->view->translate('%d common friend'), $count);
                        } else {
                            $html .= sprintf($this->view->langHelper('%d common friends',$count), $count);
                        }
                    }
                    if($this->view->userIsOnline($userID) == TRUE)
                        $html .= '<br/><b>(&nbsp;online&nbsp;)</b>';
                } else {
                    $html .= $this->view->translate('that is you');
                }
            }

            $thumb = $photos->getPhotoID($user->avatar);
            if(($bild == 0) && $user->avatar > 0 && (file_exists($this->view->escape($thumb->thumbnail)))){
                $html .= '</div>';
                $html .= '<img style="width:65px;" src="'.$thumb->thumbnail.'" alt=""/>';
            }
        }
        return $this->view->escape($html);
    }
    
    function toolTipCommonFriends($userID)
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()){
            $frReqM = new Community_Model_FrRequest();
            $tumbs = $frReqM->getCommonFriends($userID, $auth->getIdentity()->userid);
            if (count($tumbs) > 0){
                shuffle($tumbs); // Reienfolge zufälig ändern
                $tumbs = array_slice($tumbs, 0, 5);   // liefert erste 5
                $profil = new Community_Model_Users();
                $photos = new Default_Model_Photos();
                $html = '';
                foreach ($tumbs as $t) {
                    $user = $profil->getUser($t);
                    $thumb = $photos->getPhotoID($user->avatar);
                    if ($user->avatar >0 && file_exists($this->view->escape($thumb->thumbnail))){
                        $img = $thumb->thumbnail;
                    } else {
                        switch ($user->gender){
                            case "m":
                                $gender = "male";
                                break;
                            case "f":
                                $gender = "female";
                                break;
                            default:
                                $gender = "default";
                        }
                        $img = 'images/avatar/default/'.$gender.'_thumb.jpg';
                    }
                    $html .= '<img class="thumb-avatar-small" src="'.$img.'" alt=""/>';
                }
                return $html;
            }
        }
    }
    
    public function friendship($userID)
    {
        $auth = Zend_Auth::getInstance();
        $model = new Community_Model_FrRequest();
        $friends = $model->checkFrRequest($auth->getIdentity()->userid, $userID, 1);
        if (count($friends) > 0){
            return $this->view->translate('already your friend').'<br/>';
        }
    }
    
    public function commonFriends($userID)
    {
        $auth = Zend_Auth::getInstance();
        $comModel = new Community_Model_FrRequest();
        $friends = $comModel->getCommonFriends($userID, $auth->getIdentity()->userid);
            
        return $friends;
    }
}
