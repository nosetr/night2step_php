<?php

/**
 * Access.php
 * Description of Access
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 13.09.2012 15:15:38
 * 
 */
class Community_Model_Access
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
            $this->setDbTable('Community_Model_DbTable_Users');
        }
        return $this->_dbTable;
    }
    
    public function addProfil($arrayData)
    {
        $table = $this->getDbTable();
        $registerDate = new Zend_Date(date('Y-m-d H:i:s'));
        $data = array(
            'registerDate' => $registerDate->get(Zend_Date::TIMESTAMP),
            'usertype' => 'Registered',
            'name'=>$arrayData['name'],
            'type'=>$arrayData['type'],
            'avatar'=>$arrayData['avatar'],
            'password' =>$this->_setPassword($arrayData['password'])
        );
        $table->insert($data);
        $result = $table->getAdapter()->lastInsertId();
        return $result;
    }

    public function addUser($data, $setActivation = TRUE)
    {
        $prefString = $this->_generatePassword(12,FALSE);
        $registerDate = new Zend_Date(date('Y-m-d H:i:s'));
        
        if($setActivation == TRUE){
            $password = $data['user_password'];
            $activation = $this->_setActivation();
            $crypActivation = $activation['cryp'];
        } else {
            $password = $this->_generatePassword();
            $crypActivation = '';
        }
        
        $arrayData = array(
            'name' => trim($data['firstname']).' '.trim($data['lastname']),
            'firstname' => trim($data['firstname']),
            'lastname' => trim($data['lastname']),
            'email' => trim($data['user_email']),
            'password' =>  $this->_setPassword($password),
            'registerDate' => $registerDate->get(Zend_Date::TIMESTAMP),
            'usertype' => 'Registered',
            'activation' => $crypActivation,
            'gender' => $data['gender'],
            'emailPref' => $prefString,
            'type'=>'profil'
        );
        $table = $this->getDbTable();
        $table->insert($arrayData);
        
        $userID = $table->getAdapter()->lastInsertId();
        $birthDate = new Zend_Date($data['birthdate']);
        $date = $birthDate->get(Zend_Date::TIMESTAMP);
        
        $about = new Community_Model_UserAbout();
        $about->setBirthdate($userID, $date);
        
        if($setActivation == TRUE){
            $result = array('id'=>$userID,
                'activator'=>$activation['key'],
                'name'=>trim($data['firstname']));
        } else {
            $result = $userID;
        }
        
        return $result;       
    }
    
    public function setRequestAccess($email,$act=NULL)
    {
        $table = $this->getDbTable();
        
        $result = $table->fetchRow(
                $table->select()
                    ->where('email = ?', $email)
                );
        
        if (isset($result) && $act != NULL){
            if ($act == "activ" && $result['activation'] == ''){
                $return = array('error'=>TRUE);
            } else {
                switch ($act) {
                    case "pass":
                        $new = $this->_generatePassword();
                        $data = array('password' => $this->_setPassword($new));
                        break;
                    case "activ":
                        $activ = $this->_setActivation();
                        $new = $activ['key'];
                        $data = array('activation' => $activ['cryp']);
                        break;
                }

                $where = $table->getAdapter()->quoteInto('email = ?', $email);
                $table->update($data, $where);

                $return = array('error'=>FALSE,'id'=>$result['userid'],'new'=>$new,'name'=>$result['name']);
            }
            return $return;
        }
    }
    
    public function setOAuthAccess($email, $userID)
    {
        $auth = Zend_Auth::getInstance();
        $authAdapter = new N2S_Auth_AuthAdapter(NULL, 'userid');
        
        $authAdapter->setIdentity($email);
        $authAdapter->setCredential($userID);
        $result = $auth->authenticate($authAdapter);
        
        if (!$result->isValid()) {
            return FALSE;
        } else {
            $sessionKey = Zend_Registry::get('config')->authsession->default->key;
            $storage = $auth->getStorage();
            $storage->write($authAdapter->getResultRowObject(array('userid','usertype','type')));
            $session = new Zend_Session_Namespace($sessionKey);
            //$expirsec = Zend_Registry::get('config')->authsession->lifetime->dontremember->sec;
            //$session->setExpirationSeconds($expirsec);
            Zend_Session::rememberMe();
            
            $UMod = new Community_Model_Users();
            $UMod->updateLastVisit($auth->getIdentity()->userid);
            $U = $UMod->getUser($auth->getIdentity()->userid);
            if($U->deactivated == '1'){
                @set_time_limit(10 * 60);
                $ajaxModel = new Default_Model_Ajaxlist();
                $ajaxModel->setActive($U->userid);
                $comModel = new Default_Model_Comments();
                $comModel->setActive($U->userid);
                $arraydata = array(
                    'deactivated' => '0');
                $UMod->updateProfil($auth->getIdentity()->userid, $arraydata);
                return 'activated';
            } else {
                return true;
            }
        }
    }

    public function setAccess($email,$password,$rememberme,$activation,$identityKey = null,$expirsec = null)
    {
        $changedSess = Zend_Registry::get('config')->authsession->changed->key;
        if($identityKey == null){
            $sessionKey = Zend_Registry::get('config')->authsession->default->key;
            $activator = $this->_getActivation($email,$activation);
            if(Zend_Session::namespaceIsset($changedSess) == TRUE)
                Zend_Session::namespaceUnset($changedSess);
        } else {
            $sessionKey = $changedSess;
            $activator = TRUE;
        }
        if ($activator == TRUE){
            $auth = Zend_Auth::getInstance();
            $crypt = $this->_getCrypt($email,$password,$identityKey);

            $authAdapter = new N2S_Auth_AuthAdapter($identityKey);
            $authAdapter->setIdentity($email);
            $authAdapter->setCredential($crypt);
            $result = $auth->authenticate($authAdapter);
            if (!$result->isValid()) {
                return FALSE;
            } else {
                if($identityKey == null)
                    $this->_delActivation($email);
                $storage = $auth->getStorage();
                //$storage->write($authAdapter->getResultRowObject(null, 'password'));
                $storage->write($authAdapter->getResultRowObject(array('userid','usertype','type')));
                $session = new Zend_Session_Namespace($sessionKey);
                if($rememberme == 'Checked'){
                    Zend_Session::rememberMe();
                } else {
                    if($expirsec == NULL)
                        $expirsec = Zend_Registry::get('config')->authsession->lifetime->dontremember->sec;
                    $session->setExpirationSeconds($expirsec);//(24*3600 für 1 Tag)
                }
                $UMod = new Community_Model_Users();
                $UMod->updateLastVisit($auth->getIdentity()->userid);
                $U = $UMod->getUser($auth->getIdentity()->userid);
                if($U->deactivated == '1'){
                    @set_time_limit(10 * 60);
                    $ajaxModel = new Default_Model_Ajaxlist();
                    $ajaxModel->setActive($U->userid);
                    $comModel = new Default_Model_Comments();
                    $comModel->setActive($U->userid);
                    $arraydata = array(
                        'deactivated' => '0');
                    $UMod->updateProfil($auth->getIdentity()->userid, $arraydata);
                    return 'activated';
                } else {
                    return true;
                }
            }
        } else {
            return 'wait';
        }
    }
    
    public function setEmailPref($email)
    {
        $prefString = $this->_generatePassword(12,FALSE);
        $data = array(
            'emailPref' => $prefString
        );
        $table = $this->getDbTable();
        $where = $table->getAdapter()->quoteInto('email = ?', $email);
        $table->update($data, $where);
        
        return $prefString;        
    }
    
    public function setEmailPrefID($id)
    {
        $prefString = $this->_generatePassword(12,FALSE);
        $data = array(
            'emailPref' => $prefString
        );
        $table = $this->getDbTable();
        $where = $table->getAdapter()->quoteInto('userid = ?', $id);
        $table->update($data, $where);
        
        return $prefString;        
    }
    
    public function updatePass($id,$pass)
    {
        $table = $this->getDbTable();
        $data = array('password' => $this->_setPassword($pass));
        $where = $table->getAdapter()->quoteInto('userid = ?', $id);
        $table->update($data, $where);
    }

    public function _getEmailInfo($email,$password)
    {
        $table = $this->getDbTable();
        
        $selectpass = $table->fetchRow(
                $table->select()
                    ->where('email = ?', $email)
                );
        
        $crypt = $this->_getCrypt($email, $password);
        
        if ($selectpass['password'] == $crypt){
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function _getUserPass($password)
    {
        $result = false;
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity() && $auth->getIdentity()->type == 'profil'){
            $table = $this->getDbTable();
        
            $selectpass = $table->fetchRow(
                $table->select()
                    ->where('userid = ?', $auth->getIdentity()->userid)
            );
            if(isset($selectpass)){
                $crypt = $this->_getCrypt($selectpass['email'], $password);
                if ($selectpass['password'] == $crypt)
                    $result = TRUE;
            }
        }
        return $result;
    }
    
    protected function _generatePassword($pwlen=10,$stand=TRUE)
    {
	mt_srand();
        if($stand == TRUE){
            $salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        } else {
            $salt = "abcdefghijklmnopqrstuvwxyz0123456789";
        }
	
        $pw = '';
	for($i=0;$i<$pwlen;$i++)
	{
		$pw .= $salt[mt_rand(0, strlen($salt)-1)];
	}
	return $pw;
    }
    
    protected function _setPassword($pass)
    {
        mt_srand((double)microtime()*1000000);
        $salt = md5(uniqid(rand(), true));
        $cryp = md5($pass.$salt).':'.$salt;
        
        return $cryp;
    }
    
    protected function _setActivation()
    {
        mt_srand((double)microtime()*1000000);
        $confirmKey = md5(uniqid(rand(), true));
        $salt = md5(uniqid(rand(), true));
        $cryp = md5($confirmKey.$salt).':'.$salt;
        
        $result = array('cryp'=>$cryp,'key'=>$confirmKey);
        return $result;
    }
    
    protected function _getActivation($email,$activation)
    {
        $table = $this->getDbTable();
        
        $selectpass = $table->fetchRow(
                $table->select()
                    ->where('email = ?', $email)
                );

        if ($selectpass['activation'] == ''){
            return TRUE;
        } else {
            $parts	= explode( ':', $selectpass['activation'] );
            $salt	= @$parts[1];
            $crypt = md5($activation.$salt).':'.$salt;
            
            if ($crypt === $selectpass['activation']){
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }
    
    protected function _delActivation($email)
    {
        $table = $this->getDbTable();
        $data = array('activation'=>'');
        $where = $table->getAdapter()->quoteInto('email = ?', $email);
        $table->update($data, $where);
    }

    protected function _getCrypt($email,$password,$default=null)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        if($default == null){
            $select->where('email = ?', $email);
        } else {
            $select->where('userid = ?', $email);
        }
        
        $selectpass = $table->fetchRow($select);

        $parts	= explode( ':', $selectpass['password'] );
        $salt	= @$parts[1];
        $crypt = md5($password.$salt).':'.$salt;
        return $crypt;
    }
}
