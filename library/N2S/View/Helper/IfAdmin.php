<?php

/**
 * IfAdmin.php
 * Description of IfAdmin
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 30.01.2013 12:03:00
 * 
 */
class N2S_View_Helper_IfAdmin extends Zend_View_Helper_Abstract
{
    function ifAdmin($creatorID,$creatorTYP)
    {
        $html = '';
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity() && $auth->getIdentity()->userid != $creatorID){
            $admins = new Community_Model_Admins();
            $admin = $admins->getCuruser($creatorID, $creatorTYP, FALSE);
            if($admin == $creatorID){
                $users = new Community_Model_Users();
                $user = $users->getUser($creatorID);
                if(isset($user)){
                    if($user->type == 'profil'){
                        $opt = 'resetprofil';
                    } else {
                        $opt = 'changeprofil';
                    }
                    $html = '<div id="'.$creatorID.'" repl="repl" opt="'.$opt.'" class="viewNotButton switchtoad" onclick="javascript:n2s.access.change(this);" style="display:none;margin:0 10px 25px 0;">';
                    $html .= '<div style="text-align:center;">';
                    $html .= sprintf($this->view->translate('You are admin of this page. Switch to %s for edit.'),'<b>'.$user->name.'</b>');
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<script charset="utf-8" type="text/javascript">$(document).ready(function(){$(".switchtoad").fadeIn("slow");});</script>';
                }
            }
        }
        return $html;
    }
}
