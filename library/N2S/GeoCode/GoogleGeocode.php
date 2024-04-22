<?php

/**
 * GoogleGeocode.php
 * Description: helper to take an info from googlemaps
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 14:20:37
 * 
 */
class N2S_GeoCode_GoogleGeocode
{
    public function googleGeocode($adress,$lang = null,$check = TRUE)
    {
        //Adress musst be in form: $Adress.', '.$City.', '.$Land
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
            ($lang) ? $langrequest = '&language='.$lang : $langrequest = '';
            $details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $urlencodedAddress . "&sensor=false" .$langrequest;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $details_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $geoloc = json_decode(curl_exec($ch), true);
            
            if($geoloc['status'] == 'OK' && isset($geoloc['results'][0]['types'][0])){
                $get['formatted_address'] = $geoloc['results'][0]['formatted_address'];
                
                $get['lat'] = $geoloc['results'][0]['geometry']['location']['lat'];
                $get['lng'] = $geoloc['results'][0]['geometry']['location']['lng'];
                
                $view = $geoloc['results'][0]['geometry']['viewport'];
                if(isset($view)){
                    $get['ne_lat'] = $view['northeast']['lat'];
                    $get['ne_lng'] = $view['northeast']['lng'];
                    $get['sw_lat'] = $view['southwest']['lat'];
                    $get['sw_lng'] = $view['southwest']['lng'];
                }
                
                $pos = $geoloc['results'][0]['address_components'];
                if(isset($pos)){
                    for ($i=0; $i<sizeof($pos); $i++)
                    {
                        if($pos[$i]['types'][0] === 'route' ){
                            $get['route'] = $pos[$i]['long_name'];
                        }
                        if($pos[$i]['types'][0] === 'street_number' ){
                            $get['street_number'] = $pos[$i]['long_name'];
                        }
                        if($pos[$i]['types'][0] === 'postal_code' ){
                            $get['postal_code'] = $pos[$i]['long_name'];
                        }
                        if($pos[$i]['types'][0] === 'locality')
                            $city = $pos[$i]['long_name'];
                        if($pos[$i]['types'][0] === 'administrative_area_level_1')
                            $admin = $pos[$i]['long_name'];

                        if($pos[$i]['types'][0] === "country" ){
                            $get['country'] = $pos[$i]['long_name'];
                            $get['short_country'] = $pos[$i]['short_name'];
                        }
                    }
                    
                    if(!isset($city) && isset($admin)){
                        $city = $admin;
                    }
                    if($city){
                        $get['locality'] = $city;
                    }
                }
                
                if($check == TRUE && $lang != NULL){
                    $checkModel->setAddress($get, $urlencodedAddress, $lang);
                }
            }
        }
        
        return $get;
    }
}
