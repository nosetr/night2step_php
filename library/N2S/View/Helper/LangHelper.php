<?php

/**
 * LangHelper.php
 * Description of LangHelper
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 17.10.2012 18:52:50
 * 
 */
class N2S_View_Helper_LangHelper extends Zend_View_Helper_Abstract
{
    function langHelper($string,$data)
    {
        $session = new Zend_Session_Namespace('userlanguage');
        if (($session->language == 'ru')&&($data > 1)){
            $l = substr($data, -1);
            if($data < 21){
                ($data < 5)?$string = $string:$string = $string.'5';
            } else {
                if($l == 1){
                    $string = $string.'1';
                }else{
                    ($l < 5 && $l > 0)?$string = $string:$string = $string.'5';
                }
            }
        }else{
            $string = $string;  
        }
        return $this->view->translate($string);
    }
}