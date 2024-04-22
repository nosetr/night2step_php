<?php

/**
 * OauthController.php
 * Description of Oauth2Controller
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 06.10.2013 12:54:14
 * 
 */

/*
 * Community_Model_UserConnect();
 * Community_Model_Users();
 * Community_Model_Access();
 * Community_Model_UserAbout();
 * N2S_SDK_FB_Facebook();
 * N2S_Filter_File_Resize();
 * N2S_Filter_File_Multicropthumb();
 * config;
 * Default_Model_PhotoAlbums();
 * Default_Model_Photos();
 */
class Default_OauthController extends Zend_Controller_Action
{
    public function init()
    {
        
    }

    public function loginAction()
    {
        $target = (string)$this->_request->getParam('target');
        $redirect = (string)$this->_request->getParam('rdrct',NULL);
        
        if($target && method_exists($this, '_'.$target))
        {
            $method = '_'.$target;
            $html = $this->$method($redirect);
            $this->view->html = $html;
        } else {
            $this->_helper->redirector('notfound','Error','default');
            return;
        }
    }
    
    public function logoutAction()
    {
        $html = $this->view->translate('Error');
        $auth = Zend_Auth::getInstance();
        $target = (string)$this->_request->getParam('target');
        if(isset($target) && $auth->hasIdentity()){
            $connectMod = new Community_Model_UserConnect();
            $connect = $connectMod->getConnect($target, NULL, $auth->getIdentity()->userid);
            if(count($connect) > 0){
                $connectMod->delConnect($target, $auth->getIdentity()->userid);
            
            if($target == 'facebook'){
                $appID = Zend_Registry::get('config')->socsdk->fb->appId;
                $secret = Zend_Registry::get('config')->socsdk->fb->secret;
                $facebook = new N2S_SDK_FB_Facebook(array(
                    'appId' => $appID,
                    'secret' => $secret,
                    'fileUpload' => true,
                    'cookie' => true
                ));

                $user = $facebook->getUser();
                if($user)
                    $facebook->destroySession ();
            }
            
            $redirect = (string)$this->_request->getParam('rdrct');
            if(isset($redirect))
                $this->_helper->redirector->gotoUrl($redirect);
            }
        }
        $this->view->html = '<h2>'.$html.'</h2>';
    }

    private function _checkConnect($type, $connectid)
    {
        $connectMod = new Community_Model_UserConnect();
        $result = $connectMod->getConnect($type, $connectid);
        return $result;
    }

    private function _setAccess($userID)
    {
        $profil = new Community_Model_Users();
        $user = $profil->getUser($userID);
        
        $cryptModel = new Community_Model_Access();
        $result = $cryptModel->setOAuthAccess($user->email, $userID);
        
        if($result === 'activated')
            $this->_helper->flashMessenger->addMessage($this->view->translate('Welcome again!<br/>Your account was activated.'));
        
        return $result;
    }

    private function _facebook($redirect)
    {
        $appID = Zend_Registry::get('config')->socsdk->fb->appId;
        $secret = Zend_Registry::get('config')->socsdk->fb->secret;
        $facebook = new N2S_SDK_FB_Facebook(array(
            'appId' => $appID,
            'secret' => $secret,
            'fileUpload' => true,
            'cookie' => true
        ));
        
        $user = $facebook->getUser();
        
        if ($user) {
            try {
              // Proceed knowing you have a logged in user who's authenticated.
              $profile = $facebook->api('/me');
            } catch (FacebookApiException $e) {
              error_log($e);
              $user = null;
            }
        }
        
        if($user && isset($profile['first_name'])
                && isset($profile['last_name'])
                && isset($profile['email'])
                && isset($profile['gender'])
                && isset($profile['birthday'])){
            // Check if connect in DB exist
            $check = $this->_checkConnect('facebook', $profile['id']);
            $auth = Zend_Auth::getInstance();
            
            if(isset($check) && !$auth->hasIdentity()){
                $this->_setAccess($check->userid);
                $userID = $check->userid;
            } elseif (isset ($check) && $auth->hasIdentity()) {
                $userID = $auth->getIdentity()->userid;
                if($userID == $check->userid){
                    $this->_helper->flashMessenger->addMessage($this->view->translate('Your account is already connected with facebook.'));
                } else {
                    $facebook->destroySession();
                    $html = '<b>'.$this->view->translate('This facebook account is already with another profile connected.').'</b>';
                    return $html;
                }
            } else {
                $connectMod = new Community_Model_UserConnect();
                if($auth->hasIdentity()){
                    $userID = $auth->getIdentity()->userid;
                    $connectMod->setConnect($profile['id'], 'facebook', $userID);
                    // if TRUE or FALSE
                } else {
                    if (Zend_Registry::isRegistered('TABLE_PREFIX')) {
                        $pref = Zend_Registry::get('TABLE_PREFIX');
                    } else {
                        $pref = '';
                    }
                    $validator = new Zend_Validate_Db_NoRecordExists($pref.'users', 'email');
                    if($validator->isValid($profile['email'])){
                        $data = array(
                            'firstname' => $profile['first_name'],
                            'lastname' => $profile['last_name'],
                            'user_email' => $profile['email'],
                            'gender' => $profile['gender'],
                            'birthdate' => new Zend_Date($profile['birthday'], 'MM/DD/YYYY')
                        );
                        $access = new Community_Model_Access();
                        $userID = $access->addUser($data, FALSE);

                        //Set UserAbout
                        $this->_setFBAbout($profile, $userID);

                        $connectMod->setConnect($profile['id'], 'facebook', $userID);
                        $this->_setAccess($userID);
                    } else {
                        $facebook->destroySession();
                        $html = '<b>'.$this->view->translate('Obtained e-mail address is already registered on our website.').'</b><br/>';
                        $html .= $this->view->translate('Connect to Facebook is possible only after manual login through your account settings.');
                        return $html;
                    }
                }
            }
            
            if(isset($userID)){
                $profil = new Community_Model_Users();
                $curuser = $profil->getUser($userID);
                if($curuser->avatar == 0)
                    $this->__imgFBUpload($user, $userID);

                if($redirect == NULL){
                    $this->_helper->redirector('profil','index','community',array('id'=>$userID));
                } else {
                    $this->_helper->redirector->gotoUrl($redirect);
                }
            }
            return;
        } else {
            $error = (string)$this->_request->getParam('error');
            
            if($error && $error == 'access_denied'){
                $html = '<b>'.$this->view->translate('We need more information for your access.').'</b>';
                $html .= '<br/><a href="'.$this->view->url(array('module'=>'default','controller'=>'oauth','action'=>'login','target'=>'facebook'),'default',TRUE).'">'.$this->view->translate('Try again.').'</a>';
                return $html;
            } else {
                //$params = array('scope' => 'user_about_me,user_birthday,email,user_hometown,user_location,user_events,user_photos,publish_actions,create_event');
                $params = array(
                    'scope' => 'user_about_me,user_birthday,email,user_hometown,user_location'
                );
                $loginUrl = $facebook->getLoginUrl($params);
                $this->_helper->redirector->gotoUrl($loginUrl);
            }
        }
    }
    
