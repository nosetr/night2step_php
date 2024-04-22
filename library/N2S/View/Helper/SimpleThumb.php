<?php

/**
 * SimpleThumb.php
 * Description of SimpleThumb
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.10.2012 14:02:22
 * 
 */
class N2S_View_Helper_SimpleThumb extends Zend_View_Helper_Abstract
{
    function simpleThumb($userID)
    {
        $profil = new Community_Model_Users();
        $user = $profil->getUser($userID);
        $photos = new Default_Model_Photos();
        $thumb = $photos->getPhotoID($user->avatar);
        if($user){
            if ($user->avatar > 0 && file_exists($thumb->thumbnail)){
                $curimg = $thumb->thumbnail;
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
                    $curimg = 'images/avatar/default/'.$gender.'_thumb.jpg';
                } elseif($user->type == 'venue') {
                    $curimg = 'images/marker.png';
                } else {
                    $curimg = 'images/no-photo-thumb.png';
                }
            }
            return $this->view->baseUrl().$curimg;
        }
    }
}
