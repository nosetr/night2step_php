<?php

/**
 * EventGListCounter.php
 * Description of EventGListCounter
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.01.2013 13:17:41
 * 
 */
class N2S_Validate_EventGListCounter extends Zend_Validate_Abstract
{
    /*const NOT_MATCH = 'notMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'This is not a valid number.'
    );
     * 
     */
    
    const INVALID = 'intInvalid';
    const NOT_INT = 'notInt';
    
    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String or integer expected",
        self::NOT_INT => "'%value%' does not appear to be an integer",
    );

    protected $_locale;
    
    public function __construct($locale = null)
    {
        if ($locale instanceof Zend_Config) {
            $locale = $locale->toArray();
        }

        if (is_array($locale)) {
            if (array_key_exists('locale', $locale)) {
                $locale = $locale['locale'];
            } else {
                $locale = null;
            }
        }

        if (empty($locale)) {
            require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Locale')) {
                $locale = Zend_Registry::get('Zend_Locale');
            }
        }

        if ($locale !== null) {
            $this->setLocale($locale);
        }
    }
    
    public function getLocale()
    {
        return $this->_locale;
    }
    
    public function setLocale($locale = null)
    {
        require_once 'Zend/Locale.php';
        $this->_locale = Zend_Locale::findLocale($locale);
        return $this;
    }
    
    public function isValid($value, $context = array()) {
        if (isset($context['glist']) && $context['glist']=='1') {
            if (!is_string($value) && !is_int($value) && !is_float($value)) {
                $this->_error(self::INVALID);
                return false;
            }

            if (is_int($value)) {
                return true;
            }

            $this->_setValue($value);
            if ($this->_locale === null) {
                $locale        = localeconv();
                $valueFiltered = str_replace($locale['decimal_point'], '.', $value);
                $valueFiltered = str_replace($locale['thousands_sep'], '', $valueFiltered);

                if (strval(intval($valueFiltered)) != $valueFiltered) {
                    $this->_error(self::NOT_INT);
                    return false;
                }

            } else {
                try {
                    if (!Zend_Locale_Format::isInteger($value, array('locale' => $this->_locale))) {
                        $this->_error(self::NOT_INT);
                        return false;
                    }
                } catch (Zend_Locale_Exception $e) {
                    $this->_error(self::NOT_INT);
                    return false;
                }
            }

            return true;
        } else {
            return true;
        }
    }

    /*public function isValid($value, $context = array())
    {
        if (isset($context['glist']) && $context['glist']=='1') {
            if (
                    is_numeric($value)
                    &&
                    is_int($value)
                    &&
                    $value > -1
                    ){
                return TRUE;
            } else {
                $this->_error(self::NOT_MATCH);
                return false;
            }
        } else {
            return true;
        }
    }
     * 
     */
}