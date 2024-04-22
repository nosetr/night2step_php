<?php

/**
 * UserLink.php
 * Description of UserLink
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 29.01.2013 20:38:08
 * 
 */
class N2S_View_Helper_UserLink extends Zend_View_Helper_Abstract
{
    function userLink($userID)
    {
        $users = new Community_Model_Users();
        $user = $users->getUser($userID);
        $type = $user->type;
        switch ($type){
            case 'venue':
                $admins = new Default_Model_Adresses();
                $admin = $admins->getAdressWithCreator($userID);
                $userID = $admin->id;
                $link = $this->view->url(array("module"=>"default","controller"=>"venues","action"=>"show","id"=>$userID), 'default', true);
                break;
            default :
                $link = $this->view->url(array("module"=>"community","controller"=>"index","action"=>"profil","id"=>$userID), 'default', true);
        }
        
        return $link;
    }
}
