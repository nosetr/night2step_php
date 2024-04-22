<?php

/**
 * Users.php
 * Description of Users
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 15:10:36
 * 
 */
class Community_Model_DbTable_Users extends N2S_DbTable_Abstract
{

    protected $_name = 'users';
    function checkUnique($email)
    {
        $select = $this->_db->select()
                            ->from($this->_name,array('email'))
                            ->where('email=?',$email);
        $result = $this->getAdapter()->fetchOne($select);
        if($result){
            return true;
        }
        return false;
    }

}