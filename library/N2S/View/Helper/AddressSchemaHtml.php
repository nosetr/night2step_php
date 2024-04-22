<?php

/**
 * AddressSchemaHtml.php
 * Description of AddressSchemaHtml
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 24.05.2013 12:26:09
 * 
 */
class N2S_View_Helper_AddressSchemaHtml extends Zend_View_Helper_Abstract
{
    function addressSchemaHtml($address)
    {
        $html = '<span id="lcAddrs"';
        $geoloc = N2S_GeoCode_GoogleGeocode::googleGeocode($address);
            
        if(is_array($geoloc) && count($geoloc) > 0){
            $e = 0;
            $html .= ' itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
            if(isset($geoloc['route'])){
                $html .= '<span itemprop="streetAddress">'.$geoloc['route'];
                if(isset($geoloc['street_number']))
                    $html .= ' '.$geoloc['street_number'];
                $html .= '</span>';
                $e++;
            }
            if(isset($geoloc['locality'])){
                if($e > 0)
                    $html .= ', ';
                if(isset($geoloc['postal_code']))
                    $html .= '<span itemprop="postalCode">'.$geoloc['postal_code'].'</span> ';
                $html .= '<span itemprop="addressLocality">'.$geoloc['locality'].'</span>';
                $e++;
            }
            if(isset($geoloc['country'])){
                if($e > 0)
                    $html .= ', ';
                $html .= '<span itemprop="addressCountry">'.$geoloc['country'].'</span>';
            }
        } else {
            $html .= '>'.$address;
        }
        
        $html .= '</span>';
        
        return $html;
    }
}