    private function __imgFBUpload($fbUser, $userID)
    {
        $avatarUrl = 'https://graph.facebook.com/'.$fbUser.'/picture?width=740';
        $handle = @fopen("$avatarUrl", "r");

        if($handle !== false)
        {
            $albums = new Default_Model_PhotoAlbums();
            $album = $albums->getComProfilImgAlbum($userID);
            if(!isset($album)){
                $albID = $albums->setComProfilImgAlbum($userID);
            } else {
                $albID = $album->id;
            }

            $basePath   = BASE_PATH.'/albums/';
            $index      = $basePath.'index.html';
            $albPath    = $basePath.$albID;
            $origPath   = $albPath.'/originalphotos/';
            $photoPath   = $albPath.'/photos/';

            if (is_dir($albPath) == FALSE){
                mkdir($albPath, 0755);
                copy ($index, $albPath.'/index.html');
            }
            if (is_dir($origPath) == FALSE){
                mkdir($origPath, 0755);
                copy ($index, $origPath.'/index.html');
            }
            if (is_dir($photoPath) == FALSE){
                mkdir($photoPath, 0755);
                copy ($index, $photoPath.'/index.html');
            }
            
            $data = file_get_contents($avatarUrl);
            
            $filenameT = md5($fbUser).'_'.time();
            $filenameF = $filenameT.'.png';
            $fileName = $origPath.$filenameF;
            
            $file = fopen($fileName, 'w+');
            fputs($file, $data);
            fclose($file);
            
            $size = getimagesize($fileName);
            
            if($size){
                $filter1 = new N2S_Filter_File_Resize(array(
                    'width' => 200,
                    'height' => 960,
                    'keepRatio' => true,
                    'directory' => $photoPath
                ));
                $filter1->filter($fileName);

                $filter2 = new N2S_Filter_File_Multicropthumb(array(
                    'thumbwidth' => 134,
                    'thumbheight' => 134,
                    'directory' => $photoPath,
                    'name'  => 'thumb_'
                ));
                $filter2->filter($fileName);

                chmod($origPath.$filenameF, 0644);
                chmod($photoPath.$filenameF, 0644);
                chmod($photoPath.'thumb_'.$filenameF, 0644);

                $baseImgPath = 'albums/'.$albID;
                $imgPath = $baseImgPath.'/photos/'.$filenameF;
                $thumbPath = $baseImgPath.'/photos/thumb_'.$filenameF;
                $origimgPath = $baseImgPath.'/originalphotos/'.$filenameF;

                list($width, $height) = $size;
                
                $photos = new Default_Model_Photos();
                $image = $photos->setPhoto($userID,$albID,$imgPath,$thumbPath,$origimgPath,$filenameT,$width,$height,1);

                $profil = new Community_Model_Users();
                $profil->updateAvatar($userID, $image);
            } else {
                unlink($fileName);
            }
        }
    }
    
    private function _setFBAbout($profile, $userID)
    {
        $about = new Community_Model_UserAbout();
        // Hometown
        if($profile['hometown']){
            $paramID = $about->getJoin('hometown');
            $arrData = array(
                'user_id'=>$userID,
                'param_id'=>$paramID,
                'permission'=>'0',
                'value'=>$profile['hometown']['name']
            );
            $check = $about->getAbout($userID, $paramID);
            if(!isset($check)){
                $about->setAbout($arrData);
            }
        }
        // curcity
        if($profile['location']){
            $paramID = $about->getJoin('curcity');
            $arrData = array(
                'user_id'=>$userID,
                'param_id'=>$paramID,
                'permission'=>'0',
                'value'=>$profile['location']['name']
            );
            $check = $about->getAbout($userID, $paramID);
            if(!isset($check)){
                $about->setAbout($arrData);
            }
        }
        // About
        if($profile['bio']){
            $paramID = $about->getJoin('about');
            $arrData = array(
                'user_id'=>$userID,
                'param_id'=>$paramID,
                'permission'=>'0',
                'value'=>$profile['bio']
            );
            $check = $about->getAbout($userID, $paramID);
            if(!isset($check)){
                $about->setAbout($arrData);
            }
        }
    }
}
