<?php

/**
 * AlbumCreate.php
 * Description of AlbumCreate
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 20.11.2012 18:39:56
 * 
 */
class Default_Form_AlbumCreate extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setName('albumForm');

        $cancel = new Zend_Form_Element_Submit('cancel');
        $cancel ->setRequired(false)
                ->setIgnore(true)
                ->setLabel('cancel')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem'))
                ->setAttrib('onclick', '$.n2lbox.close();return false;')
                ->class = 'button special';

        $save = new Zend_Form_Element_Submit('submit');
        $save->setRequired(false)
                ->setIgnore(true)
                ->setLabel('save')
                ->addDecorator('HtmlTag',
                            array('tag' => 'div', 'class' => 'button_elem left'))
                ->class = 'button special';

        $name = $this->createElement('text', 'albname');
        $name->setLabel('Title *');

        $description = $this->createElement('textarea', 'albdescription');
        $description->setLabel('Description')
                ->setRequired(FALSE)
                ->setAttrib('COLS', '40')
                ->setAttrib('ROWS', '3');
        
        $this->addElements(array($name,$description,$save,$cancel));
    }
}