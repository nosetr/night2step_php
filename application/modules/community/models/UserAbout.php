<?php

/**
 * UserAbout.php
 * Description of UserAbout
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.03.2013 10:03:02
 * 
 */
class Community_Model_UserAbout
{
    protected $_dbTable;
    protected $_dbJoinTable;

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
            $this->setDbTable('Community_Model_DbTable_UserAboutValues');
        }
        return $this->_dbTable;
    }
    
    public function setJoinDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbJoinTable = $dbTable;
        return $this;
    }

    public function getJoinDbTable()
    {
        if (null === $this->_dbJoinTable) {
            $this->setJoinDbTable('Community_Model_DbTable_UserAbout');
        }
        return $this->_dbJoinTable;
    }
    
    public function getAllAbout($userID,$permis = 0)
    {
        $tableTab = $this->getDbTable();
        $table = $tableTab->info();
        
        $joinTab = $this->getJoinDbTable();
        $join = $joinTab->info();
        
        $select = $tableTab->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                            ->setIntegrityCheck(false);
        $select->where('user_id = ?',$userID)
            ->where('permission <= ?',$permis);
        $select->joinLeft(array('a' => $join['name']), $table['name'].'.param_id = a.aid')
            ->order(array('a.order ASC',
                    'a.aid ASC'));
        $result = $tableTab->fetchAll($select);
        
        return $result;
    }
    
    public function getAbout($userID,$paramID)
    {
        $table = $this->getDbTable();
        $result = $table->fetchRow(
                $table->select()
                    ->where('user_id = ?',$userID)
                    ->where('param_id = ?',$paramID)
                );
        return $result;
    }

    public function getJoin($name)
    {
        $table = $this->getJoinDbTable();
        $result = $table->fetchRow(
                $table->select()
                    ->where('name = ?',$name)
                );
        
        if(!isset($result)){
            $result = $this->setJoin($name);
        } else {
            $result = $result['aid'];
        }
        
        return $result;
    }

    public function setAbout($data)
    {
        $table = $this->getDbTable();
        $table->insert($data);
    }
    
    public function setJoin($name)
    {
        $table = $this->getJoinDbTable();
        $data = array(
            'name'=>$name
        );
        $table->insert($data);
        return $table->getAdapter()->lastInsertId();
    }
    
    public function setBirthdate($userID,$date,$permis = '0') //$date musst be a timestamp
    {
        $paramID = $this->getJoin('birthdate');
        $arrData = array(
            'user_id'=>$userID,
            'param_id'=>$paramID,
            'permission'=>$permis,
            'value'=>$date
        );
        $check = $this->getAbout($userID, $paramID);
        if(!isset($check)){
            $this->setAbout($arrData);
        }
    }

    public function updateAbout($userID,$paramID,$data)
    {
        $table = $this->getDbTable();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('user_id = ?', $userID);
        $where[] = $table->getAdapter()->quoteInto('param_id = ?', $paramID);
        $table->update($data, $where);
    }
    
    public function delAbout($userID,$paramID)
    {
        $table = $this->getDbTable();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('user_id = ?', $userID);
        $where[] = $table->getAdapter()->quoteInto('param_id = ?', $paramID);
        $table->delete($where);
    }

    public function checkAbout($userID, $data)
    {
        if(count($data) > 0){
            if (isset($data['birthdate']) && isset($data['permis_birthdate'])){
                $paramID = $this->getJoin('birthdate');
                $arrData = array(
                    'user_id'=>$userID,
                    'param_id'=>$paramID,
                    'permission'=>$data['permis_birthdate']
                );
                $check = $this->getAbout($userID, $paramID);
                if(isset($check)){
                    $this->updateAbout($userID, $paramID, $arrData);
                } else {
                    $time = new Zend_Date($data['birthdate']);
                    $arrData['value'] = $time->get(Zend_Date::TIMESTAMP);
                    $this->setAbout($arrData);
                }
                
                unset($arrData);
            }
            if (isset($data['curcity']) && isset($data['permis_curcity'])){
                $paramID = $this->getJoin('curcity');
                $arrData = array(
                    'user_id'=>$userID,
                    'param_id'=>$paramID,
                    'value'=>trim($data['curcity']),
                    'permission'=>$data['permis_curcity']
                );
                $check = $this->getAbout($userID, $paramID);
                if(isset($check)){
                    $this->updateAbout($userID, $paramID, $arrData);
                } else {
                    $this->setAbout($arrData);
                }
                unset($arrData);
            }
            if (isset($data['about']) && isset($data['permis_about'])){
                $paramID = $this->getJoin('about');
                $arrData = array(
                    'user_id'=>$userID,
                    'param_id'=>$paramID,
                    'value'=>trim($data['about']),
                    'permission'=>$data['permis_about']
                );
                $check = $this->getAbout($userID, $paramID);
                if(isset($check)){
                    $this->updateAbout($userID, $paramID, $arrData);
                } else {
                    $this->setAbout($arrData);
                }
                unset($arrData);
            }
            if (isset($data['hometown']) && isset($data['permis_hometown'])){
                $paramID = $this->getJoin('hometown');
                $arrData = array(
                    'user_id'=>$userID,
                    'param_id'=>$paramID,
                    'value'=>trim($data['hometown']),
                    'permission'=>$data['permis_hometown']
                );
                $check = $this->getAbout($userID, $paramID);
                if(isset($check)){
                    $this->updateAbout($userID, $paramID, $arrData);
                } else {
                    $this->setAbout($arrData);
                }
                unset($arrData);
            }
            if (isset($data['school']) && isset($data['permis_school'])){
                $paramID = $this->getJoin('school');
                $arrData = array(
                    'user_id'=>$userID,
                    'param_id'=>$paramID,
                    'value'=>trim($data['school']),
                    'permission'=>$data['permis_school']
                );
                $check = $this->getAbout($userID, $paramID);
                if(isset($check)){
                    $this->updateAbout($userID, $paramID, $arrData);
                } else {
                    $this->setAbout($arrData);
                }
                unset($arrData);
            }
            if (isset($data['uni']) && isset($data['permis_uni'])){
                $paramID = $this->getJoin('uni');
                $arrData = array(
                    'user_id'=>$userID,
                    'param_id'=>$paramID,
                    'value'=>trim($data['uni']),
                    'permission'=>$data['permis_uni']
                );
                $check = $this->getAbout($userID, $paramID);
                if(isset($check)){
                    $this->updateAbout($userID, $paramID, $arrData);
                } else {
                    $this->setAbout($arrData);
                }
                unset($arrData);
            }
            if (isset($data['work']) && isset($data['permis_work'])){
                $paramID = $this->getJoin('work');
                $arrData = array(
                    'user_id'=>$userID,
                    'param_id'=>$paramID,
                    'value'=>trim($data['work']),
                    'permission'=>$data['permis_work']
                );
                $check = $this->getAbout($userID, $paramID);
                if(isset($check)){
                    $this->updateAbout($userID, $paramID, $arrData);
                } else {
                    $this->setAbout($arrData);
                }
                unset($arrData);
            }
        }
    }
}