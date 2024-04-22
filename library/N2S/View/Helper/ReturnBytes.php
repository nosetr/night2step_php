<?php

/**
 * ReturnBytes.php
 * Description of ReturnBytes
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 12.11.2012 19:55:54
 * 
 */
class N2S_View_Helper_ReturnBytes extends Zend_View_Helper_Abstract
{
    function returnBytes($val) {
       $val = trim($val);
       $last = strtolower($val{strlen($val)-1});
       switch($last) {
           // The 'G' modifier is available since PHP 5.1.0
           case 'g':
               $val *= 1024;
           case 'm':
               $val *= 1024;
           case 'k':
               $val *= 1024;
       }

       return $val;
    }
}
