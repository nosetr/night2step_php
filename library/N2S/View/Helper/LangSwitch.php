<?php
class N2S_View_Helper_LangSwitch extends Zend_View_Helper_Abstract
{
    function langSwitch()
    {
        $session = new Zend_Session_Namespace('userlanguage');
        $html = '<div id="n2s-langswitch"><ul><li>';
        if ($session->language != 'en'){
            $html .= '<a href="'.$this->view->url(array("lang"=>"en")).'">English</a>';
        }else{
            $html .= '<b>English</b>';  
        }
        $html .= '</li><li>';
        if ($session->language != 'de'){
            $html .= '<a href="'.$this->view->url(array("lang"=>"de")).'">Deutsch</a>';
        }else{
            $html .= '<b>Deutsch</b>';  
        }
        $html .= '</li><li>';
        if ($session->language != 'ru'){
            $html .= '<a href="'.$this->view->url(array("lang"=>"ru")).'">Русский</a>';
        }else{
            $html .= '<b>Русский</b>';  
        }
        $html .= '</li></ul></div>';
        
        return $html;
    }
}