<?php

/**
 * EventEdit.php
 * Description of EventEdit
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 12.12.2012 14:42:19
 * 
 */
class Default_Form_EventEdit extends ZendX_JQuery_Form
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
        
        $duration = new N2S_Form_Element_DateTimePicker('duration',array('label' => 'StartDate *'));
        $duration->setJQueryParams(array(
            'changeYear'=>'true',
            'changeMonth'=>'true',
            'ampm' => false,
            'timeOnly'=> false,
            'hourGrid' => 4,
            'minuteGrid' => 5,
            'stepHour' => 1,
            'stepMinute' => 1,
            'timeOnlyTitle' => $translator->translate('Choose Time'),
            'currentText' => $translator->translate('Now'),
            'closeText' => $translator->translate('Done'),
            'timeText' => $translator->translate('Time'),
            'hourText' => $translator->translate('Hour'),
            'minuteText' => $translator->translate('Minute'),
            'dateFormat'=>$dateFormat,
            'minDate'=>'0'
            ));
        $duration->setAttribs(array(
                    'timepicker'=>true,
                    'readonly' => 'readonly'
                ))
                ->setRequired(true);
        
        $endDuration = new N2S_Form_Element_DateTimePicker('endduration',array('label' => 'EndDate *'));
        $endDuration->setJQueryParams(array(
            'changeYear'=>'true',
            'changeMonth'=>'true',
            'ampm' => false,
            'timeOnly'=> false,
            'hourGrid' => 4,
            'minuteGrid' => 5,
            'stepHour' => 1,
            'stepMinute' => 1,
            'timeOnlyTitle' => $translator->translate('Choose Time'),
            'currentText' => $translator->translate('Now'),
            'closeText' => $translator->translate('Done'),
            'timeText' => $translator->translate('Time'),
            'hourText' => $translator->translate('Hour'),
            'minuteText' => $translator->translate('Minute'),
            'dateFormat'=>$dateFormat,
            'minDate'=>'0'
            ));
        $endDuration->setAttribs(array(
                    'timepicker'=>true,
                    'readonly' => 'readonly'
                ))
                ->setRequired(true)
                ->setAllowEmpty(false)
                ->addValidator(new N2S_Validate_EventEndDateConfirmation(),true)
                ;

        $name = $this->createElement('text', 'albname');
        $name->setLabel('Title *')
                ->setRequired(TRUE)
                ->setValidators(array(
                        array('StringLength',FALSE,array(3,50))
                    ));
        
        $local = new ZendX_JQuery_Form_Element_AutoComplete('loc',array('label' => 'Location'));
        $local->setJQueryParams(array('source'=>'/tags/adress','change'=>new Zend_Json_Expr('function(){n2s.edit.checkloc();}'),
            'select'=>new Zend_Json_Expr('function(event,ui){
                $("#albaddress").val(ui.item.address).attr("disabled","disabled");
                $("#locname").val(ui.item.value);
                $("#locid").val(ui.item.id);
                }')));
        
        $locid = new Zend_Form_Element_Hidden('locid');
        $locid->setDecorators(array('ViewHelper'))->setValue('0');
        
        $locname = new Zend_Form_Element_Hidden('locname');
        $locname->setDecorators(array('ViewHelper'));

        $address = $this->createElement('text', 'albaddress');
        $address->setLabel('Address')
                ->setAllowEmpty(false)
                ->setAttrib('onfocus', 'this.select();')
                ->addValidator(new N2S_Validate_FieldConfirmation(),true);

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
                ->setLabel('publish')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem', 'style' => 'float:left;'))
                ->class = 'button special';

        $save2 = new Zend_Form_Element_Submit('cancel');
        $save2->setRequired(false)
                ->setIgnore(true)
                ->setLabel('save as draft')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem'))
                ->class = 'button special';
        
        $glist = new Zend_Form_Element_Checkbox('glist');
        $glist->setLabel('Guest list embed');

        $glistdescription = $this->createElement('textarea', 'glistdescription');
        $glistdescription->setLabel('Guest list Description')
                //->setAllowEmpty(false)
                //->addValidator(new N2S_Validate_EventGListRequered(),true)
                ->setAttrib('COLS', '75')
                ->setAttrib('ROWS', '4')
                ->addDecorator('HtmlTag',array('tag'=>'dd','class'=>'tag_glistdescription','style'=>'display:none'))
                ->addDecorator('Label',array('tag'=>'dt','class'=>'label_glistdescription', 'style' => 'display:none'));
        
        $gastlistcount = $this->createElement('text', 'glistcount');
        $gastlistcount->setLabel('Maximum number of guest list places')
                ->setAllowEmpty(false)
                ->addValidator(new N2S_Validate_EventGListCounter(),true)
                ->addValidator(new Zend_Validate_GreaterThan(-1), false)
                ->setErrorMessages(array('This value is not valid.'))
                 ->setDescription('* Enter the maximum number of guest list posts. Enter 0 (zero), so there is no restriction.')
                 ->addDecorator('Description', array('placement' => 'prepend'))
                 ->addDecorator('HtmlTag',array('tag'=>'dd','class'=>'tag_glistcount','style'=>'display:none'))
                 ->addDecorator('Label',array('tag'=>'dt','class'=>'label_glistcount', 'style' => 'display:none'));
        
        $glistduration = new N2S_Form_Element_DateTimePicker('glistduration',array('label' => 'Guest list StartDate *'));
        $glistduration->setJQueryParams(array(
            'changeYear'=>'true',
            'changeMonth'=>'true',
            'ampm' => false,
            'timeOnly'=> false,
            'hourGrid' => 4,
            'minuteGrid' => 5,
            'stepHour' => 1,
            'stepMinute' => 15,
            'timeOnlyTitle' => $translator->translate('Choose Time'),
            'currentText' => $translator->translate('Now'),
            'closeText' => $translator->translate('Done'),
            'timeText' => $translator->translate('Time'),
            'hourText' => $translator->translate('Hour'),
            'minuteText' => $translator->translate('Minute'),
            'dateFormat'=>$dateFormat,
            'minDate'=>'0'
            ));
        $glistduration->setAttribs(array(
                    'timepicker'=>true,
                    'readonly' => 'readonly'
                ))
                ->setAllowEmpty(false)
                ->addValidator(new N2S_Validate_EventGListRequered(),true)
                ->addValidator(new N2S_Validate_EventGListStartDuration(),FALSE)
                 ->addDecorator('HtmlTag',array('tag'=>'dd','class'=>'tag_glistduration','style'=>'display:none'))
                 ->addDecorator('Label',array('tag'=>'dt','class'=>'label_glistduration', 'style' => 'display:none'));
        
        $emailAddress = new N2S_Validate_EmailAddress();
        $glistEmail = new Zend_Form_Element_Text('glistemail', array('label' => 'Guest list send to email'));
        $glistEmail->addFilters(array('StringTrim', 'StripTags'))
                 ->addValidator($emailAddress,  TRUE)
                 ->setDescription('* If you wish to become an email with guest list from us, write your email hier. Otherwise leave it empty.')
                 ->addDecorator('Description', array('placement' => 'prepend'))
                 ->addDecorator('HtmlTag',array('tag'=>'dd','class'=>'tag_glistemail','style'=>'display:none'))
                 ->addDecorator('Label',array('tag'=>'dt','class'=>'label_glistemail', 'style' => 'display:none'));
                
        $glistendDuration = new N2S_Form_Element_DateTimePicker('glistendduration',array('label' => 'Guest list EndDate *'));
        $glistendDuration->setJQueryParams(array(
            'changeYear'=>'true',
            'changeMonth'=>'true',
            'ampm' => false,
            'timeOnly'=> false,
            'hourGrid' => 4,
            'minuteGrid' => 5,
            'stepHour' => 1,
            'stepMinute' => 15,
            'timeOnlyTitle' => $translator->translate('Choose Time'),
            'currentText' => $translator->translate('Now'),
            'closeText' => $translator->translate('Done'),
            'timeText' => $translator->translate('Time'),
            'hourText' => $translator->translate('Hour'),
            'minuteText' => $translator->translate('Minute'),
            'dateFormat'=>$dateFormat,
            'minDate'=>'0'
            ));
        $glistendDuration->setAttribs(array(
                    'timepicker'=>true,
                    'readonly' => 'readonly'
                ))
                ->setAllowEmpty(false)
                ->addValidator(new N2S_Validate_EventGListRequered(),true)
                ->addValidator(new N2S_Validate_EventGListEndDuration(),FALSE)
                 ->addDecorator('HtmlTag',array('tag'=>'dd','class'=>'tag_glistendDuration','style'=>'display:none'))
                 ->addDecorator('Label',array('tag'=>'dt','class'=>'label_glistendDuration', 'style' => 'display:none'));
        
        $select = new Zend_Form_Element_Select('permissions');
        $select->setLabel('Who can see this event?')
                 ->setDescription('* Only public events are displayed in the main calendar.')
                 ->addDecorator('Description', array('placement' => 'prepend'))
                 ->addMultiOptions(array(0 => 'anyone', 20 => 'only registerd users', 40 => 'only my friends'));
        
        $this->addElements( array (
                            $name,
            $description,
            $spec,
            $select,
            $duration,
            $endDuration,//
            $local,
            $address,
            $glist,$gastlistcount,$glistdescription,$glistduration,$glistendDuration,$glistEmail));
        
        $this->addDisplayGroup(array('albname','permissions'),'titel');
        $this->addDisplayGroup(array('albdescription','specdescription'),'info', array('legend' => 'Info'));
        $this->addDisplayGroup(array('duration','endduration'),'time', array('legend' => 'Time'));
        $this->addDisplayGroup(array('loc','albaddress'),'local', array('legend' => 'Location'));
        $this->addDisplayGroup(array('glist','glistdescription','glistcount','glistduration','glistendduration','glistemail'),'about', array('legend' => 'Guest list'));
        /*
        $groups = $this->getDisplayGroup('titel');
        $groups->setDecorators(array(
                'FormElements',
                'Fieldset',
                array('HtmlTag',array('tag'=>'div','class' => 'someclassname'))
        ));
         * 
         */
        
        $this->setDisplayGroupDecorators(array(
                'FormElements',
                //array(array('innerHtmlTag'=>'HtmlTag'),array('tag'=>'ul')),
                'Fieldset',
                array(array('outerHtmlTag'=>'HtmlTag'),array('tag'=>'div','style'=>'margin:20px 0;'))
                //array('HtmlTag',array('tag'=>'div','class' => 'someclassname')) 
            ));

        $this->addElements(array($save,$save2,$locname,$locid));
    }

}