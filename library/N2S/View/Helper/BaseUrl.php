<?php

/**
 * BaseUrl.php
 * Description of BaseUrl
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 22.10.2012 13:06:22
 * 
 */
class N2S_View_Helper_BaseUrl extends Zend_View_Helper_Abstract
{
    function baseUrl($url = '/')
    {
        $base_url = 'http://'.$_SERVER['HTTP_HOST'].$url;
        return $base_url;
    }
}