<?php

/**
 * EmailAddress.php
 * Description of EmailAddress
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.09.2012 18:19:54
 * 
 */
class N2S_Validate_EmailAddress extends Zend_Validate_EmailAddress
{
    public function getMessages() {
        $errorMes = $this->getTranslator()->translate("'%value%' is not a valid e-mail address");
        $this->_messages = array(str_ireplace("'%value%'", $this->_value, $errorMes));
        return $this->_messages;
    }
} 