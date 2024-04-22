<?php

/**
 * UserIsOnline.php
 * Description of UserIsOnline
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 10:01:05
 * 
 */
class N2S_View_Helper_UserIsOnline extends Zend_View_Helper_Abstract
{
    function userIsOnline($userID)
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity() && $userID == $auth->getIdentity()->userid) {
            $online = FALSE;
        } else {
            $profil = new Community_Model_Users();
            $user = $profil->getUser($userID);
            $oVonDatum = $user->lastvisitDate;
            $date = Zend_Date::now();
            $oBisDatum = $date->get(Zend_Date::TIMESTAMP);
            $time = floor(($oBisDatum-$oVonDatum) / 60);
            if($time < 1.5){
                $online = TRUE;
            } else {
                $online = FALSE;
            }
        }
        return $online;
    }
}