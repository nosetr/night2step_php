<?php

/**
 * UserParams.php
 * Description of UserParams
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 14:01:45
 * 
 */
class Community_Model_DbTable_UserParams extends N2S_DbTable_Abstract
{

    protected $_name = 'userparams';

    function checkID($param)
    {
        $select = $this->_db->select()
                            ->from($this->_name,array('id'))
                            ->where('name=?',$param);
        $result = $this->getAdapter()->fetchOne($select);
        if($result){
            return $result;
        }
        return false;
    }
}
