<?php

/**
 * SettingAbout.php
 * Description of SettingAbout
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 11.03.2013 11:02:26
 * 
 */
class Community_Form_SettingAbout extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        
        $this->setMethod('post')
                ->setName('about')
                ->setAction('/community/index/ajax/task/about');
        
        $translator = $this->getTranslator();
        
        if (isset($options['birthDate']) && isset($options['type']) && $options['type'] == 'profil'){
            if($options['birthDate'] > 0) {
                $date = $this->createElement('text', 'birthdate');
                $date->setLabel('Birth date')
                        ->setAttrib('readonly', 'true')
                        ->addDecorator('HtmlTag',
                            array('openOnly' => true,'tag' => 'div', 'class' => 'setting_elem'));
            } else {
                $locale = Zend_Registry::get('Zend_Locale');
                $dateFormat = Zend_Locale_Data::getContent($locale, 'date', 'short');
                if (strpos($dateFormat, 'MM') !== FALSE)
                    $dateFormat = str_ireplace('MM', 'mm', $dateFormat);
                if (strpos($dateFormat, 'M') !== FALSE)
                    $dateFormat = str_ireplace('M', 'mm', $dateFormat);
                $date = new ZendX_JQuery_Form_Element_DatePicker('birthdate');
                $date->setRequired(true)
                        ->setLabel('Birth date')
                        //->removeDecorator('Label')
                        ->setAttrib('readonly', 'true')
                        ->setDescription('*You must be at least 16 years old')
                        ->addDecorator('Description', array('placement' => 'prepend'))
                        ->addDecorator('FormErrors', array('placement' => 'prepend','tag' => 'span', 'class' => 'setting_error'))
                        ->addDecorator('HtmlTag',
                            array('openOnly' => true,'tag' => 'div', 'class' => 'setting_elem'))
                        ->setAttrib('placeholder', $translator->translate('Birth date'))
                        ->addFilter('StripTags')
                        ->addFilter('StringTrim')
                        ->addValidator(new Zend_Validate_Date(array('format' => $dateFormat)))
                        ->addValidator(new N2S_Validate_BirthDate());
            }
            
            $select1 = new Zend_Form_Element_Select('permis_birthdate');
            $select1->removeDecorator('Label')
                ->addDecorator('HtmlTag',
                    array('closeOnly' => true,'tag' => 'div'))
                ->addMultiOptions(array(0 => 'anyone can see it', 20 => 'only registerd users', 40 => 'only my friends', 50 => 'only me'));

            $work = $this->createElement('text', 'work');
            $work->setLabel('Work')
                //->removeDecorator('Label')
                ->setAttrib('placeholder', $translator->translate('Where have you worked?'))
                ->addDecorator('HtmlTag',
                    array('openOnly' => true,'tag' => 'div', 'class' => 'setting_elem'));
            
            $select2 = new Zend_Form_Element_Select('permis_work');
            $select2->removeDecorator('Label')
                ->addDecorator('HtmlTag',
                    array('closeOnly' => true,'tag' => 'div'))
                ->addMultiOptions(array(0 => 'anyone can see it', 20 => 'only registerd users', 40 => 'only my friends', 50 => 'only me'));

            $uni = $this->createElement('text', 'uni');
            $uni->setLabel('University')
                //->removeDecorator('Label')
                ->setAttrib('placeholder', $translator->translate('Where did you go to university?'))
                ->addDecorator('HtmlTag',
                    array('openOnly' => true,'tag' => 'div', 'class' => 'setting_elem'));
            
            $select3 = new Zend_Form_Element_Select('permis_uni');
            $select3->removeDecorator('Label')
                ->addDecorator('HtmlTag',
                    array('closeOnly' => true,'tag' => 'div'))
                ->addMultiOptions(array(0 => 'anyone can see it', 20 => 'only registerd users', 40 => 'only my friends', 50 => 'only me'));

            $school = $this->createElement('text', 'school');
            $school->setLabel('School')
                //->removeDecorator('Label')
                ->setAttrib('placeholder', $translator->translate('Where did you go to secondary school?'))
                ->addDecorator('HtmlTag',
                    array('openOnly' => true,'tag' => 'div', 'class' => 'setting_elem'));
            
            $select4 = new Zend_Form_Element_Select('permis_school');
            $select4->removeDecorator('Label')
                ->addDecorator('HtmlTag',
                    array('closeOnly' => true,'tag' => 'div'))
                ->addMultiOptions(array(0 => 'anyone can see it', 20 => 'only registerd users', 40 => 'only my friends', 50 => 'only me'));

            $curcity = $this->createElement('text', 'curcity');
            $curcity->setLabel('Current city')
                //->removeDecorator('Label')
                ->setAttrib('placeholder', $translator->translate('Add your current city'))
                ->addDecorator('HtmlTag',
                    array('openOnly' => true,'tag' => 'div', 'class' => 'setting_elem'));
            
            $select5 = new Zend_Form_Element_Select('permis_curcity');
            $select5->removeDecorator('Label')
                ->addDecorator('HtmlTag',
                    array('closeOnly' => true,'tag' => 'div'))
                ->addMultiOptions(array(0 => 'anyone can see it', 20 => 'only registerd users', 40 => 'only my friends', 50 => 'only me'));

            $hometown = $this->createElement('text', 'hometown');
            $hometown->setLabel('Hometown')
                //->removeDecorator('Label')
                ->setAttrib('placeholder', $translator->translate('Add your hometown'))
                ->addDecorator('HtmlTag',
                    array('openOnly' => true,'tag' => 'div', 'class' => 'setting_elem'));
            
            $select6 = new Zend_Form_Element_Select('permis_hometown');
            $select6->removeDecorator('Label')
                ->addDecorator('HtmlTag',
                    array('closeOnly' => true,'tag' => 'div'))
                ->addMultiOptions(array(0 => 'anyone can see it', 20 => 'only registerd users', 40 => 'only my friends', 50 => 'only me'));

            $description = $this->createElement('textarea', 'about');
            $description->setLabel('About')
                //->removeDecorator('Label')
                ->setAttrib('COLS', '32')
                ->setAttrib('ROWS', '4')
                ->setAttrib('placeholder', $translator->translate('Write about yourself'))
                ->addDecorator('HtmlTag',
                    array('openOnly' => true,'tag' => 'div', 'class' => 'setting_elem'));
            
            $select10 = new Zend_Form_Element_Select('permis_about');
            $select10->removeDecorator('Label')
                ->addDecorator('HtmlTag',
                    array('closeOnly' => true,'tag' => 'div'))
                ->addMultiOptions(array(0 => 'anyone can see it', 20 => 'only registerd users', 40 => 'only my friends', 50 => 'only me'));

            

            $this->addElements(array($date,$select1,$work,$select2,$uni,$select3,$school,$select4,
                $curcity,$select5,$hometown,$select6,$description,$select10));
            
            $this->addDisplayGroup(array('birthdate','permis_birthdate','about','permis_about'),'aboutme', array('legend' => 'About you'));
            $this->addDisplayGroup(array('work','permis_work','uni','permis_uni','school','permis_school'),'educ', array('legend' => 'Work and education'));
            $this->addDisplayGroup(array('curcity','permis_curcity','hometown','permis_hometown'),'living', array('legend' => 'Living'));
        }
        
        $save = new Zend_Form_Element_Submit('submit');
        $save->setRequired(false)
                ->setIgnore(true)
                //->setOrder(10)
                ->setLabel('save')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem left'))
                ->class = 'button special';

        $cancel = new Zend_Form_Element_Submit('cancel');
        $cancel ->setRequired(false)
                ->setIgnore(true)
                //->setOrder(11)
                ->setLabel('cancel')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem'))
                ->setAttrib('onclick', 'sett.reset();return false;')
                ->class = 'button special';
        
        $this->addElements(array($save,$cancel));
    }
}
