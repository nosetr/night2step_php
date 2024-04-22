<?php

/**
 * Permissions.php
 * Description of Permissions
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 03.12.2012 17:53:51
 * 
 */
class Community_Model_Permissions
{
    public function checkPermissions($creator,$permis)
    {
        if($permis == 0){
            return TRUE;
        } else {
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                if($permis == 20){
                    return TRUE;
                } else {
                    $curuser = $auth->getIdentity()->userid;
                    if($curuser == $creator){
                        return TRUE;
                    } else {
                        $check = new Community_Model_FrRequest();
                        $result = $check->checkIfFriend($curuser, $creator);
                        return $result;
                    }
                }
            } else {
                return FALSE;
            }
        }
    }
    
    public function getPermissions($creator,$user = TRUE)
    {
        $result = -1;
        
        $profil = new Community_Model_Users();
        $checkuser = $profil->getUser($creator);
        if(isset($checkuser) && $checkuser->deactivated != '1')
            $result = 0;
            
        
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            if($user == TRUE)
                $user = $auth->getIdentity()->userid;
            if($user == $creator){
                $result = 50;
            } else {
                $check = new Community_Model_FrRequest();
                $fr = $check->checkIfFriend($user, $creator);
                if($fr == TRUE){
                    $result = 40;
                } else {
                    $result = 20;
                }
            }
        }
        
        return $result;
    }
}
