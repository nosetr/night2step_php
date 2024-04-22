<?php

/**
 * AlbumEdit.php
 * Description of AlbumEdit
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 20.11.2012 18:39:56
 * 
 */
class Default_Form_AlbumEdit extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setName('albumForm');

        $cancel = new Zend_Form_Element_Button('cancel');
        $cancel ->setRequired(false)
                ->setIgnore(true)
                ->setLabel('Cancel')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem'))
                ->setAttrib('onclick', 'javascript:void(location.href = \'/\');')
                ->class = 'button';

        $save = new Zend_Form_Element_Submit('submit');
        $save->setRequired(false)
                ->setIgnore(true)
                ->setLabel('save')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem'))
                ->class = 'button special';

        $name = $this->createElement('text', 'albname');
        $name->setLabel('Title *');

        $description = $this->createElement('textarea', 'albdescription');
        $description->setLabel('Description')
                ->setRequired(FALSE)
                ->setAttrib('COLS', '40')
                ->setAttrib('ROWS', '3');
        
        $local = new ZendX_JQuery_Form_Element_AutoComplete('loc',array('label' => 'Location'));
        $local->setJQueryParams(array('source'=>'/tags/adress','change'=>new Zend_Json_Expr('function(){n2s.edit.checkloc();}'),
            'select'=>new Zend_Json_Expr('function(event,ui){
                $("#albaddress").val(ui.item.address).attr("disabled","disabled");
                $("#locname").val(ui.item.value);
                $("#locid").val(ui.item.id);
                }')))
                ->setAllowEmpty(false)
                ->setAttrib('onchange', 'n2s.edit.checkloc();')
                ->setAttrib('onfocus', 'this.select();')
                ->addValidator(new N2S_Validate_Field2Confirmation(),true);

        $address = $this->createElement('text', 'albaddress');
        $address->setLabel('Address')
                ->setAllowEmpty(false)
                ->setAttrib('onfocus', 'this.select();')
                ->addValidator(new N2S_Validate_FieldConfirmation(),true);
        
        $created = new Zend_Form_Element_Hidden('registerDate');
        $created->setDecorators(array('ViewHelper'));
        
        $locname = new Zend_Form_Element_Hidden('locname');
        $locname->setDecorators(array('ViewHelper'));
        
        $locid = new Zend_Form_Element_Hidden('locid');
        $locid->setDecorators(array('ViewHelper'))->setValue('0');
        
        $check = new Zend_Form_Element_Checkbox('event');
        $check->setLabel('Eventsalbum');
        
        $select = new Zend_Form_Element_Select('permissions');
        $select->setLabel('Who can see this album?')
                 ->setDescription('* Only public albums are displayed in the main calendar.')
                 ->addDecorator('Description', array('placement' => 'prepend'))
                 ->addMultiOptions(array(0 => 'anyone', 20 => 'only registerd users', 40 => 'only my friends'));
        
        $locale = Zend_Registry::get('Zend_Locale');
        $dateFormat = Zend_Locale_Data::getContent($locale, 'date', 'short');
        if (strpos($dateFormat, 'MM') !== FALSE)
            $dateFormat = str_ireplace('MM', 'mm', $dateFormat);
        if (strpos($dateFormat, 'M') !== FALSE)
            $dateFormat = str_ireplace('M', 'mm', $dateFormat);
        $date = new ZendX_JQuery_Form_Element_DatePicker('eventdate',
                array('jQueryParams' => array(
                    'defaultDate'=>'0','changeYear'=>'true','minDate'=>'-36m','maxDate'=>'+24m','dateFormat'=>$dateFormat,'changeMonth'=>'true','firstDay'=>'1','yearRange'=>'c-3:c+2')));
        $date->setAttrib('readonly', 'true')
                ->setAllowEmpty(false)
                ->addValidator(new N2S_Validate_Field3Confirmation(),true)
                ->setLabel('Eventsdate *')
                ->addDecorator('HtmlTag',array('tag'=>'dd','class'=>'tag_eventdate','style'=>'display:none'))
                ->addDecorator('Label',array('tag'=>'dt','class'=>'label_eventdate', 'style' => 'display:none'));
        
        $this->addElements(array($name,$description,$select,$check,$date,$local,$address,$save,$created,$locname,$locid));
    }
}