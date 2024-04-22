<?php

/**
 * ShortText.php
 * Bsp.:
 * $longtext = "Das ist ein sehr langer Text der auf eine bestimmte Länge gekürzt werden soll.";
 * echo shortText($longtext,70);
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 14:19:42
 * 
 */
class N2S_View_Helper_ShortText extends Zend_View_Helper_Abstract
{
    function shortText($string,$lenght, $ajax = FALSE, $url = TRUE) {
        
        $string = $this->view->escape($string);
        if (strlen($string) > $lenght) {
            if ($ajax == TRUE)
                ($url == TRUE)?$orig = $this->view->urlReplace($string):$orig = $string;
            $string = substr($string,0,$lenght)."...";
            $string_ende = strrchr($string, " ");
            $string = strip_tags(str_replace($string_ende," ... ", $string));
            if ($url == TRUE)
                $string = $this->view->urlReplace($string);
            
            if ($ajax == TRUE){
                $string = '<span>'.$string.'</span>
                    <span class="post_more" onclick="$(this).hide(); $(this).prev().hide(); $(this).next().show();">
                    <b>'.$this->view->translate('more').'</b></span>
                    <span style="display: none">'.$orig.'</span>';
            }
        } else {
            if ($url == TRUE)
                $string = $this->view->urlReplace($string);
        }
        return $string;
    }
}