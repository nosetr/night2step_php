<?php

/**
 * SocButtons.php
 * Description of SocButtons
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 26.05.2013 13:59:42
 * 
 */
class N2S_View_Helper_SocButtons extends Zend_View_Helper_Abstract
{
    function socButtons()
    {
        $html = '<div id="fb-root"></div>';
        $html .= '<div class="scBtn">';
        $html .= '<span class="scBtnAr">';
        $html .= '<div class="fb-like" data-send="false" data-layout="button_count" data-width="120" data-show-faces="false"></div>';
        $html .= '</span><span class="scBtnAr">';
        $html .= '<div class="g-plus" data-action="share" data-annotation="bubble"></div>';
        $html .= '</span><span class="scBtnAr">';
        $html .= '<a href="https://twitter.com/share" class="twitter-share-button" data-via="night2step" data-lang="de"></a>';
        $html .= '</span>';
        $html .= '</div>';
        $html .= '<script type="text/javascript">';
        //Facebook
        $html .= '(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id)) return;js=d.createElement(s);js.id=id;js.src="//connect.facebook.net/de_DE/all.js#xfbml=1&appId=214206468602478";fjs.parentNode.insertBefore(js,fjs);}(document,"script","facebook-jssdk"));';
        //GooglePlus
        $html .= '(function(){var po=document.createElement("script");po.type="text/javascript";po.async=true;po.src="https://apis.google.com/js/plusone.js";var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(po,s);})();';
        //Twitter
        $html .= '!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");';
        $html .= '</script>';
        return $html;
    }
}