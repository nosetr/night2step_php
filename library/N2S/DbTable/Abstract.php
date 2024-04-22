<?php

/**
 * Abstract.php
 * Description: for seting of DbTable-Prefix
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 13:57:39
 * 
 */
abstract class N2S_DbTable_Abstract extends Zend_Db_Table_Abstract
{
        protected function _setupTableName()
        {
                 parent::_setupTableName();

                 if (Zend_Registry::isRegistered('TABLE_PREFIX'))
                 {
                        $pref = Zend_Registry::get('TABLE_PREFIX');
                        $this->_name = $pref . $this->_name;
                 }
        }
}