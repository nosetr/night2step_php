<?php

/**
 * AdressesGeoloc.php
 * Description of AdressesGeoloc
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.06.2013 10:05:15
 * 
 */
class Default_Model_AdressesGeoloc
{
    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Default_Model_DbTable_AdressesGeoloc');
        }
        return $this->_dbTable;
    }
    
    public function getAddress($request,$lang)
    {
        $lang = strtolower($lang);
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                    $table->select()
                        ->where('request = ?',$request)
                        ->where('lang = ?',$lang)
                );
        if($result)
            $result = $result->toArray();
        return $result;
    }
    
    public function setAddress($data,$request,$lang)
    {
        $data['request'] = $request;
        $data['lang'] = strtolower($lang);
        $table = $this->getDbTable();
        $table->insert($data);
    }
}
