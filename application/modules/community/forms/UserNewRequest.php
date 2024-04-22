<?php

/**
 * UserNewRequest.php
 * Description of UserNewRequest
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 29.09.2012 15:51:27
 * 
 */
class Community_Form_UserNewRequest extends Zend_Form
{    
    public function init() 
    {
        if (Zend_Registry::isRegistered('TABLE_PREFIX')) {
            $pref = Zend_Registry::get('TABLE_PREFIX');
        } else {
            $pref = '';
        }
        
        $this->setMethod('post')
                ->setName('newRequest');
        $db_lookup_validator = new Zend_Validate_Db_RecordExists($pref.'users', 'email');
        $emailAddress = new N2S_Validate_EmailAddress();
        $user = new Zend_Form_Element_Text('login_user', array('label' => 'Your e-mail address', 'required' => true));
        $user->addFilters(array('StringTrim', 'StripTags'))
                ->addValidator($emailAddress,  TRUE)
                ->addValidator($db_lookup_validator,  TRUE);
        
        $submit = new Zend_Form_Element_Submit('submit', array('label' => 'Send'));
        
        $this->addElements(array($user,$submit));
    }
}
