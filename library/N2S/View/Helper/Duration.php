<?php
class N2S_View_Helper_Duration extends Zend_View_Helper_Abstract
{
    function duration($secs) {
        $hours = intval($secs / (60 * 60));
        $secs  = $secs % (60 * 60);
        $mins  = intval($secs / 60);
        $secs  = $secs % 60;
        if($hours > 0){
            $hours = $hours.":";
        } else {
            $hours = "";
        }
        if(strlen($mins)==1){
          $mins = "0".$mins;
        }
        if(strlen($secs)==1){
          $secs = "0".$secs;
        }
        
        $Time = $hours.$mins.":".$secs;
        
        return $Time;
    }
}