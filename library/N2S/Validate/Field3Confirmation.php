<?php

/**
 * Field3Confirmation.php
 * Description of Field3Confirmation
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.11.2012 17:44:38
 * 
 */
class N2S_Validate_Field3Confirmation extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Value is required and can\'t be empty'
    );

    public function isValid($value, $context = array())
    {
        if (isset($context['event']) && $context['event']=='1') {
            if (!empty($value)) {
                return true;
            } else {
                $this->_error(self::NOT_MATCH);
                return false;
            }
        } else {
            return true;
        }
    }
}