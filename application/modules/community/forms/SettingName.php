<?php

/**
 * SettingName.php
 * Description of SettingName
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 18.02.2013 11:58:30
 * 
 */
class Community_Form_SettingName extends Zend_Form
{
    public function init() 
    {
        $this->setMethod('post')
                ->setName('name')
                ->setAction('/community/index/ajax/task/name')
                ->setAttrib('autocomplete', 'off');
        
        $locale = Zend_Registry::get('Zend_Locale');
        
        $firstname = $this->createElement('text', 'firstname');
        $firstname->setLabel('First Name')
                ->setRequired(TRUE)
                ->setValidators(array(
                        array('StringLength',FALSE,array(1,50))));
        /* Es gibt aktuell 3 Sprachen welche nicht mit Ihrer eigenen 
         * Schreibweise akzeptiert werden. 
         * Diese Sprachen sind koreanisch, japanisch und chinesisch, 
         * da diese Sprachen ein Alphabeth verwenden bei dem einzelne Zeichen 
         * so aufgebaut werden dass Sie mehrere Zeichen verwenden.
         * 
         * Chinese = zh 
         * Japanese = ja 
         * Korean = ko 
         * 
         */
        if ($locale != 'zh' && $locale != 'ja' && $locale != 'ko')
            $firstname->addValidator('Alpha', false, array('messages'=>'Value must contain only letters'));
        
        $lastname = $this->createElement('text', 'lastname');
        $lastname->setLabel('Last Name')
                ->setRequired(TRUE)
                ->setValidators(array(
                        array('StringLength',FALSE,array(1,50))));
        /* Es gibt aktuell 3 Sprachen welche nicht mit Ihrer eigenen 
         * Schreibweise akzeptiert werden. 
         * Diese Sprachen sind koreanisch, japanisch und chinesisch, 
         * da diese Sprachen ein Alphabeth verwenden bei dem einzelne Zeichen 
         * so aufgebaut werden dass Sie mehrere Zeichen verwenden.
         * 
         * Chinese = zh 
         * Japanese = ja 
         * Korean = ko 
         * 
         */
        if ($locale != 'zh' && $locale != 'ja' && $locale != 'ko')
            $lastname->addValidator('Alpha', false, array('messages'=>'Value must contain only letters'));
        
        $confirm_password = new N2S_Validate_Password();
        $password = new Zend_Form_Element_Password('login_password', array('label' => 'Password by night2step', 'required' => true));
        $password->addValidator($confirm_password);
        
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
                            $firstname,$lastname,$password,$save,$cancel
                            )
                );
    }
}
