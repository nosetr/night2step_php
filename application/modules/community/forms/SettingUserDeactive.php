<?php

/**
 * SettingUserDeactive.php
 * Description of SettingUserDeactive
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 01.03.2013 13:59:44
 * 
 */
class Community_Form_SettingUserDeactive extends Zend_Form
{
    public function init() 
    {
        $this->setMethod('post')
                ->setName('deactive')
                ->setAction('/community/index/ajax/task/deactive');
        
        $save = new Zend_Form_Element_Submit('submit');
        $save->setRequired(false)
                ->setIgnore(true)
                ->setLabel('confirm')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem left'))
                ->class = 'button special';
        
        $cancel = new Zend_Form_Element_Submit('cancel');
        $cancel ->setRequired(false)
                ->setIgnore(true)
                ->setLabel('cancel')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem'))
                ->setAttrib('onclick', 'sett.deactreset();return false;')
                ->class = 'button special';
        
        $this->addElements( array (
                            $save,$cancel
                            )
                );
    }
}
