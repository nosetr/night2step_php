<?php

/**
 * EventStartDateConfirmation.php
 * Description of EventStartDateConfirmation
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 17.01.2013 10:17:31
 * 
 */
class N2S_Validate_EventStartDateConfirmation extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Startdate is earlier as nowdate.'
    );

    public function isValid($value)
    {
        $now = Zend_Date::now();
        $start = new Zend_Date($value);
        
        if($start->isEarlier($now)){
            $this->_error(self::NOT_MATCH);
            return false;
        }
        
        return TRUE;
    }
}