<?php

/**
 * SettingEMail.php
 * Description of SettingEMail
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 27.02.2013 21:19:45
 * 
 */
class Community_Form_SettingEMail extends Zend_Form
{
    public function init() 
    {
        if (Zend_Registry::isRegistered('TABLE_PREFIX')) {
            $pref = Zend_Registry::get('TABLE_PREFIX');
        } else {
            $pref = '';
        }
        
        $this->setMethod('post')
                ->setName('email')
                ->setAction('/community/index/ajax/task/email');
        
        $db_lookup_validator = new Zend_Validate_Db_NoRecordExists($pref.'users', 'email');
        $emailAddress = new N2S_Validate_EmailAddress();
        $email = new Zend_Form_Element_Text('user_email', array('label' => 'E-Mail', 'required' => true));
        $email->addFilters(array('StringTrim', 'StripTags'))
                ->addValidator($emailAddress,  TRUE)
                ->addValidator($db_lookup_validator,  TRUE);
        
        $save = new Zend_Form_Element_Submit('submit');
        $save->setRequired(false)
                ->setIgnore(true)
                ->setLabel('save')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem left'))
                ->class = 'button special';

        $cancel = new Zend_Form_Element_Submit('cancel');
        $cancel ->setRequired(false)
                ->setIgnore(true)
                ->setLabel('cancel')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem'))
                ->setAttrib('onclick', 'sett.reset();return false;')
                ->class = 'button special';
        
        $this->addElements( array (
                            $email,$save,$cancel
                            )
                );
    }
}
