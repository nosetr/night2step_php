<?php

/**
 * EventGListEndDuration.php
 * Description of EventGListEndDuration
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.01.2013 16:46:23
 * 
 */
class N2S_Validate_EventGListEndDuration extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';
    const DATE_INVALID = 'dateInvalid';
    const NOT_GLMATCH = 'gLnotMatch';
    const DATE_GLINVALID = 'gLdateInvalid';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Event ends earlier as this date.',
        self::DATE_INVALID => 'This date can\'t be equals to enddate from event.',
        self::NOT_GLMATCH => 'Start of guest list earlier as this date.',
        self::DATE_GLINVALID => 'This date can\'t be equals to the start of guest list.'
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
                } 
                /*else {
                    return TRUE;
                }
                 * 
                 */
            }
            if(isset($context['glistduration']) && !empty($context['glistduration'])) {
                $start = new Zend_Date($context['glistduration']);
                $end = new Zend_Date($value);
                if ($end->isEarlier($start)) {
                    $this->_error(self::NOT_GLMATCH);
                    return false;
                } elseif ($end->equals($start)) {
                    $this->_error(self::DATE_GLINVALID);
                    return FALSE;
                }
                /*else {
                    return TRUE;
                }
                 * 
                 */
            }
            //else {
                return TRUE;
            //}
        }
        //else {
            return true;
        //}
    }
}