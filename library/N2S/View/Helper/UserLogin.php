<?php

/**
 * UserLogin.php
 * Description of UserLogin
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 08:59:05
 * 
 */
class N2S_View_Helper_UserLogin extends Zend_View_Helper_Abstract
{
    function userLogin()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $profil = new Community_Model_Users();
            $userID = $auth->getIdentity()->userid;
            $type = $auth->getIdentity()->type;
            $user = $profil->getUser($userID);
            
            $img = $this->view->simpleThumb($userID);
            
            $lhtml = $this->view->notification($userID);
            /*$lhtml .= '<a href="'.$this->view->userLink($user->userid).'"  title="'.$this->view->translate('Account').'">';
            $lhtml .= '<div class="n2s-ushelpcont"><img class="thumb-avatar-supersmall" src="'.$img.'" alt=""/><span class="uhelperName">'
                    .$user->name.'</span></div></a>';
             * 
             */
            
            $admins = new Community_Model_Admins();
            $html = '';
            $count = 0;
            if($type == "profil"){
                $curuser = $userID;
            } else {
                $curuser = N2S_User::curuser();
                $user2 = $profil->getUser($curuser);
                $html .= '<a id="1" opt="resetprofil" onclick="javascript:n2s.access.change(this);" href="javascript:void(0);">';
                $html .= '<li class="url"><div><img class="thumb-avatar-supersmall" src="'.$this->view->simpleThumb($curuser).'" alt=""/><span>'.$user2->name.'</span></div></li>';
                $html .= '</a>';
                $count++;
            }
            if($curuser > 0){
                $all = $admins->findAllAccess($curuser);
                if(count($all) > 0){
                    $list = array();
                    foreach ($all as $r){
                        $list[] = $r->objectid;
                    }
                    $allU = $profil->getUsersInList($list,true);
                    foreach ($allU as $a){
                        if($userID != $a->userid){
                            $html .= '<a id="'.$a->userid.'" opt="changeprofil" onclick="javascript:n2s.access.change(this);" href="javascript:void(0);">';
                            $html .= '<li class="url"><div><img class="thumb-avatar-supersmall" src="'.$this->view->simpleThumb($a->userid).'" alt=""/><span>'.$a->name.'</span></div></li>';
                            $html .= '</a>';
                            $count++;
                        }
                    }
                }
                $html .= '<div style="width: 100%; height: 0px; border-bottom: 1px dotted rgb(153, 153, 153);"></div>';
            }
            ($count > 0)?$chProf = '<li style="color: rgb(153, 153, 153); font-weight: bold; font-style: italic; margin: 10px 10px 0px; padding: 0px;cursor: default;">'.$this->view->translate('Use night2step as:').'</li>'.$html:$chProf = '';
            
            $lhtml .= '<div class="n2s-edit" style="float:right;"><div class="n2s-editlink">EDIT</div><ul class="showcasten">';
            $lhtml .= $chProf;
            if($auth->getIdentity()->type == 'profil'){
                $lhtml .= '<a href="'.$this->view->url(array("module"=>"community","controller"=>"index","action"=>"profiledit"),"default",true).'">';
                $lhtml .= '<li class="url">'.$this->view->translate('Account settings').'</li></a>';
            }
            //$lhtml .= '<li class="url">'.$this->view->translate('Privacy Settings').'</li>';
            $lhtml .= '<a href="javascript:void(0);" onclick="javascript:n2s.access.logout();">';
            $lhtml .= '<li class="url">'.$this->view->translate('Logout').'</li></a>';
            //$lhtml .= '<li class="url">'.$this->view->translate('Help').'</li>';
            $lhtml .= '</ul></div>';
            
            $lhtml .= '<a href="'.$this->view->userLink($user->userid).'"  title="'.$this->view->translate('Account').'">';
            $lhtml .= '<div class="n2s-ushelpcont"><img class="thumb-avatar-supersmall" src="'.$img.'" alt=""/><span class="uhelperName">'
                    .$user->name.'</span></div></a>';
        }else{
            $module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
            ($module !== 'community')?
                $lhtml = '<div class="right"><a class="n2s-login n2lbox.ajax" href="'.$this->view->url(array("module"=>"community","controller"=>"index","action"=>"index","view"=>"login"),"default",true).'">'.$this->view->translate('Login').'</a>
                            <a href="'.$this->view->url(array("module"=>"community","controller"=>"index","action"=>"index"),"default",true).'">'.$this->view->translate('SignUp').'</a></div>':
                $lhtml = '';
        }
        return $lhtml;
    }
}