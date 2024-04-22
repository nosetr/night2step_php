<?php

/**
 * Distance.php
 * Description of Distance
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 24.10.2012 15:55:48
 * 
 */
class N2S_View_Helper_Distance extends Zend_View_Helper_Abstract
{
    function distance($lat1, $lng1, $lat2, $lng2)
    {
        $dist = acos(sin($lat1/180*M_PI)*sin($lat2/180*M_PI)
        + cos($lat1/180*M_PI)*cos($lat2/180*M_PI)*cos($lng1/180*M_PI-$lng2/180*M_PI) ) * (6378.137);
        return $dist;
    }
}
