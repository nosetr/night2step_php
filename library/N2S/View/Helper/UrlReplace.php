<?php

/**
 * UrlReplace.php
 * Find and replace URL in string
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.10.2012 15:19:22
 * 
 */
class N2S_View_Helper_UrlReplace extends Zend_View_Helper_Abstract
{
    function urlReplace($text,$nl2br = FALSE)
    {
        $urlsearch[] = "/([^]_a-z0-9-=\"'\/])((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\};<>]*)/si";
        $urlsearch[] = "/^((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\};<>]*)/si";
        $urlreplace[]= "\\1[URL]\\2\\4[/URL]";
        $urlreplace[]= "[URL]\\1\\3[/URL]";
        $text = preg_replace($urlsearch, $urlreplace, $text);
        $text = preg_replace("/\[URL\](.*?)\[\/URL\]/si"      , "<a href=\"\\1\" target=\"blank\" style=\"text-decoration:none\">\\1</a>", $text);
        $text = preg_replace("/\[URL=(.*?)\](.*?)\[\/URL\]/si", "<a href=\"\\1\" target=\"blank\" style=\"text-decoration:none\">\\2</a>", $text);

        $text = str_replace("href=\"www","href=\"http://www",$text);
        
        //nl2br() - Fügt vor allen Zeilenumbrüchen eines Strings HTML-Zeilenumbrüche ein
        if($nl2br == TRUE)
            $text = nl2br($text);
        
        return($text);
    }
}
