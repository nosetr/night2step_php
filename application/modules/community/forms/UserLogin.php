<?php

/**
 * UserLogin.php
 * Description of UserLogin
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 19.09.2012 17:23:40
 * 
 */
class Community_Form_UserLogin extends Zend_Form
{    
    public function init() 
    {
        if (Zend_Registry::isRegistered('TABLE_PREFIX')) {
            $pref = Zend_Registry::get('TABLE_PREFIX');
        } else {
            $pref = '';
        }
        
        $this->setMethod('post')
                ->setName('userLogin');
        
        $db_lookup_validator = new Zend_Validate_Db_RecordExists($pref.'users', 'email');
        $emailAddress = new N2S_Validate_EmailAddress();
        $user = new Zend_Form_Element_Text('login_user', array('label' => 'E-Mail', 'required' => true));
        $user->addFilters(array('StringTrim', 'StripTags'))
                ->addValidator($emailAddress,  TRUE)
                ->addValidator($db_lookup_validator,  TRUE);
        
        $confirm_password = new N2S_Validate_Password();
        $password = new Zend_Form_Element_Password('login_password', array('label' => 'Password', 'required' => true));
        $password->addValidator($confirm_password);
        
        $rememberme = new Zend_Form_Element_Checkbox('login_rememberme', array('label' => 'Remember me'));
        $rememberme->setChecked(TRUE)
                ->setCheckedValue('Checked')
                ->setUnCheckedValue('UnChecked');
        
        $submit = new Zend_Form_Element_Submit('submit', array('label' => 'Login'));
        
        /*
        $csrf = $this->createElement('hash','csrf', array('salt' => 'unique'));
        $csrf->setIgnore(TRUE);
        $csrf->addErrorMessage('The session has expired. Try again.');
         * 
         */
        
        $this->addElements(array($user, $password, $rememberme, $submit));
    }
}