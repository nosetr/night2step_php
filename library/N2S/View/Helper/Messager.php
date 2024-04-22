<?php

/**
 * Messager.php
 * Description of Messager
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.01.2013 16:31:15
 * 
 */
class N2S_View_Helper_Messager extends Zend_View_Helper_Abstract
{
    function messager($userID,$style = null)
    {
        $html = '';
        if($userID != N2S_User::curuser()){
            $html .= '<a class="red n2s-message n2lbox.ajax" href="/message/send/uid/'.$userID.'">';
            $html .= '<div class="ajaxlink" style="';
            if($style)
                $html .= $style;
            $html .= 'background: url(/images/mail.png) no-repeat scroll 7px 3px #ffffff; padding: 2px 10px 2px 25px;">';
            $html .= $this->view->translate('send a message');
            $html .= '</div></a>';
        }
        return $html;
    }
}