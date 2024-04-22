<?php

/**
 * GeoSearch.php
 * Description of GeoSearch
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 26.10.2012 16:26:42
 * 
 */
class N2S_View_Helper_GeoSearch extends Zend_View_Helper_Abstract
{
    function geoSearch($link ='geocode', $gSearch = NULL)
    {
        $session = new Zend_Session_Namespace('geopos');
        (isset($session->city))?$hidden = $session->city.', '.$session->country:$hidden = $session->country;
        $html = '<div id="geosearcher">';
        $html .= '<div id="geosearchposition" class="n2s-tooltip left" title="'.$this->view->translate('Click here to change').'"><b>'.$this->view->translate('Search for').': </b><span id="geosearchpos">';
        if ($gSearch == null){
            if(isset($session->city))
                $html .= $session->city.' / ';
            $html .= $session->country;
        } else {
            $html .= rawurldecode($gSearch);
        }
        $html .= '</span></div>';
        $html .= '<span><a onclick="javascript:geosearch.reset(\''.$link.'\');" href="javascript:void(0);">';
        $html .= '<img id="resetLocButton" ';
        if ($gSearch == null || $gSearch == $hidden)
            $html .= 'style="display:none;" ';
        $html .= 'title="'.$this->view->translate('Click here to reset').'" class="n2s-tooltip" src="/images/reset.png" alt=""/>';
        $html .= '</a></span>';
        $html .= '<div class="clear"></div>';
        $html .= '<div id="geosearchinput" class="left" style="display: none">';
        $html .= '<input type="text" value="" name="geosearch"/>';
        $html .= '<a id="geoSearch" onclick="javascript:geosearch.find(\''.$link.'\');" href="javascript:void(0);" style="margin-left: 10px;">'.$this->view->translate('GO').'</a>';
        $html .= '</div>';
        $html .= '<div class="clear"></div>';
        $html .= '</div>';
        $html .= '<input type="hidden" name="UserRegionMain" value="'.$hidden.'"/>';
        
        return $html;
    }
}