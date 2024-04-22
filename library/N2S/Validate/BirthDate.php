<?php

/**
 * BirthDate.php
 * Description of BirthDate
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 26.09.2012 13:15:54
 * 
 */
class N2S_Validate_BirthDate extends Zend_Validate_Abstract
{
    const DATE_INVALID = 'dateInvalid';

    protected $_messageTemplates = array(
        self::DATE_INVALID => "You must be at least 16 years old"
    );

    public function isValid($value) {
        $this->_setValue($value);

        $date = new Zend_Date($value);
        $now = new Zend_Date();
        $now->subYear('16');

        if ($now->isEarlier($date)) {
            $this->_error(self::DATE_INVALID);
            return false;
        }

        return true;
    }
}
