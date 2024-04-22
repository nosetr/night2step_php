<?php

/**
 * UserRegistration.php
 * Description of UserRegistration
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 19.09.2012 17:20:55
 * 
 */
class Community_Form_UserRegistration extends Zend_Form
{    
    public function init() 
    {
        if (Zend_Registry::isRegistered('TABLE_PREFIX')) {
            $pref = Zend_Registry::get('TABLE_PREFIX');
        } else {
            $pref = '';
        }
        
        $this->setMethod('post');
        $this->setName('registerForm');
        
        $locale = Zend_Registry::get('Zend_Locale');
        $dateFormat = Zend_Locale_Data::getContent($locale, 'date', 'short');
        if (strpos($dateFormat, 'MM') !== FALSE)
            $dateFormat = str_ireplace('MM', 'mm', $dateFormat);
        if (strpos($dateFormat, 'M') !== FALSE)
            $dateFormat = str_ireplace('M', 'mm', $dateFormat);
        $date = new ZendX_JQuery_Form_Element_DatePicker('birthdate',
                array('jQueryParams' => array(
                    'defaultDate'=>'-17y',
                    'changeYear'=>'true',
                    'minDate'=>'-1200m',
                    'maxDate'=>'-192m',
                    'dateFormat'=>$dateFormat,
                    'changeMonth'=>'true',
                    'firstDay'=>'1',
                    'yearRange'=>'c-84:c')));
        $date->setRequired(true)
                ->setLabel('Birth date')
                ->setAttrib('readonly', 'true')
                ->setDescription('*You must be at least 16 years old')
                ->addDecorator('Description', array('placement' => 'prepend'))
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator(new Zend_Validate_Date(array('format' => $dateFormat)))
                ->addValidator(new N2S_Validate_BirthDate());
        if (Zend_Registry::get('Zend_Locale') != 'en'){
            $translator = $this->getTranslator();
            $mNS = explode( ',', $translator->translate('monthNamesShort'));
            $dNM = explode( ',', $translator->translate('dayNamesMin'));
            $dN = explode( ',', $translator->translate('dayNames'));
            $date->setJQueryParams(array(
                'monthNamesShort' => $mNS,
                'dayNamesMin' => $dNM,
                'dayNames' => $dN,
                'nextText' => $translator->translate('nextText'),
                'prevText' => $translator->translate('prevText')));
        }

        $gender = new Zend_Form_Element_Radio('gender');

        $gender->setSeparator('')
                ->setLabel('Gender')
                ->setRequired(true)
                        ->addMultiOption('m', 'Male')
                        ->addMultiOption('f', 'Female');

        $save = new Zend_Form_Element_Submit('submit');
        $save->setRequired(false)
                ->setIgnore(true)
                ->setLabel('SignUp')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem'))
                ->class = 'button special';

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

        $db_lookup_validator = new Zend_Validate_Db_NoRecordExists($pref.'users', 'email');
        $emailAddress = new N2S_Validate_EmailAddress();
        $email = new Zend_Form_Element_Text('user_email', array('label' => 'E-Mail', 'required' => true));
        $email->addFilters(array('StringTrim', 'StripTags'))
                ->addValidator($emailAddress,  TRUE)
                ->addValidator($db_lookup_validator,  TRUE);

        $password = $this->createElement('password','user_password');
        $password->setLabel('Password')
                ->setRequired(true)
                ->setDescription('*Min. 7 and max. 50 characters')
                ->addDecorator('Description', array('placement' => 'prepend'))
                ->setValidators(array(
                        array('StringLength',FALSE,array(7,50))
                    ));
                
        $confirm_password = new N2S_Validate_PasswordConfirmation();
        
        $confirmPassword = $this->createElement('password','password_confirm');
        $confirmPassword->setLabel('Confirm Password')
                ->setRequired(true)
                ->addValidator($confirm_password);

        $captcha = $this->createElement('captcha', 'captcha',
                    array('required' => true,
                    'captcha' => array('captcha' => 'Image',
                    'font' => BASE_PATH.'/fonts/arial.ttf',
                    'fontSize' => '24',
                    'wordLen' => 5,
                    'height' => '50',
                    'width' => '200',
                    'imgDir' => BASE_PATH.'/captcha',
                    'imgUrl' => Zend_Controller_Front::getInstance()->getBaseUrl().'/captcha',
                    'dotNoiseLevel' => 50,
                    'lineNoiseLevel' => 5)));
        $captcha->setLabel('Please type the words shown');

        $csrf = new Zend_Form_Element_Hash('csrf');
        $csrf->setIgnore(TRUE);
        $csrf->addErrorMessage('The session has expired. Try again.');
        $this->addElements( array (
                            $firstname,$lastname,$email,$date,$gender,$password,$confirmPassword,
                            $captcha,$save,$csrf
                            )
                );
    }
}
