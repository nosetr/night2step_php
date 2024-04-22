<?php

/**
 * FieldConfirmation.php
 * Description of FieldConfirmation
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.11.2012 17:44:38
 * 
 */
class N2S_Validate_FieldConfirmation extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';
    const DATE_INVALID = 'dateInvalid';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'If you set a location, address can`t be empty',
        self::DATE_INVALID => 'Specified address is not valid.'
    );

    public function isValid($value, $context = array())
    {
        if ((isset($context['loc']) && !empty($context['loc']) && isset($context['locid']) && $context['locid']==0) || (isset($context['event']) && $context['event']=='1' && isset($context['locid']) && $context['locid']==0)) {
            if (!empty($value)) {
                if (isset($context['locid']) && $context['locid']==0){
                    $geoloc = N2S_GeoCode_GoogleGeocode::googleGeocode($value);
                    if(!is_array($geoloc) || count($geoloc) == 0){
                        $this->_error(self::DATE_INVALID);
                        return false;
                    }
                }
                return true;
            }
            $this->_error(self::NOT_MATCH);
            return false;
        }
        
        return true;
    }
}