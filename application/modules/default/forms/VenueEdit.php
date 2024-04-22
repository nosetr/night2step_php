<?php

/**
 * VenueEdit.php
 * Description of VenueEdit
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 03.02.2013 21:43:31
 * 
 */
class Default_Form_VenueEdit extends ZendX_JQuery_Form
{
    public function init()
    {
        $translator = $this->getTranslator();
        
        $this->setMethod('post')
            ->setName('albumForm')
            ->setAttrib('enctype', 'multipart/form-data');
        
        $locale = Zend_Registry::get('Zend_Locale');
        $dateFormat = Zend_Locale_Data::getContent($locale, 'date', 'short');
        if (strpos($dateFormat, 'MM') !== FALSE)
            $dateFormat = str_ireplace('MM', 'mm', $dateFormat);
        if (strpos($dateFormat, 'M') !== FALSE)
            $dateFormat = str_ireplace('M', 'mm', $dateFormat);
        
        $name = $this->createElement('text', 'albname');
        $name->setLabel('Title *')
                ->setRequired(TRUE)
                ->setValidators(array(
                        array('StringLength',FALSE,array(3,50))
                    ));
        
        $description = $this->createElement('textarea', 'albdescription');
        $description->setLabel('Description')
                ->setRequired(FALSE)
                ->setAttrib('COLS', '75')
                ->setAttrib('ROWS', '4');
        
        $spec = $this->createElement('textarea', 'specdescription');
        $spec->setLabel('Specials')
                ->setRequired(FALSE)
                ->setAttrib('COLS', '75')
                ->setAttrib('ROWS', '4');

        $save = new Zend_Form_Element_Submit('submit');
        $save->setRequired(false)
                ->setIgnore(true)
                ->setLabel('save')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem', 'style' => 'float:left;'))
                ->class = 'button special';

        $save2 = new Zend_Form_Element_Button('cancel');
        $save2->setRequired(false)
                ->setIgnore(true)
                ->setLabel('cancel')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem'))
                ->class = 'button special';
        
        $this->addElements( array (
                            $name,
            $description,
            $save,$save2
                            )
                );
    }
}
