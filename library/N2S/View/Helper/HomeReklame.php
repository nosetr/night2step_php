<?php

/**
 * HomeReklame.php
 * Description of HomeReklame
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 30.10.2013 14:27:29
 * 
 */
class N2S_View_Helper_HomeReklame extends Zend_View_Helper_Abstract
{
    function homeReklame()
    {
        $html = '<div>';
        $html .= '<div class="n2Module" style="text-align:center;">';
        
        $html.= '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>';
        //Large Rectangle (336 x 280)
        $html .= '<ins class="adsbygoogle"';
        $html .= 'style="display:inline-block;width:336px;height:280px"';
        $html .= 'data-ad-client="ca-pub-2418931938914434"';
        $html .= 'data-ad-slot="5708329525"></ins>';
        $html .= '<script>';
        $html .= '(adsbygoogle = window.adsbygoogle || []).push({});';
        $html .= '</script>';
        
        $html .= '</div><div class="clear"></div></div>';
        
        return $html;
    }
}
