<?php

/**
 * Password.php
 * Description of Password
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.09.2012 21:27:51
 * 
 */
class N2S_Validate_Password extends Zend_Validate_Abstract 
{
    const NOT_MATCH = 'notMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Passwords don\'t match'
    );

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);
        
        if (is_array($context)) {
            $model = new Community_Model_Access();
            $result = FALSE;
            if (isset($context['login_user']))
            {
                $result = $model->_getEmailInfo($context['login_user'],$value);
            } else {
                $result = $model->_getUserPass($value);
            }
            if($result == TRUE)
                return $result;
        }

        $this->_error(self::NOT_MATCH);
        return false;
    }
}
