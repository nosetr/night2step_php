<?php

/**
 * HomeUsers.php
 * Description of HomeUsers
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.05.2013 22:54:35
 * 
 */
class N2S_View_Helper_HomeUsers extends Zend_View_Helper_Abstract
{
    function homeUsers()
    {
        $count = 8;
        $usersMod = new Community_Model_Users();
        $users = $usersMod->getHomeUsers($count);
        
        $html = '<div>';
        $html .= '<div class="n2Module">';
        $html .= '<h3 style="margin:0px;">'.$this->view->translate('New users').'</h3>';
        if(count($users) > 0){
            $list = array();
            foreach ($users as $user){
                $list[] = $user->userid;
            }
            $html .= '<div style="margin-top:10px;">';
            $html .= $this->view->userThumbList($list, $count);
            $html .= '</div>';
        }
        $html .= '</div><div class="clear"></div></div>';
        
        return $html;
    }
}
