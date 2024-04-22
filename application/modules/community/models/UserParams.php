<?php

/**
 * UserParams.php
 * Description: to get userparams from DB
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 13:40:39
 * 
 */
class Community_Model_UserParams
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
            $this->setDbTable('Community_Model_DbTable_UserParamsValues');
        }
        return $this->_dbTable;
    }
    
    public function getParam($userID,$param) //$param: param-name as string 
    {
        $params = new Community_Model_DbTable_UserParams();// list of params-arts
        $paramID = $params->checkID($param);
        
        if ($paramID == FALSE){
            return FALSE;
        }else{
            $paramsTab = $this->getDbTable();
            $select = $paramsTab->select();
            $select->where('user_id = ?',$userID)
                    ->where('param_id = ?',$paramID);
            
            $result = $paramsTab->fetchRow($select);
            
            if ($result){
                return $result->value;
            } else {
                return FALSE;
            }
        }
    }
    
    /*
     * N2S_Language_LangSelector    line:77
     */
    public function setParam($userID,$param_name,$param,$update = FALSE)
    {
        $table = $this->getDbTable();
        
        if ($update == FALSE){
            $params = new Community_Model_DbTable_UserParams();
            $paramID = $params->checkID($param_name);

            $data = array(
                'user_id'=> $userID,
                'param_id'=> $paramID,
                'value' => $param
            );
            $table->insert($data);
        } else {
            $data = array(
                'value' => $param
            );
            $where = $table->getAdapter()->quoteInto('user_id = ?', $userID);
            $table->update($data, $where);
        }
    }
}
