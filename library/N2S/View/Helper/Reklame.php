<?php

/**
 * Reklame.php
 * Description of Reklame
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 02.11.2012 18:52:54
 * 
 */
class N2S_View_Helper_Reklame extends Zend_View_Helper_Abstract
{
    function reklame()
    {
        $html = '<div id="n2s-advert-main">';
        $html .= $this->view->reklameChitika();
        $html .= '</div>';
        /*
        $html = '<div id="n2s-advert-main">';
        $html .= '<img width="240" src="images/relame_bsp.jpg" alt=""/>';
         * 
         */
        /*
        $html .= '<div id="n2s-advert-google-SmallSquare">';
        $html .= '<script type="text/javascript">';
        //$html .= '<!--';
        $html .= 'google_ad_client = "ca-pub-2418931938914434";';
        $html .= 'google_ad_slot = "2768871921";';
        $html .= 'google_ad_width = 200;';
        $html .= 'google_ad_height = 200;';
        //$html .= '//-->';
        $html .= '</script>';
        $html .= '<script type="text/javascript"';
        $html .= 'src="http://pagead2.googlesyndication.com/pagead/show_ads.js">';
        $html .= '</script></div>';
        */
        /*
        $html .= '</div>';
        return $html;
         * 
         */
        return $html;
    }
}