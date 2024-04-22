<?php

/**
 * TimeStamp.php
 * Zend_Date::SECOND_SHORT     Sekunde, (0-59), eine oder zwei Ziffern
 * Zend_Date::MINUTE_SHORT     Minute, (0-59), eine oder zwei Ziffern
 * Zend_Date::HOUR_SHORT       Stunde, (0-23), eine oder zwei Ziffern
 * Zend_Date::DAY_SHORT        Monatstag, eine oder zwei Ziffern
 * Zend_Date::WEEK             Woche, eine oder zwei Ziffern
 * Zend_Date::MONTH_SHORT      Monat, eine oder zwei Ziffern
 * Zend_Date::YEAR             Jahr, mindestens eine Ziffer
 * 
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 31.10.2012 13:05:18
 * 
 */
/*
 
 */
class N2S_View_Helper_TimeStamp extends Zend_View_Helper_Abstract
{
    function timeStamp($date, $days = 0)
    {
        date_default_timezone_set('Europe/Berlin'); 
 
         $oVonDatum = new Zend_Date($date);
         $oBisDatum = Zend_Date::now();
         
         if ($days == 0 ){
            $nDiff = $oBisDatum->getTimestamp() - $oVonDatum->getTimestamp();
            $day = floor($nDiff / (24*60*60));
            
            if($day == 0){
                $hD = floor($nDiff / (60*60));
                $mD = floor($nDiff / (60));
                $sD = floor($nDiff);
    //            $minut = $oBisDatum->get(Zend_Date::MINUTE_SHORT) - $oVonDatum->get(Zend_Date::MINUTE_SHORT);
    //            $hour = $oBisDatum->get(Zend_Date::HOUR_SHORT) - $oVonDatum->get(Zend_Date::HOUR_SHORT);
                if ($hD >= 1):
    //                $diff = $oBisDatum->get(Zend_Date::HOUR_SHORT) - $oVonDatum->get(Zend_Date::HOUR_SHORT);
                    ($hD > 1) ? $key = $this->view->langHelper('%d hours ago',$hD) : $key = $this->view->translate('%d hour ago');
                    $keyDate = sprintf($key, $hD);
                elseif ($mD >= 1):
                    ($mD > 1) ? $key = $this->view->langHelper('%d minutes ago',$mD) : $key = $this->view->translate('%d minute ago');
                    $keyDate = sprintf($key, $mD);
                else:
                    
    //                $keyDate = $this->view->translate($minut.' | '.$sD.' | '.$oVonDatum);
                    $keyDate = $this->view->translate('less than a minute ago');
                endif;
            } elseif($day == 1){
                $keyDate = $this->view->translate('yesterday');
            } elseif($day < 7){
                $diff = floor($day);
                ($diff > 1) ? $key = $this->view->langHelper('%d days ago',$diff): $key = $this->view->translate('%d day ago');
                $keyDate = sprintf($key, $diff);
            } elseif(($day >= 7) && ($day < 30)){
                $diff = floor($day/7);
                ($diff < 2) ? $key = $this->view->langHelper('%d week ago',$diff): $key = $this->view->translate('%d weeks ago');
                $keyDate = sprintf($key, $diff);
            } else if(($day >= 30)){
                $diff = floor($day/30);
                ($diff > 1) ? $key = $this->view->langHelper('%d months ago',$diff): $key = $this->view->translate('%d month ago');
                $keyDate = sprintf($key, $diff);
            }
        
        }else{
            $keyDate = $this->daysOnly($date);
        }
         
         return $keyDate;
    }
    
    function daysOnly($date)
    {
         $oVonDatum = new Zend_Date($date);  
         $oBisDatum = Zend_Date::now();   

         $nDiff = $oBisDatum->getTimestamp() - $oVonDatum->getTimestamp();
         $diff = floor($nDiff / (24*60*60));
         
         if($diff < 1){
                $hD = floor($nDiff / (60*60));
                $mD = floor($nDiff / (60));
                $sD = floor($nDiff);
                if ($hD >= 1):
                    ($hD > 1) ? $key = $this->view->langHelper('%d hours ago',$hD) : $key = $this->view->translate('%d hour ago');
                    $keyDate = sprintf($key, $hD);
                elseif ($mD >= 1):
                    ($mD > 1) ? $key = $this->view->langHelper('%d minutes ago',$mD) : $key = $this->view->translate('%d minute ago');
                    $keyDate = sprintf($key, $mD);
                else:
                    $keyDate = $this->view->translate('less than a minute ago');
                endif;
            } else {
                 ($diff > 1) ? $key = $this->view->langHelper('%d days ago',$diff): $key = $this->view->translate('%d day ago');
                 $keyDate = sprintf($key, $diff);
            }
         
         return $keyDate;
    }
}