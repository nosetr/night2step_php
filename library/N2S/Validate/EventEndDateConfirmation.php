<?php

/**
 * EventEndDateConfirmation.php
 * Description of EventEndDateConfirmation
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.01.2013 11:23:07
 * 
 */
class N2S_Validate_EventEndDateConfirmation extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';
    const DATE_INVALID = 'dateInvalid';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Enddate is earlier as startdate.',
        self::DATE_INVALID => 'Enddate can\'t be equals to startdate.'
    );

    public function isValid($value, $context = array())
    {
        if(isset($context['duration'])){
            $start = new Zend_Date($context['duration']);
            $end = new Zend_Date($value);
            if ($end->isEarlier($start)) {
                $this->_error(self::NOT_MATCH);
                return false;
            } elseif ($end->equals($start)) {
                $this->_error(self::DATE_INVALID);
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return TRUE;
        }
    }
}