<?php

/**
 * UserlistController.php
 * Description of UserlistController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 19.03.2013 13:01:20
 * 
 */
class Community_UserlistController extends Zend_Controller_Action
{
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        if (!$this->_request->isXmlHttpRequest() || !$auth->hasIdentity())
            $this->_helper->redirector('notfound', 'Error', 'default');
    }

    public function indexAction()
    {
        $this->_helper->layout()->disableLayout();
        $view = (string)$this->_request->getParam('view');
        $userID = (int)$this->_request->getParam('id',0);
        $page = $this->_request->getParam( 'page' , 1 );
        $show = (string)$this->_request->getParam('show');
        $auth = Zend_Auth::getInstance();
        
        if($userID > 0){
            $users = new Community_Model_Users();
            $user = $users->getUser($userID);
        }
        
        if($userID > 0 && isset($view) && isset($user) && $user->type == 'profil'){
            $friends = new Community_Model_FrRequest();
            switch ($view){
                case "maybefr":
                    if($auth->getIdentity()->type != 'profil' || $auth->getIdentity()->userid != $userID)
                        $this->_helper->redirector('notfound', 'Error', 'default');
                    $title = $this->view->translate('People you might know');
                    $friend = $friends->getMayBeFriends($userID);
                    break;
                case "commonfriends":
                    if($auth->getIdentity()->type != 'profil')
                        $this->_helper->redirector('notfound', 'Error', 'default');
                    $title = $this->view->translate('common friends').':<br />'.$user->firstname.' '.$this->view->translate('and you');
                    $friend = $friends->getCommonFriends($auth->getIdentity()->userid, $userID);
                    break;
                default :
                    $this->_helper->redirector('notfound', 'Error', 'default');
            }
            
            if(count($friend) > 0){
                $listUser = $users->getUsersInList($friend);
                if(count($listUser) > 0){
                    $paginator = Zend_Paginator::factory($listUser);
                    $paginator->setItemCountPerPage(5);
                    $paginator->setCurrentPageNumber($page);
                    $listHtml = '<ul>';
                    $listHtml .= $this->view->paginationControl($paginator, 'Sliding', '_partials/ajaxpagination.phtml');
                    foreach ($paginator as $u)
                    {
                        $listHtml .= '<li class="newsfeed-item"><div class="newsfeed-avatar">';
                        $listHtml .= $this->view->userThumb($u->userid,1,0);
                        $listHtml .= '</div>';
                        $listHtml .= '<div class="newsfeed-content">';
                        $listHtml .= '<div clas="newsfeed-content-top"><a class="black" href="'.$this->view->userLink($u->userid).'">'.$u->name.'</a></div>';
                        $listHtml .= $this->view->friendRequest($u->userid,'margin:5px 0;');
                        $listHtml .= $this->view->userCommonFriends($u->userid);
                        //if($new == true){
                            //$checkAd = $adReqs->getRequest($u->userid, $object);
                        /*
                            $html .= '<div class="newsfeed-meta small">';
                            $html .= '<div id="dAdRq'.$u->userid.'">';
                            $html .= '<a onclick="javascript:vens.addadminreq('.$u->userid.','.$object.',\'venue\',\'addadminreq\');" href="javascript:void(0);">';
                            $html .= '<div class="ajaxlink" style="margin-bottom: 5px; float: left; padding: 0px 20px;">';
                            $html .= $this->view->translate('Set as admin');
                            $html .= '</div></a>';
                            $html .= '</div>';
                            $html .= '</div>';
                         * 
                         */
                        //}
                        $listHtml .= '</div>';
                        $listHtml .= '</li>';                        
                    }
                    $listHtml .= '</ul>';
                } else {
                    $listHtml = $this->view->translate('There are no users.');
                }
            }

            $html = '';
            if($show !== 'ajax'){
                $html .= '<div class="n2Module"><h3>';
                $html .= $title;
                $html .= '</h3>';
                $html .= '<div id="n2s-listin-box">';
            }
            $html .= $listHtml;
            if($show !== 'ajax'){
                $html .= '</div></div>';
                $this->view->html = $html;
            } else {
                $result = array('error'=>FALSE,'html'=>$html);
                $this->_helper->json($result);
            }
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }        
    }
    
    public function commonFr($userID)
    {
        $friends = $this->commonFriends($userID);
        $profil = new Community_Model_Users();
        
        if (0 < count($friends)){
            $count = count($friends);
            shuffle($friends); // Reienfolge zufälig ändern
            
            $auth = Zend_Auth::getInstance();
            $curuser = $auth->getIdentity()->id;
            
            $html  = '<ul>';
            foreach ($friends as $f) {
                $fuser = $profil->getUser($f);
                
                $html .= '<li class="newsfeed-item">';
                $html .= '<div class="newsfeed-avatar">'.$this->view->userThumb($f,1,0).'</div>';
                $html .= '<div class="newsfeed-content">';
                $html .= '<div clas="newsfeed-content-top"><a href="'.$this->view->url(array("controller"=>"index","action"=>"profil","id"=>$f)).'">'.$fuser->name.'</a></div>';
                if ($curuser != $f){
                    $html .= '<div class="newsfeed-meta small">';
                    $html .= '<div>'.$this->friendship($f).'</div>';
                    $html .= '<a class="n2s-userlist n2lbox.ajax n2s-tooltip" title="'.$this->view->toolTip('proba',$f,0,'comFr').'" href="'.$this->view->url(array("controller"=>"userlist","action"=>"index","view"=>"commonfriends","id"=>$f)).'">';
                    if (count($this->commonFriends($f)) > 1){
                        $html .= sprintf($this->view->langHelper('%d common friends',count($this->commonFriends($f))), count($this->commonFriends($f)));
                    } else {
                        $html .= sprintf($this->view->translate('%d common friend',count($this->commonFriends($f))));
                    }
                    $html .= '</a>';
                    $html .= '</div></div></li>';
                } else {
                    $html .= '<div class="newsfeed-meta small">'.$this->view->translate('that is you');
                    $html .= '<div></div></div></div></li>';
                }
            }
            $html .= '</ul>';

            return $html;
        }
    }
}
