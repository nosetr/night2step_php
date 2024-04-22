<?php

/**
 * UserThumbList.php
 * Description of UserThumbList
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 11.01.2013 13:02:50
 * 
 */
class N2S_View_Helper_UserThumbList extends Zend_View_Helper_Abstract
{
    function userThumbList($list, $count=5)
    {
        shuffle($list); // Reienfolge zufälig ändern
        $list = array_slice($list, 0, $count);   // liefert erste 6
        $html = '';
        foreach ($list as $l) {
            $html .= '<div style="float:left;margin-right:5px;">'.$this->view->userThumb($l).'</div>';
        }
        $html .= '<div class="clear"></div>';
        return $html;
    }
}