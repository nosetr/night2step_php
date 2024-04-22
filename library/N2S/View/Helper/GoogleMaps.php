<?php

/**
 * GoogleMaps.php
 * Description of GoogleMaps
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 08.11.2012 12:22:11
 * 
 */
class N2S_View_Helper_GoogleMaps extends Zend_View_Helper_Abstract
{
    public function googleMaps($Lat,$Lng,$content,$width='510px',$height='150px',
            $zoom=14,$timeout = 300)//1000 ms = 1 second.
    {
        $html = '<div  style="overflow:hidden;height:'.$height.';width:'.$width.';">';
        $html .= '<div id="map_canvas" style="height:'.$height.';width:'.$width.';"></div>';
        $html .= '</div>';
        $html .= '<script type="text/javascript">';
        $html .= 'window.setTimeout("initGmaps();",'.$timeout.');';
        $html .= 'function initGmaps(){';
        $html .= 'var myLatlng = new google.maps.LatLng('.$Lat.','.$Lng.');';
        $html .= 'var myOptions = {';
        $html .= 'zoom:'.$zoom.',';
        $html .= 'center:new google.maps.LatLng('.$Lat.','.$Lng.'),';
        $html .= 'mapTypeId: google.maps.MapTypeId.ROADMAP};';
        $html .= 'map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);';
        $html .= 'marker = new google.maps.Marker({map: map,position: myLatlng});';
        $html .= 'infowindow = new google.maps.InfoWindow({position: myLatlng,';
        $html .= 'content: "'.$content.'",pixelOffset: new google.maps.Size(0, 0)});';
        $html .= 'google.maps.event.addListener(marker, "click", function(){';
        $html .= 'infowindow.open(map,marker);});}</script>';
        
        return $html;
    }
}