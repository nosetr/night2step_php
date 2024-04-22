<?php

/**
 * IndexController.php
 * Description of IndexController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 18.09.2012 19:14:46
 * 
 */
class Community_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        if ($this->_helper->FlashMessenger->hasMessages()) {
            $this->view->flashmessage = $this->_helper->FlashMessenger->getMessages();
        }
        $this->view->headTitle($this->view->translate('Community'), 'APPEND');
    }

    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
        $activation = (string)$this->_request->getParam('emailconfirm', NULL);
        $redirect = (string)$this->_request->getParam('redirect', NULL);
        
        if (!$auth->hasIdentity()) {
            $rForm = new Community_Form_UserRegistration();
            $rForm->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'registerForm'));
            
            $lForm = new Community_Form_UserLogin();
            $lForm->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'loginForm'));
            if ($activation != NULL){
                $hidden = new Zend_Form_Element_Hidden('emailconfirm'); 
                $hidden->removeDecorator('label')
                        ->setValue($activation);
                $lForm->addElement($hidden);
            }
            
            if ($this->_request->isXmlHttpRequest()) {
                $this->_helper->layout()->disableLayout();
                $view = (string)$this->_request->getParam('view', NULL);
                if ($view == 'login')
                    $lForm->setAction($this->view->url(array("module"=>"community","controller"=>"index","action"=>"index"),"default",true));
                if($view == 'login'){
                    $form = '<h2>'.$this->view->translate('Login').'</h2>'.$lForm;
                    $form .= '<div style="margin: 0px 0px 5px 40px;"><a rel="nofollow" class="n2s-login n2lbox.ajax" href="';
                    $form .= $this->view->url(array("module"=>"community",
                                "controller"=>"index",
                                "action"=>"newrequest",
                                "view"=>"password"),"default",true);
                    $form .= '">'.$this->view->translate('Forgot password?').'</a></div>';
                    $form .= '<div style="margin: 0px 0px 25px 40px;"><a rel="nofollow" class="n2s-login n2lbox.ajax" href="';
                    $form .= $this->view->url(array("module"=>"community",
                                "controller"=>"index",
                                "action"=>"newrequest",
                                "view"=>"activation"),"default",true);
                    $form .= '">'.$this->view->translate('Resend activation code?').'</a></div>';
                    $form .= '<div style="margin: 0px 0px 25px 40px;"><a rel="nofollow" class="fb-connect" href="';
                    $form .= $this->view->url(array("module"=>"default",
                                    "controller"=>"oauth",
                                    "action"=>"login",
                                    "target"=>"facebook"),"default",true);
                    $form .= '">'.$this->view->translate('Login with Facebook').'</a></div>';
                } else {
                    $form = '<h2>'.$this->view->translate('SignUp').'</h2>'.$rForm;
                }
                $this->view->html = $form;
            } else {
                $registrSuccess = FALSE;
                if($this->getRequest()->isPost()) {
                    ($_POST["submit"] == $this->view->translate('Login'))?$form3 = $lForm:$form3 = $rForm;
                    if ($form3->isValid($_POST))
                    {
                        $cryptModel = new Community_Model_Access();
                        if ($_POST["submit"] == $this->view->translate('Login')){
                            $email = $this->getRequest()->getPost('login_user');
                            $password = $this->getRequest()->getPost('login_password');
                            $rememberme = $this->getRequest()->getPost('login_rememberme');
                            $confirmEmail = $this->getRequest()->getPost('emailconfirm',NULL);
                            
                            $access = $cryptModel->setAccess($email, $password, $rememberme,$confirmEmail);
                            
                            if($access === 'wait') {
                                $form3->login_password->addError('Confirm your email at first');
                            } else {
                                if($access === 'activated')
                                    $this->_helper->flashMessenger->addMessage($this->view->translate('Welcome again!<br/>Your account was activated.'));

                                if($redirect != NULL && $access !== 'activated'){
                                    $this->_redirect($redirect);
                                } else {
                                    $this->_redirect($this->view->url(array("module"=>"community",
                                                                "controller"=>"index",
                                                                "action"=>"index"),"default",true));
                                }
                            }
                        } elseif ($_POST["submit"] == $this->view->translate('SignUp')) {
                            $data = $form3->getValues();
                            $result = $cryptModel->addUser($data);
                            
                            $subject = $this->view->translate('Complete registration');
                            $string = '<br/>'.str_replace('%name%',$result['name'],$this->view->translate('Hello %name%!')).'<br/>';
                            $string .= $this->view->translate('You are currently registered at night2step.com. To complete the registration, click on the link below and give your login details:').'<br/>';
                            $link = $this->view->serverUrl().$this->view->url(array("module"=>"community",
                                                                                "controller"=>"index",
                                                                                "action"=>"index",
                                                                                "emailconfirm"=>$result["activator"]),"default",true);
                            $string .= '<a href="'.$link.'">'.$link.'</a><br/><br/>';
                            $string .= $this->view->translate('If you believe this message is wrong, ignore it.').'<br/>';
                            
                            $this->view->eMail($result["id"],$subject,$string);
                            $registrSuccess = TRUE;
                        }
                    }
                }
                                
                if($registrSuccess == TRUE) {
                    $this->view->html = '<div style="padding: 10px; color: rgb(255, 255, 255); background-color: rgb(204, 0, 0); font-weight: bold; border-radius: 4px 4px 4px 4px;">'.$this->view->translate('To complete registration, check your email inbox.').'</div>';
                } else {
                    $terms = '<a rel="nofollow" target="_blank" href="';
                    $terms .= $this->view->url(array("module"=>"default",
                                    "controller"=>"terms",
                                    "action"=>"index",
                                    "view"=>"login"),"default",true);
                    $terms .= '">'.$this->view->translate('Terms').'</a>';
                    $policy = '<a rel="nofollow" target="_blank" href="">'.$this->view->translate('Data use policy').'</a>';
                    $cookie = '<a rel="nofollow" target="_blank" href="">'.$this->view->translate('Cookie Use').'</a>';
                    $valArray = array('%terms%','%policy%','%cookie%');
                    $changeArray = array((string)$terms,(string)$policy,(string)$cookie);
                    $formTermDesc = $this->view->translate("By clicking Sign Up, you agree to our %terms% and that you have read our %policy%, including our %cookie%.");
                    $formTermDesc = str_replace($valArray,$changeArray,$formTermDesc);
                    $rForm->captcha->setDescription($formTermDesc)->getDecorator('Description')->setEscape(false);
                    $html = '<div style="float: left; box-shadow: 0px 1px 10px rgba(0, 0, 0, 0.3); margin: 15px 5%; padding: 0px 5%; width: 30%;">';
                    $html .= '<h2>'.$this->view->translate('SignUpMain').'</h2>';
                    $html .= '<div class="hint">'.$this->view->translate('*All fields are required').'</div>'.$rForm.'</div><div style="float: right; box-shadow: 0px 1px 10px rgba(0, 0, 0, 0.3); margin: 15px 5%; padding: 0px 5%; width: 30%;">';
                    $html .= '<h2>'.$this->view->translate('Login').'</h2>'.$lForm.'<div style="margin: 0px 0px 5px 40px;"><a rel="nofollow" class="n2s-login n2lbox.ajax" href="';
                    $html .= $this->view->url(array("module"=>"community",
                                    "controller"=>"index",
                                    "action"=>"newrequest",
                                    "view"=>"password"),"default",true);
                    $html .= '">'.$this->view->translate('Forgot password?').'</a></div>';
                    $html .= '<div style="margin: 0px 0px 25px 40px;"><a rel="nofollow" class="n2s-login n2lbox.ajax" href="';
                    $html .= $this->view->url(array("module"=>"community",
                                    "controller"=>"index",
                                    "action"=>"newrequest",
                                    "view"=>"activation"),"default",true);
                    $html .= '">'.$this->view->translate('Resend activation code?').'</a></div>';
                    $html .= '<div style="margin: 0px 0px 25px 40px;"><a rel="nofollow" class="fb-connect" href="';
                    $html .= $this->view->url(array("module"=>"default",
                                    "controller"=>"oauth",
                                    "action"=>"login",
                                    "target"=>"facebook"),"default",true);
                    $html .= '">'.$this->view->translate('Login with Facebook').'</a></div></div>';
                    $this->view->html = $html;
                }
            }
            
            return $this->render('form');
        } else {
            $this->view->jQuery()->addJavascriptFile('/js/n2s.comment.js');
            $html = $this->view->action('index','activities','default');
            $this->view->html = $html;
        }
    }
    
    public function profilAction()
    {
        $showAdminPanel = FALSE;
        $this->view->jQuery()->addJavascriptFile('/js/n2s.comment.js');
        $id =  (int)$this->_request->getParam('id',0);
        $public =  $this->_request->getParam('pub',FALSE);
        $auth = Zend_Auth::getInstance();
        
        $userModel = new Community_Model_Users();
        $user = $userModel->getUser($id);
        if(!isset($user))
            $this->_helper->redirector('notfound', 'Error', 'default');
        
        if($user->deactivated == '1')
            $this->_forward('removed', 'Error', 'default');
        
        if(isset($user) && $user->type != 'profil'){
            $type = $user->type;
            switch ($type){
                case 'venue':
                    $adresses = new Default_Model_Adresses();
                    $adress = $adresses->getAdressWithCreator($id);
                    $this->_helper->redirector('show', 'venues', 'default', array('id'=>$adress->id));
                    break;
                default :
                    $this->_helper->redirector('notfound', 'Error', 'default');
            }
        }
        
        $curuser = 0;
        if($auth->hasIdentity()){
            $curUserID = $auth->getIdentity()->userid;
            /*
            if($curUserID == 62){
                $appID = Zend_Registry::get('config')->socsdk->fb->appId;
                $secret = Zend_Registry::get('config')->socsdk->fb->secret;
                $facebook = new N2S_SDK_FB_Facebook(array(
                    'appId' => $appID,
                    'secret' => $secret,
                    'fileUpload' => true,
                    'cookie' => true
                ));

                $FBuser = $facebook->getUser();
                if($FBuser){
                    $events= $facebook->api("/me/permissions");
                    var_dump($events);
                }
            }
            */
            if($curUserID == $user->userid){
                $showAdminPanel = TRUE;
                if($public == FALSE){
                    $curuser = $curUserID;
                }
            }
        }
        
        ($curuser == $user->userid)?$owner = TRUE:$owner = FALSE;
        
        $this->view->mTitle = '<h1 style="width:740px;margin:0px;min-height:45px;line-height:40px;">'.$user->name.'</h1>';
        
        $backGrBanner = $this->view->userBanner('profil',$id,$owner);
        $this->view->userbanner = $backGrBanner['html'];
        ($backGrBanner['map'] == TRUE)?$gridTop = '':$gridTop = ' style="top:-220px;z-index:2;"';
        $this->view->gridtop = $gridTop;
        
        $permis = Community_Model_Permissions::getPermissions($user->userid,$curuser);
        
        $panel = $this->view->showPanel($id,'profil',$owner);
        $bgPos = $panel['width']+30;
        
        $html = '<div style="background: url(/images/bg-line2.png) repeat-y scroll '.$bgPos.'px 0px rgb(250, 250, 250);">';
        $html .= '<div class="profInfoAbout" style="width:'.$panel['width'].'px;">';
        if($owner == TRUE){
            $html .= '<a href="';
            $html .= $this->view->url(array('module'=>'community','controller'=>'index','action'=>'profiledit','task'=>'about'),'default', true);
            $html .= '" title="edit" class="alb_edit"></a>';
        }
        $html .= '<ul style="margin:0px;">';
        $aModel = new Community_Model_UserAbout();
        $rA = $aModel->getAllAbout($user->userid,$permis);
        foreach ($rA as $r){
            if($r->value != null){
                if($r->name == 'birthdate'){
                    $val = new Zend_Date($r->value);
                    $value = $val->get(Zend_Date::DATE_LONG);
                } else {
                    $value = $r->value;
                }
                
                $html .= '<li><span class="n2s-tooltip infospan INFO_'.$r->name.'" title="'.$this->view->translate('INFO_'.$r->name).'">';
                $html .= $this->view->shortText($this->view->escape($value),100,TRUE,FALSE);
                $html .= '</span></li>';
            } else {
                $aModel->delAbout($user->userid, $r->param_id);
            }
        }
        
        if($auth->hasIdentity() && N2S_User::curuser() != $id){
            $html .= '<li style="margin-top: 5px;">';
            $html .= $this->view->messager($id,'float:left;');
            $html .= '<div class="clear"></div></li>';
        }
        
        if($auth->hasIdentity() && N2S_User::curuser() != $id){
            $html .= '<li style="margin-top: 5px;">';
            $html .= $this->view->friendRequest($id,'float:left;padding: 2px 10px;',TRUE);
            $html .= '<div class="clear"></div></li>';
        }
        
        $html .= '</ul></div>';
        $html .= $panel['html'];
        $html .= '<div class="clear"></div></div>';
        $html .= '<div class="clear"></div>';
        
        $html .= $this->view->action('index','activities','default',array('task'=>'profil','cid'=>$user->userid));
        
        $html .= '<input type="hidden" name="active-show" value="profil"/>';
        $html .= '<input type="hidden" name="active-id" value="'.$id.'"/>';
        $this->view->html = $html;
        
        $this->view->headTitle($user->name, 'PREPEND');
        
        $photos = new Default_Model_Photos();
        $photo = $photos->getPhotoID($user->avatar);
        if ($photo && file_exists($photo->image)){
            $evimg = $photo->image;
            $thumbexist = 1;
            $permis = Community_Model_Permissions::getPermissions($photo->creator);
        } else {
            switch ($user->gender){
                case "m":
                    $gender = "male";
                    break;
                case "f":
                    $gender = "female";
                    break;
                default:
                    $gender = "default";
            }
            $evimg = $this->view->baseUrl().'images/avatar/default/'.$gender.'.jpg';
            $thumbexist = 0;
        }
        
        if($thumbexist > 0 && $photo->permissions > $permis)
            $thumbexist = 0;
        
        $thumb = '<div id="n2s-useravatar" style="z-index:2;box-shadow:-1px 2px 3px rgba(0, 0, 0, 0.3);border: 1px solid rgb(153, 153, 153); width: 238px; margin-bottom: 10px; min-height: 225px; background-color: rgb(255, 255, 255);">';
        $thumb .= '<div id="avSn2s">';
        if($thumbexist > 0){
            $thumb .= '<a class="n2s-phBox n2lbox.ajax" href="';
            $thumb .= $this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$user->avatar),'default', true);
            $thumb .= '">';
        }
        $thumb .= '<img id="n2simg-avat_surround" ';
        $thumb .= 'src="'.$evimg.'" alt=""/>';
        if($thumbexist > 0)
            $thumb .= '</a>';
        $thumb .= '</div>';
        if($auth->hasIdentity() && $curuser == $user->userid){
            $thumb .= '<a class="n2s-imgup n2lbox.ajax n2s-transpb" href="';
            $thumb .= $this->view->url(array(
                'module'=>'default','controller'=>'ajax','action'=>'imgup',
                'task'=>'avatarchange','target'=>'avatar'),'default', true);
            $thumb .= '" class="" style="text-align: center; padding: 10px 0px; width: 240px; top: 1px;">'.$this->view->translate('Change avatar').'</a>';
        }
        $thumb .= '</div>';
        $this->view->thumb = $thumb;
        
        $pubStand = '';
        $pubview = '';
        if($showAdminPanel == TRUE){
            if($public == TRUE && $this->_request->isXmlHttpRequest()){
                $pubview = '<div onclick="javascript:n2s.publicview();" class="viewNotButton"><h3 style="text-align:center;">';
                $pubview .= $this->view->translate('Close public view').'</h3>';
                $pubview .= '</div>';
                $pubStand = 'var pubview = 0;';
            } else {
                $pubview = '<a href="javascript:void(0);" onclick="javascript:n2s.publicview();"';
                $pubview .= ' class="ajaxlink adpan-top left">';
                $pubview .= $this->view->translate('public view');
                $pubview .= '</a>';
                $pubview .= '<a href="';
                $pubview .= $this->view->url(array('module'=>'community','controller'=>'index','action'=>'profiledit'),'default', true);
                $pubview .= '" class="ajaxlink adpan-top left">';
                $pubview .= $this->view->translate('Account settings');
                $pubview .= '</a>';
                $pubStand = 'var pubview = 1;';
            }
        } else {
            if($auth->hasIdentity() && $auth->getIdentity()->type != 'profil'){
                $pubview .= $this->view->ifAdmin($user->userid,$user->type);
            }
        }
        $this->view->public = $pubStand;
        $this->view->pubview = $pubview;
        
        if($this->_request->isXmlHttpRequest())
            $this->_helper->layout()->disableLayout();
    }
    
    public function ajaxAction()
    {
        if(!$this->_request->isXmlHttpRequest())
            $this->_helper->redirector('notfound', 'Error', 'default');
        
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity() || $auth->getIdentity()->type != 'profil'){
            $result = array('error'=>TRUE,'message'=>$this->view->translate('Error'));
        } else {
            $users = new Community_Model_Users();
            $user = $users->getUser($auth->getIdentity()->userid);
            if(!isset($user)){
                $result = array('error'=>TRUE,'message'=>$this->view->translate('Error'));
            } else {
                $task = (string)$this->_request->getParam('task',NULL);
                switch ($task){
                    case 'about':
                        $birthDate = '0';
                        $aModel = new Community_Model_UserAbout();
                        $rA = $aModel->getAllAbout($user->userid,50);
                        foreach ($rA as $r){
                            if($r->value != NULL){
                                if($r->name == 'birthdate'){
                                    $birthDate = $r->value;
                                }
                            }
                        }
                        $formSet = array('birthDate'=>$birthDate,'type'=>$auth->getIdentity()->type);
                        $form = new Community_Form_SettingAbout($formSet);
                        if($this->_request->isPost() && !$form->isValid($_POST)){
                            $evVals = $form->getValues();
                        } else {
                            $evVals = array();
                            foreach ($rA as $r){
                                if($r->name == 'birthdate'){
                                    $val = new Zend_Date((int)$r->value);
                                    $value = $val->get(Zend_Date::DATE_LONG);
                                } else {
                                    $value = $r->value;
                                }
                                $evVals[$r->name] = $value;
                                $evVals['permis_'.$r->name] = $r->permission;
                            }
                        }
                        $form->setDefaults($evVals);
                        $js = '<script charset="utf-8" type="text/javascript">';
                        $js .= '$("textarea").autogrow();';
                        if($birthDate == '0'){
                            $locale = Zend_Registry::get('Zend_Locale');
                            $dateFormat = Zend_Locale_Data::getContent($locale, 'date', 'short');
                            if (strpos($dateFormat, 'MM') !== FALSE)
                                $dateFormat = str_ireplace('MM', 'mm', $dateFormat);
                            if (strpos($dateFormat, 'M') !== FALSE)
                                $dateFormat = str_ireplace('M', 'mm', $dateFormat);
                            
                            $js .= '$( "#birthdate" ).datepicker({';
                            $js .= '"defaultDate": "-17y",';
                            $js .= '"changeYear":true,';
                            $js .= '"minDate": "-1200m",';
                            $js .= '"maxDate":"-192m",';
                            $js .= '"changeMonth":true,';
                            $js .= '"firstDay":"1",';
                            $js .= '"yearRange": "c-84:c",';
                            if (Zend_Registry::get('Zend_Locale') != 'en'){
                                $mNS = explode( ',', $this->view->translate('monthNamesShort'));
                                $mNSArr = '[';
                                foreach ($mNS as $m){
                                    $mNSArr .= '"'.$m.'",';
                                }
                                $mNSArr .= ']';
                                $dNM = explode( ',', $this->view->translate('dayNamesMin'));
                                $dNMArr = '[';
                                foreach ($dNM as $m){
                                    $dNMArr .= '"'.$m.'",';
                                }
                                $dNMArr .= ']';
                                $dN = explode( ',', $this->view->translate('dayNames'));
                                $dNArr = '[';
                                foreach ($dN as $m){
                                    $dNArr .= '"'.$m.'",';
                                }
                                $dNArr .= ']';
                                $js .= '"monthNamesShort":'.$mNSArr.',';
                                $js .= '"dayNamesMin":'.$dNMArr.',';
                                $js .= '"dayNames":'.$dNArr.',';
                                $js .= '"nextText":"'.$this->view->translate('nextText').'",';
                                $js .= '"prevText":"'.$this->view->translate('prevText').'",';
                            }
                            $js .= '"dateFormat":"'.$dateFormat.'"';
                            $js .= '});';
                        }
                        $js .= '</script>';
                        
                        $html = $form.$js;
                        break;
                    case 'deactive':
                        $form = new Community_Form_SettingUserDeactive();
                        $html = '';
                        break;
                    case 'name':
                        $form = new Community_Form_SettingName();
                        $evVals = array(
                            'firstname'=>$user->firstname,'lastname'=>$user->lastname
                        );
                        $form->setDefaults($evVals);
                        $html = $form;
                        break;
                    case 'email':
                        $form = new Community_Form_SettingEMail();
                        $evVals = array(
                            'user_email'=>$user->email
                        );
                        $form->setDefaults($evVals);
                        $html = $form;
                        break;
                    case 'password':
                        $form = new Community_Form_SettingPass();
                        $html = $form;
                        break;
                    case 'social':
                        $connectMod = new Community_Model_UserConnect();
                        $checkFB = $connectMod->getConnect('facebook', NULL, $user->userid);
                        if(isset($checkFB)){
                            $html = '<div class="scAr"><img src="/images/fb.png">';
                            $html .= $this->view->translate('Connected with facebook');
                            $html .= '<a class="cntSozRemove n2s-tooltip" title="'.$this->view->translate('Remove').'" rel="nofollow" href="'.$this->view->url(array("module"=>"default",
                                        "controller"=>"oauth",
                                        "action"=>"logout",
                                        "target"=>"facebook"),"default",true).'?rdrct='.$this->view->baseUrl($this->view->url(
                                                array("module"=>"community",
                                        "controller"=>"index",
                                        "action"=>"profiledit",
                                        "task"=>"social"),"default",true
                                                )).'">'.$this->view->translate('Remove').'</a></div>';
                        } else {
                            $html = '<a rel="nofollow" class="fb-connect" href="';
                            $html .= $this->view->url(array("module"=>"default",
                                        "controller"=>"oauth",
                                        "action"=>"login",
                                        "target"=>"facebook"),"default",true).'?rdrct='.$this->view->baseUrl($this->view->url(
                                                array("module"=>"community",
                                        "controller"=>"index",
                                        "action"=>"profiledit",
                                        "task"=>"social"),"default",true
                                                ));
                            $html .= '">'.$this->view->translate('Connect with Facebook').'</a>';
                        }
                        /*
                        $html .= '<div style="margin: 0px 0px 25px 40px;"><a rel="nofollow" class="fb-connect" href="';
                        $html .= $this->view->url(array("module"=>"default",
                                    "controller"=>"oauth",
                                    "action"=>"login",
                                    "target"=>"facebook"),"default",true);
                        $html .= '">'.$this->view->translate('Login with Facebook').'</a></div>';
                         * 
                         */
                        break;
                    default :
                        die($this->_helper->json(array('error'=>TRUE,'message'=>$this->view->translate('Error'))));
                }
                if($this->_request->isPost() && $form->isValid($_POST)){
                    $data = $form->getValues();
                    switch ($task){
                        case 'about':
                            $aModel->checkAbout($user->userid, $data);
                            $rA = $aModel->getAllAbout($user->userid,50);
                            $html = '';
                            foreach ($rA as $r){
                                if($r->value != NULL){
                                    if($r->name == 'birthdate'){
                                        $val = new Zend_Date((int)$r->value);
                                        $value = $val->get(Zend_Date::DATE_LONG);
                                    } else {
                                        $value = $r->value;
                                    }
                                    $html .= '<b>'.  $this->view->translate('INFO_'.$r->name).':</b> ';
                                    $html .= $this->view->shortText($this->view->escape($value),80,TRUE,FALSE);
                                    $html .= '<br/>';
                                } else {
                                    $aModel->delAbout($user->userid, $r->param_id);
                                }
                            }
                            break;
                        case 'deactive':
                            @set_time_limit(10 * 60);
                            $ajaxList = new Default_Model_Ajaxlist();
                            $ajaxList->setDeactive($user->userid);
                            $comModel = new Default_Model_Comments();
                            $comModel->setDeactive($user->userid);
                            $arraydata = array('deactivated' => '1');
                            $users->updateProfil($user->userid, $arraydata);
                            $html = '';
                            break;
                        case 'name':
                            $new = trim($data['firstname']).' '.trim($data['lastname']);
                            $arraydata = array(
                                'name' => $new,
                                'firstname' => trim($data['firstname']),
                                'lastname' => trim($data['lastname']));
                            $users->updateProfil($user->userid, $arraydata);
                            $html = $new;
                            break;
                        case 'email':
                            $new = trim($data['user_email']);
                            $model = new Community_Model_UserEmailConfirm();
                            $code = $model->setConfirm($user->userid, $new);
                            $html = $user->email;
                            if($code != FALSE){
                                $subject = $this->view->translate('Confirm email');
                                $string = '<br/>'.str_replace('%name%',$user->name,$this->view->translate('Hello %name%!')).'<br/>';
                                $string .= $this->view->translate('You are currently changed your email-address at night2step.com. To complete, click on the link below:').'<br/>';
                                $link = $this->view->serverUrl().$this->view->url(array("module"=>"community",
                                                                                    "controller"=>"index",
                                                                                    "action"=>"index",
                                                                                    "newset_confirm"=>$code.'_'.$user->userid,
                                                                                    "conftask"=>"email"),"default",true);
                                $string .= '<a href="'.$link.'">'.$link.'</a><br/><br/>';
                                $string .= $this->view->translate('If you believe this message is wrong, ignore it.').'<br/>';
                                $this->view->eMail($user->userid,$subject,$string,FALSE,$new);

                                $html .= '<br/><span style="font-size: 9px;">';
                                $html .= sprintf($this->view->translate('The new email was saved. Please check our post on %s to confirm.'), '<b>'.$new.'</b>');
                                $html .= '</span>';
                            }
                            break;
                        case 'password':
                            $model = new Community_Model_Access();
                            $model->updatePass($user->userid, $data['user_password']);
                            
                            $html = '<b style="font-weight: bold; font-size: 9px;">';
                            $html .= $this->view->translate('The new password was saved.');
                            $html .= '</b>';
                            break;
                    }
                    $result = array('error'=>FALSE,'success'=>TRUE,'html'=>$html);
                } else {
                    $html = '<span id="albformarray">'.$html.'</span>';
                    $result = array('error'=>FALSE,'success'=>FALSE,'html'=>$html);
                }
            }
        }
        $this->_helper->json($result);
    }

    public function profileditAction()
    {
        $auth = Zend_Auth::getInstance();
        if(!$auth->hasIdentity() || $auth->getIdentity()->type != 'profil')
            $this->_helper->redirector('notfound', 'Error', 'default');
        
        $this->view->headLink()->appendStylesheet('/css/setting.css');
        $users = new Community_Model_Users();
        $user = $users->getUser($auth->getIdentity()->userid);
        if(!isset($user))
            $this->_helper->redirector('notfound', 'Error', 'default');
        
        $this->view->jQuery()->addJavascriptFile('/js/n2s.setting.js');
        $this->view->jQuery()->addJavascriptFile('/js/n2s.venues.js');
        
        $task = (string)$this->_request->getParam('task');
        
        $this->view->headTitle($this->view->translate('Account settings'), 'PREPEND');
        
        $html = '<h1>'.$this->view->translate('Account settings').'</h1>';
        $html .= '<div class="formarround">';
        $html .= '<ul id="farul">';
        $html .= '<li class="setLi">';
        $html .= '<span class="left"><strong>'.$this->view->translate('Name').'</strong></span>';
        $html .= '<span class="right"><a class="edSett" id="name" href="javascript:void(0);" onclick="javascript:sett.getForm(this);">'.$this->view->translate('Edit').'</a></span>';
        $html .= '<div id="set_name" class="setItemContent"><span class="curCont">'.$user->name.'</span></div>';
        $html .= '<div class="clear"></div></li>';
        $html .= '<li class="setLi">';
        $html .= '<span class="left"><strong>'.$this->view->translate('Email').'</strong></span>';
        $html .= '<span class="right"><a class="edSett" id="email" href="javascript:void(0);" onclick="javascript:sett.getForm(this);">'.$this->view->translate('Edit').'</a></span>';
        $html .= '<div id="set_email" class="setItemContent"><span class="curCont">'.$user->email;
        $emailmodel = new Community_Model_UserEmailConfirm();
        $newemail = $emailmodel->getConfirm($user->userid);
        if(isset($newemail)){
            $html .= '<br/><span style="font-size: 9px;">';
            $html .= sprintf($this->view->translate('The new email was saved. Please check our post on %s to confirm.'), '<b>'.$newemail->email.'</b>');
            $html .= '</span>';
        }
        $html .= '</span></div>';
        $html .= '<div class="clear"></div></li>';
        $html .= '<li class="setLi">';
        $html .= '<span class="left"><strong>'.$this->view->translate('Password').'</strong></span>';
        $html .= '<span class="right"><a class="edSett" id="password" href="javascript:void(0);" onclick="javascript:sett.getForm(this);">'.$this->view->translate('Edit').'</a></span>';
        $html .= '<div id="set_password" class="setItemContent"><span class="curCont"></span></div>';
        $html .= '<div class="clear"></div></li>';
        $html .= '<li class="setLi">';
        $html .= '<span class="left"><strong>'.$this->view->translate('About').'</strong></span>';
        $html .= '<span class="right"><a class="edSett" id="about" href="javascript:void(0);" onclick="javascript:sett.getForm(this);">'.$this->view->translate('Edit').'</a></span>';
        $html .= '<div id="set_about" class="setItemContent"><span class="curCont">';
        
        $aModel = new Community_Model_UserAbout();
        $rA = $aModel->getAllAbout($user->userid,50);
        foreach ($rA as $r){
            if($r->value != null){
                if($r->name == 'birthdate'){
                    $val = new Zend_Date($r->value);
                    $value = $val->get(Zend_Date::DATE_LONG);
                } else {
                    $value = $r->value;
                }
                $html .= '<b>'.  $this->view->translate('INFO_'.$r->name).':</b> ';
                $html .= $this->view->shortText($this->view->escape($value),80,TRUE,FALSE);
                $html .= '<br/>';
            } else {
                $aModel->delAbout($user->userid, $r->param_id);
            }
        }
        
        $html .= '</span></div>';
        $html .= '<div class="clear"></div></li>';
        
        $html .= '<li class="setLi">';
        $html .= '<span class="left"><strong>'.$this->view->translate('Social network').'</strong></span>';
        $html .= '<span class="right"><a class="edSett" id="social" href="javascript:void(0);" onclick="javascript:sett.getForm(this);">'.$this->view->translate('Edit').'</a></span>';
        $html .= '<div id="set_social" class="setItemContent"><span class="curCont"></span></div>';
        $html .= '<div class="clear"></div></li>';
        
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '<div id="dact" style="margin:0 0 10px 50px;float:left;">';
        $html .= '<a id="dactlink" style="font-weight:normal;" onclick="javascript:sett.deactive();" href="javascript:void(0);">';
        $html .= $this->view->translate('Deactivate your account.');
        $html .= '</a></div>';
        
        if(isset($task)){
            $html .= '<script type="text/javascript">';
            $html .= '$( document ).ready(function(){var nmfld=$("#farul").find($("#'.$task.'"));';
            $html .= 'if(nmfld.length>0){';
            $html .= 'sett.getForm(nmfld);}});';
            $html .= '</script>';
        }
        
        $this->view->html = $html;
    }
    
    public function deactiveAction()
    {
        $auth = Zend_Auth::getInstance();
        if($this->_request->isXmlHttpRequest()){
            if($auth->hasIdentity() && $auth->getIdentity()->type == 'profil'){
                $curuser = $auth->getIdentity()->userid;
                $friends = new Community_Model_FrRequest();
                $friend = $friends->getFriendsListArray($curuser);
                $form = new Community_Form_SettingUserDeactive();
                $html = '<ul id="fardeac">';
                $html .= '</li>';
                $html .= '<h2>'.$this->view->translate('Are you sure you want to deactivate your account?').'</h2>';
                $html .= '</li>';
                $html .= '<li>';
                $html .= $this->view->translate('Deactivating your account will disable your Profile and remove your name and picture from most things you\'ve shared on night2step. Some information may still be visible to others, such as your name in their friends list and messages you sent.');
                $html .= '</li>';
                if(count($friend) > 0){
                    $html .= '<li><b>';
                    $html .= sprintf($this->view->translate('Your %d friends will no longer be able to keep in touch with you.'),count($friend));
                    $html .= '</b></li>';
                    shuffle($friend);
                    $friend = array_slice($friend, 0, 10);
                    $html .= '<li>';
                    foreach ($friend as $f){
                        $html .= '<span class="n2sth">'.$this->view->userThumb($f['connect_from']).'</span>';
                    }
                    $html .= '<div class="clear"></div></li>';
                }
                $admins = new Community_Model_Admins();
                $all = $admins->findSoleAdmin($curuser);
                if(count($all) > 0){
                    $html .= '<li><b>';
                    $html .= $this->view->translate('You are the sole admin of the following page. You must add an additional admin to keep the page active and accessible.');
                    $html .= '</li>';
                    $venues = new Default_Model_Adresses();
                    foreach ($all as $a){
                        $html .= '<li>';
                        $html .= '<div style="float:left;">'.$this->view->userThumb($a,1,0).'</div>';
                        $venue = $venues->getAdressWithCreator($a);
                        $link = $this->view->url(array("module"=>"default",
                                                        "controller"=>"venues",
                                                        "action"=>"adminlist",
                                                        "task"=>'add',
                                                        "id"=>$venue->id),"default",true);
                        $html .= '<div style="margin-left: 70px;">';
                        $html .= '<div style="margin-bottom: 5px; font-weight: bold;">'.$venue->name.'</div>';
                        $html .= '<a class="ajaxlink n2s-userlist n2lbox.ajax left" style="padding:0 15px;" href="'.$link.'">';
                        $html .= $this->view->translate('Add administrators').'</a>';
                        $html .= '</div><div class="clear"></div></li>';
                    }
                }
                $html .= '<li id="albformarray">'.$form.'</li></ul>';
                $result = array('error'=>FALSE,'html'=>$html);
            } else {
                $result = array('error'=>TRUE,'message'=>$this->view->translate('Error'));
            }
            $this->_helper->json($result);
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }
    }

    public function changeaccountAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity() && $this->_request->isXmlHttpRequest()) {
            $changeprofil =  (int)$this->_request->getParam('changeprofil',0);
            $resetprofil =  (int)$this->_request->getParam('resetprofil',0);
            if($changeprofil > 0 || $resetprofil > 0){
                $userID = $auth->getIdentity()->userid;
                $link = $this->view->userLink($userID);              
                $result = array('changed'=>TRUE,'link'=>$link);
            } else {
                $result = array('changed'=>FALSE);
            }
            $this->_helper->json($result);
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }
    }

    public function newrequestAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->layout()->disableLayout();
            $view = (string)$this->_request->getParam('view', NULL);
            if ($view == NULL){
                $this->_forward('notfound', 'error', 'default');
            } else {
                $registrSuccess = FALSE;
                $form = new Community_Form_UserNewRequest();
                $form->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'requestForm'))
                        ->setAction($this->view->url());
                if ($this->getRequest()->isPost() && $form->isValid($_POST))
                {
                    $email = $this->getRequest()->getPost('login_user');
                    $cryptModel = new Community_Model_Access();
                    switch ($view) {
                        case "password":
                            $new = "pass";
                            break;
                        case "activation":
                            $new = "activ";
                            break;
                    }
                    
                    if ($new == "pass" || $new == "activ") {
                        $result = $cryptModel->setRequestAccess($email, $new);
                        
                        $string = '<br/>'.str_replace('%name%',$result['name'],$this->view->translate('Hello %name%!')).'<br/>';
                        $html = '<div id="n2lcontResult">';
                        if ($new == "pass") {
                            $html .= '<b>'.$this->view->translate('New password has been sent to your email address.').'</b>';
                            $subject = $this->view->translate('Restore password');
                            $string .= $this->view->translate('Due to your request, we have changed your password. Your new password is:').'<br/><br/>';
                            $string .= '<b>'.$result["new"].'</b><br/><br/>';
                            $string .= $this->view->translate('Once you\'re logged in to our site, change it for security in your private settings.').'<br/><br/>';
                        } elseif ($new == "activ") {
                            if ($result["error"] == TRUE){
                                $html .= '<b>'.$this->view->translate('Your profile has been already activated.').'</b>';
                            } else {
                                $html .= '<b>'.$this->view->translate('New activation code was sent to your email address.').'</b>';
                                $subject = $this->view->translate('Resend activation code');
                                $link = $this->view->serverUrl().$this->view->url(array("module"=>"community",
                                                                                "controller"=>"index",
                                                                                "action"=>"index",
                                                                                "emailconfirm"=>$result["new"]),"default",true);

                                $string .= $this->view->translate('Because of your request we send the activation code again. To complete the registration, click on the link below and give your login details:').'<br/>';
                                $string .= '<a href="'.$link.'">'.$link.'</a><br/><br/>';
                                $string .= $this->view->translate('The earlier activation code is not more valid.').'<br/><br/>';
                            }
                        }
                        if ($result["error"] == FALSE){
                            $string .= $this->view->translate('If you believe this message is wrong, ignore it.').'<br/>';
                            $this->view->eMail($result["id"],$subject,$string);
                        }
                        $html .= '</div>';
                        
                        $registrSuccess = TRUE;
                    }
                }
                if ($registrSuccess == FALSE){
                    $html = '<div id="n2lcontResult">';
                    if ($view == 'password'){
                        $html .= '<h2>'.$this->view->translate('Restore password').'</h2>'.$form;
                    } elseif ($view == 'activation') {
                        $html .= '<h2>'.$this->view->translate('Resend activation code').'</h2>'.$form;
                    } else {
                        $this->_forward('notfound', 'error', 'default');
                    }
                    $html .= '</div>';
                }
                
                $this->view->html = $html;
            }
        } else {
            $this->_forward('notfound', 'error', 'default');
        }
    }
    
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::forgetMe();
        
        $defaultSess = Zend_Registry::get('config')->authsession->default->key;
        $changedSess = Zend_Registry::get('config')->authsession->changed->key;
        Zend_Session::namespaceUnset($defaultSess);
        Zend_Session::namespaceUnset($changedSess);
        
        $this->_helper->flashMessenger->addMessage($this->view->translate('You are logged out of your account'));

        $this->view->logout = true;
        if($this->_request->isXmlHttpRequest()){
            $this->_helper->json(array('logout'=>TRUE));
        } else {
            return $this->_helper->redirector('index','index');
        }
    }
}