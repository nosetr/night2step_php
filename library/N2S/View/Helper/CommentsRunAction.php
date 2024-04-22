<?php

/**
 * CommentsRunAction.php
 * Description of CommentsRunAction
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 25.03.2013 09:53:46
 * 
 */
class N2S_View_Helper_CommentsRunAction extends Zend_View_Helper_Abstract
{
    function commentsRunAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $url = $this->view->url(array('module'=>'default',
                'controller'=>'comment',
                'action'=>'ajax',
                'task'=>'runaction'),'default', true);
            $html = "$(window).unload(function(){ $.post('".$url."',{});});";
            return $html;
        }
    }
}
