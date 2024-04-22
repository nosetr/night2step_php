<?php

/**
 * UserPoints.php
 * Description of UserPoints
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 11.04.2013 10:05:36
 * 
 */
class Community_Model_UserPoints
{
    //function "admin" musst be deleted
    public function admin($target,$task,$action,$userID)
    {
        $users = new Community_Model_Users();
        $user = $users->getUser($userID);
        if(isset($user)){
            $content = '_'.$action;
            $config = Zend_Registry::get('config')->upoint->$target->$task;
            if(isset($config) && method_exists($this, $content)){
                $this->$content($userID,$config,$user->points);
            }
        }
    }
    
    public function point($target,$task,$action)
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity() && isset($target) && isset($task) && isset($action)) {
            $userID = $auth->getIdentity()->userid;
            $users = new Community_Model_Users();
            $user = $users->getUser($userID);
            if(isset($user)){
                $content = '_'.$action;
                $config = Zend_Registry::get('config')->upoint->$target->$task;
                if(isset($config) && method_exists($this, $content)){
                    $this->$content($userID,$config,$user->points);
                }
            }
        }
    }
    
    private function _set($userID,$point,$points)
    {
        $data = array('points'=>$points + $point);
        $users = new Community_Model_Users();
        $users->updateProfil($userID, $data);
    }
}
