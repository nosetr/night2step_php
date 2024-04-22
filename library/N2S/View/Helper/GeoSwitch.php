<?php

/**
 * GeoSwitch.php
 * Description of GeoSwitch
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 26.10.2012 12:58:57
 * 
 */
class N2S_View_Helper_GeoSwitch extends Zend_View_Helper_Abstract
{
    function geoSwitch()
    {
        $session = new Zend_Session_Namespace('geopos');
        $html = '<div id="geoswitcher">';
        $html .= '<div id="geoposition" class="n2s-tooltip left" title="'.$this->view->translate('Click here to change').'"><b>'.$this->view->translate('Your region').': </b>';
        if(isset($session->city))
            $html .= $session->city.' / ';
        $html .= $session->country;
        $html .= '</div>';
        $html .= '<div id="geoinput" class="left" style="display: none">';
        $html .= '<input type="text" value="" name="geoswitch"/>';
        $html .= '<a id="geoSwitch" rel="nofollow" href="'.$this->view->url(array('geocode'=>'geolocal'),'default', false).'" style="margin-left: 10px;">'.$this->view->translate('GO').'</a>';
        $html .= '</div><a href="javascript:void(0);" onclick="javascript:n2s.geolocation.autoDetect();"><img class="n2s-tooltip" title="'.$this->view->translate('Auto-Detect').'" id="autoLocButton" src="/images/auto_location.png" alt=""/></a>';
        $html .= '</div>';
        
        return $html;
    }
}