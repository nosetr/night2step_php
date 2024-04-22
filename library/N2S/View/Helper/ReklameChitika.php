<?php

/**
 * Reklame.php
 * Description of Reklame
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 02.11.2012 18:52:54
 * 
 */
class N2S_View_Helper_ReklameChitika extends Zend_View_Helper_Abstract
{
    function reklameChitika($mod=1)
    {
        if($mod == 1){
            $html = '<script type="text/javascript">(function(){if(window.CHITIKA===undefined){window.CHITIKA={"units":[]};};var unit={"publisher":"nosetr","width":250,"height":250,"type":"mpu","sid":"Chitika Default","color_site_link":"CC0000","color_title":"CC0000","color_border":"FFFFFF","color_text":"333333","color_bg":"FFFFFF"};var placement_id=window.CHITIKA.units.length;window.CHITIKA.units.push(unit);document.write(\'<div id="chitikaAdBlock-\' + placement_id + \'"></div>\');var s=document.createElement("script");s.type="text/javascript";s.src="http://scripts.chitika.net/getads.js";try{document.getElementsByTagName("head")[0].appendChild(s);}catch(e){document.write(s.outerHTML);}}());</script>';
        } elseif ($mod == 2) {
            $html = '<script type="text/javascript">(function(){if(window.CHITIKA===undefined){window.CHITIKA={"units":[]};};var unit={"publisher":"nosetr","width":300,"height":250,"type":"mpu","sid":"Chitika Default","color_site_link":"CC0000","color_title":"CC0000","color_border":"FFFFFF","color_text":"333333","color_bg":"FFFFFF"};var placement_id=window.CHITIKA.units.length;window.CHITIKA.units.push(unit);document.write(\'<div id="chitikaAdBlock-\' + placement_id + \'"></div>\');var s=document.createElement("script");s.type="text/javascript";s.src="http://scripts.chitika.net/getads.js";try{document.getElementsByTagName("head")[0].appendChild(s);}catch(e){document.write(s.outerHTML);}}());</script>';
        } else {
            $html = '';
        }
        return $html;
    }
}