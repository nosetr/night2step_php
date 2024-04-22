<?php

/**
 * EventGListRequered.php
 * Description of EventGListRequered
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.01.2013 11:48:39
 * 
 */
class N2S_Validate_EventGListRequered extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Value is required and can\'t be empty'
    );

    public function isValid($value, $context = array())
    {
        if (isset($context['glist']) && $context['glist']=='1') {
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