<?php

/**
 * SimpleMaps.php
 * Description of SimpleMaps
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 06.11.2012 17:43:19
 * 
 */
class N2S_View_Helper_SimpleMaps extends Zend_View_Helper_Abstract
{
    public function simpleMaps($locid,$height=150,$frame=FALSE)
    {        
        $adresses = new Default_Model_Adresses();
        $location = $adresses->getAdress($locid);
        if($location){
            $sessionlang = new Zend_Session_Namespace('userlanguage');
            (isset($sessionlang->language))?$lang = $sessionlang->language:$lang = null;
            $pos = $location->address;
            $geoloc = N2S_GeoCode_GoogleGeocode::googleGeocode($pos,$lang);
            if(is_array($geoloc) && count($geoloc) > 0){
                $formAdress = $geoloc['formatted_address'];
            } else {
                $formAdress = $pos;
            }
            $Lat=$location->latitude;
            $Lng=$location->longitude;
            $loclink = $this->view->url(array('module'=>'default',
                                    'controller'=>'venues','action'=>'show',
                                    'id'=>$location->id),
                                    'default', true);
            
            $photos = new Default_Model_Photos();
            $photo = $photos->getPhotoID($location->photoid);
            if ($location->photoid > 0 && $photo && file_exists($photo->thumbnail)){
                $evimg = '/'.$photo->thumbnail;
            } else {
                $evimg = '/images/marker.png';
            }

            $thumb = '<a class=\"mapLink_to\" href=\"'.$loclink.'\"><img class=\"thumb-avatar\" width=\"64\" height=\"64\" src=\"'.$evimg.'\" alt=\"\"/></a>';
            $content = '<div class=\"n2s-mapInfoBox\">';
            $content .= '<div class=\"newsfeed-avatar\"><div class=\"n2s-thumb\">'.$thumb.'</div></div>';
            $content .= '<div style=\"margin:0 0 2px 60px;\"><div class=\"n2s-mapInfoBox-title\"><a class=\"mapLink_to\" href=\"'.$loclink.'\">'.$location->name.'</a></div>';
            $content .= '<div class=\"n2s-mapInfoBox-address\">'.$formAdress.'</div></div>';
            $content .= '<div style=\"margin:0 0 2px 60px;\"><a id=\"view_L-map\" class=\"n2s-map n2lbox.iframe\" href=\"/events/map/lat/'.$Lat.'/long/'.$Lng.'/loc/'.$location->name;
            $content .= '\">'.$this->view->translate('View large map').'</a></div>';
            $content .= '</div>';
        }
        $html = '<div style="width:100%;height:'.$height.'px;"><div id="map_canvas" style="width:100%;height:'.$height.'px;"></div></div>';
        
        $html .= '<script type="text/javascript">';
        $html .= 'var mapTS=true;';
        $html .= 'function InfoBox(opts){';
        $html .= 'google.maps.OverlayView.call(this);';
        $html .= 'this.latlng_ = opts.latlng;';
        $html .= 'this.map_ = opts.map;';
        $html .= 'this.offsetVertical_ = -67;';
        $html .= 'this.offsetHorizontal_ = -167;';
        $html .= 'this.height_ = 50;';
        $html .= 'this.width_ = 330;';
        $html .= 'var me = this;';
        $html .= 'this.boundsChangedListener_ =';
        $html .= 'google.maps.event.addListener(this.map_,"bounds_changed",function(){';
        $html .= 'return me.panMap.apply(me);});';
        $html .= 'this.setMap(this.map_);}';
        $html .= 'InfoBox.prototype = new google.maps.OverlayView();';
        $html .= 'InfoBox.prototype.remove = function(){';
        $html .= 'if(this.div_){';
        $html .= 'this.div_.parentNode.removeChild(this.div_);';
        $html .= 'this.div_ = null;}};';
        $html .= 'InfoBox.prototype.draw = function(){';
        $html .= 'this.createElement();if (!this.div_) return;';
        $html .= 'var pixPosition = this.getProjection().fromLatLngToDivPixel(this.latlng_);';
        $html .= 'if (!pixPosition) return;';
        $html .= 'this.div_.style.width = this.width_ + "px";';
        $html .= 'this.div_.style.left = (pixPosition.x + this.offsetHorizontal_) + "px";';
        $html .= 'this.div_.style.height = this.height_ + "px";';
        $html .= 'this.div_.style.top = (pixPosition.y + this.offsetVertical_) + "px";';
        $html .= 'this.div_.style.display = "block";};';
        $html .= 'InfoBox.prototype.createElement = function(){';
        $html .= 'var panes = this.getPanes();';
        $html .= 'var div = this.div_;';
        $html .= 'if(!div){';
        $html .= 'div = this.div_ = document.createElement("div");';
        $html .= 'div.style.position = "absolute";';
        $html .= 'div.style.width = this.width_ + "px";';
        $html .= 'div.style.height = this.height_ + "px";';
        $html .= 'var contentDiv = document.createElement("div");';
        $html .= 'contentDiv.innerHTML = "'.$content.'";';
        $html .= 'var topDiv = document.createElement("div");';
        $html .= 'topDiv.style.textAlign = "right";';
        $html .= 'topDiv.appendChild(contentDiv);';
        $html .= 'div.appendChild(topDiv);';
        $html .= 'div.appendChild(contentDiv);';
        $html .= 'div.style.display = "none";';
        $html .= 'panes.floatPane.appendChild(div);';
        $html .= 'this.panMap();';
        $html .= '} else if (div.parentNode != panes.floatPane) {';
        $html .= 'div.parentNode.removeChild(div);';
        $html .= 'panes.floatPane.appendChild(div);}else{}};';
        $html .= 'InfoBox.prototype.panMap = function(){';
        $html .= 'var map = this.map_;';
        $html .= 'var bounds = map.getBounds();';
        $html .= 'if (!bounds) return;';
        $html .= 'var position = this.latlng_;';
        $html .= 'var iwWidth = this.width_;';
        $html .= 'var iwHeight = this.height_;';
        $html .= 'var iwOffsetX = this.offsetHorizontal_;';
        $html .= 'var iwOffsetY = this.offsetVertical_;';
        $html .= 'var padX = 40;var padY = 40;';
        $html .= 'var mapDiv = map.getDiv();';
        $html .= 'var mapWidth = mapDiv.offsetWidth;';
        $html .= 'var mapHeight = mapDiv.offsetHeight;';
        $html .= 'var boundsSpan = bounds.toSpan();';
        $html .= 'var longSpan = boundsSpan.lng();';
        $html .= 'var latSpan = boundsSpan.lat();';
        $html .= 'var degPixelX = longSpan / mapWidth;';
        $html .= 'var degPixelY = latSpan / mapHeight;';
        $html .= 'var mapWestLng = bounds.getSouthWest().lng();';
        $html .= 'var mapEastLng = bounds.getNorthEast().lng();';
        $html .= 'var mapNorthLat = bounds.getNorthEast().lat();';
        $html .= 'var mapSouthLat = bounds.getSouthWest().lat();';
        $html .= 'var iwWestLng = position.lng() + (iwOffsetX - padX) * degPixelX;';
        $html .= 'var iwEastLng = position.lng() + (iwOffsetX + iwWidth + padX) * degPixelX;';
        $html .= 'var iwNorthLat = position.lat() - (iwOffsetY - padY) * degPixelY;';
        $html .= 'var iwSouthLat = position.lat() - (iwOffsetY + iwHeight + padY) * degPixelY;';
        $html .= 'var shiftLng = (iwWestLng < mapWestLng ? mapWestLng - iwWestLng : 0) + (iwEastLng > mapEastLng ? mapEastLng - iwEastLng : 0);';
        $html .= 'var shiftLat = (iwNorthLat > mapNorthLat ? mapNorthLat - iwNorthLat : 0) + (iwSouthLat < mapSouthLat ? mapSouthLat - iwSouthLat : 0);';
        $html .= 'var center = map.getCenter();';
        $html .= 'var centerX = center.lng() - shiftLng;';
        $html .= 'var centerY = center.lat() - shiftLat;';
        $html .= 'map.setCenter(new google.maps.LatLng(centerY, centerX));';
        
        /* Remove the listener after panning is complete.
         * Nur wenn zoom bei hover nicht nötig.
         */
        //$html .= 'google.maps.event.removeListener(this.boundsChangedListener_);this.boundsChangedListener_ = null;';
        
        $html .= '};';
        $html .= 'function initialize(){';
        $html .= 'var myOptions={zoom:10,';
        //$html .= 'minZoom:10,maxZoom:10,';
        $html .= 'zoomControl:false,scaleControl:false,scrollwheel:false,disableDoubleClickZoom:true,';
        $html .= 'center:new google.maps.LatLng('.$Lat.','.$Lng.'),mapTypeId:google.maps.MapTypeId.ROADMAP,sensor:"true",disableDefaultUI:true};';
        $html .= 'var map=new google.maps.Map(document.getElementById("map_canvas"),myOptions);';
        $html .= 'map.setOptions({ draggable : false });';
        $html .= 'var marker=new google.maps.Marker({icon:"/images/point.png",position:new google.maps.LatLng('.$Lat.','.$Lng.'),map:map,cursor:"default"});';
        $html .= 'google.maps.event.addListener(marker,"click",function(e){';
        $html .= 'var infoBox=new InfoBox({latlng:marker.getPosition(),map:map});});';
        //Zoom bei hover
        //$html .= 'google.maps.event.addListener(map, "mouseover", function(e) {map.setCenter(marker.getPosition());map.setZoom(14);});';
        //$html .= 'google.maps.event.addListener(map,"mouseout",function(e){map.hover=false;map.setCenter(marker.getPosition());map.setZoom(6);});';
        //End zoom
        $html .= 'google.maps.event.trigger(marker, "click");}$(window).load(function(){initialize();});</script>';
    
        
        return $html;
    }
}