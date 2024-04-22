<?php

/**
 * LayoutLoader.php
 * Description of LayoutLoader
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 16:01:51
 * 
 */
class N2S_Layout_LayoutLoader extends Zend_Controller_Action_Helper_Abstract
{
    public function preDispatch()
    {
        $bootstrap = $this->getActionController()
                         ->getInvokeArg('bootstrap');
        $config = $bootstrap->getOptions();
        $module = $this->getRequest()->getModuleName();
        if(empty($module)) $module = "default";
        if (isset($config[$module]['resources']['layout']['layout'])) {
            $layoutScript =
                 $config[$module]['resources']['layout']['layout'];
        } else {
            $layoutScript =
                 $config['default']['resources']['layout']['layout'];
        }
        $this->getActionController()
                 ->getHelper('layout')
                 ->setLayout($layoutScript);
    }
}