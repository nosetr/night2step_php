<?php

/**
 * SimpleUrl.php
 * Description of SimpleUrl:
 * Standart url('module','action','controller')+array(params)
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.11.2012 21:57:03
 * 
 */
class N2S_View_Helper_SimpleUrl extends Zend_View_Helper_Abstract
{
    function simpleUrl($array = null)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $url = $this->view->url(array_merge(
            array_intersect_key(
                $request->getParams(),
                array_flip(array('module','action','controller'))
            ),$array),
            null,
            true
        );
        return $url;
    }
}