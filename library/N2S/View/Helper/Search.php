<?php

/**
 * Search.php
 * Description of Search
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 17.10.2013 10:54:52
 * 
 */
class N2S_View_Helper_Search extends Zend_View_Helper_Abstract
{
    function search()
    {
        $req = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $req->getControllerName();
        if($controller != 'search'){
            $html = '<div id="idxS">';
            $html .= '<input placeholder="'.$this->view->translate('Search...').'" onfocus="javascript:n2s.search.auto(this);" type="text" value="" name="indexsearch"/>';
            $html .= '<a onclick="javascript:n2s.search.goto(event);" href="javascript:void(0);" id="idxSearch">SEARCH</a>';
            $html .= '<div id="idxSFld"><div id="notifArrow"></div><div id="idxSFldT"></div></div>';

            $html .= '</div>';

            return $html;
        }
    }
}
