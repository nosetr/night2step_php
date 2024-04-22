<?php

/**
 * OpenStreetGeocode.php
 * Description of OpenStreetGeocode
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 03.06.2013 09:28:35
 * 
 */
class N2S_GeoCode_OpenStreetGeocode
{
    public function getJson($adress,$lang = null,$check = TRUE)
    {
        if($lang == NULL){
            $sessionlang = new Zend_Session_Namespace('userlanguage');
            if(isset($sessionlang->language))
                $lang = $sessionlang->language;
        }
        if (preg_match("@^[a-zA-Z0-9%+-_]*$@", $adress)) {
            $urlencodedAddress = $adress;
        } else {
            $urlencodedAddress = urlencode($adress);
        }
        $get = array();
        
        if($check == TRUE && $lang != NULL){
            $checkModel = new Default_Model_AdressesGeoloc();
            $get = $checkModel->getAddress($urlencodedAddress, $lang);
        }
        
        if(count($get) == 0){
            ($lang) ? $langrequest = '&accept-language='.$lang : $langrequest = '';
            $details_url = "http://nominatim.openstreetmap.org/search.php?q=" . $urlencodedAddress . "&format=json&addressdetails=1&limit=1" .$langrequest;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $details_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $geoloc = json_decode(curl_exec($ch), true);
            
            if(is_array($geoloc) &&
                    count($geoloc) > 0 &&
                    isset($geoloc[0]['address']) &&
                    isset($geoloc[0]['boundingbox'])){
                $pos = $geoloc[0]['address'];
                
                if(isset($pos['village'])){
                    $city = $pos['village'];
                } elseif(isset ($pos['town'])) {
                    $city = $pos['town'];
                } elseif(isset ($pos['city'])) {
                    $city = $pos['city'];
                } elseif(isset ($pos['state'])) {
                    $city = $pos['state'];
                }
                
                if(isset($city))
                    $get['locality'] = $city;
                
                if(isset($pos['country'])){
                    $country = $pos['country'];
                    $get['country'] = $country;
                    $get['short_country'] = strtoupper($pos['country_code']);
                }
                
                $view = $geoloc[0]['boundingbox'];
                
                $get['lat'] = $geoloc[0]['lat'];
                $get['lng'] = $geoloc[0]['lon'];
                $get['ne_lat'] = $view[0];
                $get['ne_lng'] = $view[2];
                $get['sw_lat'] = $view[1];
                $get['sw_lng'] = $view[3];
                
                if(isset($city) && isset($country)){
                    $get['formatted_address'] = $city.', '.$country;
                } elseif (isset ($country)) {
                    $get['formatted_address'] = $country;
                } else {
                    $get = array();
                }
                
                if($check == TRUE && $lang != NULL && count($get) > 0){
                    $checkModel->setAddress($get, $urlencodedAddress, $lang);
                }
            }
        }
        
        return $get;
    }
}
