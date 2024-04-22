<?php

/**
 * SettingPass.php
 * Description of SettingPass
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 27.02.2013 21:29:34
 * 
 */
class Community_Form_SettingPass extends Zend_Form
{
    public function init() 
    {
        $this->setMethod('post')
                ->setName('password')
                ->setAction('/community/index/ajax/task/password')
                ->setAttrib('autocomplete', 'off');
        
        $confirm_old_password = new N2S_Validate_Password();
        $oldpassword = new Zend_Form_Element_Password('login_password', array('label' => 'Current', 'required' => true));
        $oldpassword->addValidator($confirm_old_password);

        $password = $this->createElement('password','user_password');
        $password->setLabel('New')
                ->setRequired(true)
                ->setDescription('*Min. 7 and max. 50 characters')
                ->addDecorator('Description', array('placement' => 'prepend'))
                ->setValidators(array(
                        array('StringLength',FALSE,array(7,50))
                    ));
                
        $confirm_password = new N2S_Validate_PasswordConfirmation();
        
        $confirmPassword = $this->createElement('password','password_confirm');
        $confirmPassword->setLabel('Retype new')
                ->setRequired(true)
                ->addValidator($confirm_password);
        
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
                            $oldpassword,$password,$confirmPassword,$save,$cancel
                            )
                );
    }
}
