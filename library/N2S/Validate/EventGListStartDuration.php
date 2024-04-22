<?php

/**
 * EventGListStartDuration.php
 * Description of EventGListStartDuration
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.01.2013 16:26:17
 * 
 */
class N2S_Validate_EventGListStartDuration extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';
    const DATE_INVALID = 'dateInvalid';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Event ends earlier as this date.',
        self::DATE_INVALID => 'This date can\'t be equals to enddate from event.'
    );

    public function isValid($value, $context = array())
    {
        if (isset($context['glist']) && $context['glist']=='1') {
            if (isset($context['endduration']) && !empty($context['endduration'])) {
                $start = new Zend_Date($value);
                $end = new Zend_Date($context['endduration']);
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
        } else {
            return true;
        }
    }
}