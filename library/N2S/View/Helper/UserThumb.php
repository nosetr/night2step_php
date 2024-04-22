<?php

/**
 * UserThumb.php
 * Description of UserThumb
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 09:59:05
 * 
 */
class N2S_View_Helper_UserThumb extends Zend_View_Helper_Abstract
{
    function userThumb($userID,$ajax = 1, $tooltip = 1, $showOnline = true)
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity() && $userID == $auth->getIdentity()->userid) {
            $showOnline = FALSE;
        }
        $profil = new Community_Model_Users();
        $user = $profil->getUser($userID);
        if(isset($user)){
            $photos = new Default_Model_Photos();
            $file = $photos->getPhotoID($user->avatar);
            if ($user->avatar > 0 && $file && file_exists($file->thumbnail)) {
                $img = $file->thumbnail;
            } else {
                if($user->type == 'profil'){
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
                } elseif($user->type == 'venue') {
                    $img = '/images/marker.png';
                } else {
                    $img = '/images/no-photo-thumb.png';
                }
            }
            
            if($showOnline == true && $this->view->userIsOnline($userID) == TRUE):
                $onlinestatus = 'online';
            else: $onlinestatus = '';
            endif;
            
            $html  = '<div class="n2s-thumb">';
            $html .= '<a href="'.$this->view->userLink($userID).'">';
            $html .= '<img class="thumb-avatar';
            if ($tooltip == 1){
                $html .= ' n2s-tooltip" title="'.$this->view->toolTip($user->name,$userID);
            }
            if ($ajax == 0) {
                $html .= '" alt="'.$user->name.'" src="images/grey.gif" data-original="'.$img.'" width="64" height="64"/>';
            } else {
                $html .= '" alt="'.$user->name.'" src="'.$img.'" width="64" height="64"/>';
            }
            $html .= '</a>';
            $html .= '<b>'.$onlinestatus.'</b>';
            
            
            $html .= '</div>';

            return $html;
        }
    }
}