<?php

/**
 * CommentController.php
 * Description of CommentController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 31.10.2012 13:22:12
 * 
 */
class Default_CommentController extends Zend_Controller_Action
{
    public function init()
    {
        
    }
    
    public function indexAction()
    {
        if(!$this->_request->isXmlHttpRequest()) { //Only json.
            $this->_helper->redirector('notfound','Error','default');
        } else {
            $auth = Zend_Auth::getInstance();
            $cid = (int)$this->_request->getParam('id');
            $last = (int)$this->_request->getParam('last',null);
            $type = $this->_request->getParam('type');
            if($auth->hasIdentity()){
                $userID = $auth->getIdentity()->userid;
            } else {
                $userID = 0;
            }

            $comments = new Default_Model_Comments();
            if($last == null){
                $string = preg_replace("/\r|\n/s", " ", trim($this->_request->getParam('string')));
                if($string == '' || !$auth->hasIdentity())
                    die($this->_helper->json(array('error'=>true,'action'=>'stop')));
                $timestamp = Zend_Date::now()->get(Zend_Date::TIMESTAMP);
                $data = array(
                    'contentid'=>$cid,
                    'post_by'=>$userID,
                    'comment'=>$string,
                    'date'=>$timestamp,
                    'published'=>'1',
                    'type'=>$type
                );
                $ID = $comments->setComment($data);
                $html = '<li id="comCont'.$ID.'" class="comCont'.$ID.'">'.$this->view->userThumb($userID,1,1);
                $html .= '<div style="margin-left: 45px;"><div>'.$this->view->escape($string).'</div>';
                $html .= '<div style="float: left;color: #555555;font-size: 10px;"><b>'.$this->view->timeStamp($timestamp).'</b>';
                $html .= '<span onclick="javascript:comment.comdel('.$ID.')" class="comDel" id="'.$ID.'"><b> · '.$this->view->translate('delete').'</b></span>';
                $html .= '</div></div><div class="clear"></div></li>';
                $result = array('error'=>false,'html'=>$html);
            } else {
                $results = $comments->getComments($cid, $type, $last);
                if(count($results)>0){
                    $spam = Default_Model_Comments::checkAdmin($userID,$cid,$type);
                    $check = new Default_Model_Ajaxaction();
                    $html = '';
                    foreach ($results as $r){
                        $list = $check->getDelComment($r->id);
                        if(!isset($list)){
                            $html .= '<li id="comCont'.$r->id.'" class="comCont'.$r->id.'">'.$this->view->userThumb($r->post_by,1,1);
                            $html .= '<div style="margin-left: 45px;"><div>'.$this->view->escape($r->comment).'</div>';
                            $html .= '<div style="float: left;color: #555555;font-size: 10px;"><b>'.$this->view->timeStamp($r->date).'</b>';
                            if($auth->hasIdentity() && $r->post_by == $userID){
                                $html .= '<span onclick="javascript:comment.comdel('.$r->id.')" class="comDel" id="'.$r->id.'"><b> · '.$this->view->translate('delete').'</b></span>';
                            } elseif($spam == TRUE) {
                                $html .= '<span onclick="javascript:comment.comspam('.$r->id.')" class="comSpam" id="'.$r->id.'"><b> · '.$this->view->translate('spam').'</b></span>';
                            }
                            $html .= '</div></div><div class="clear"></div></li>';
                        }
                    }
                    $result = array('error'=>false,'html'=>$html);
                } else {
                    die($this->_helper->json(array('error'=>true,'action'=>'stop')));
                }
            }
            $this->_helper->json($result);
        }
    }

    public function ajaxAction()
    {
        $auth = Zend_Auth::getInstance();
        if(!$this->_request->isXmlHttpRequest() || !$auth->hasIdentity()) { //Only json.
            $this->_helper->redirector('notfound','Error','default');
        } else {
            $userID = $auth->getIdentity()->userid;
            $task = $this->_request->getParam('task');
            $activs = new Default_Model_Comments();
            $ajax = new Default_Model_Ajaxaction();
            
            if ($task == 'runaction' && $this->_request->isPost()){
                $actions = $ajax->getActions($userID, 'comment');
                
                if (count($actions) > 0) {
                    foreach ($actions as $act){
                        $activ = $activs->getEditComment($userID,$act->objectid);
                        if ($act->action == '1' && isset($activ)){
                            $activs->delComment($act->objectid);
                        }
                    }
                    $ajax->delActions($userID, 'comment');
                }
            } elseif ($task == 'comdel'){
                $activID = (int)$this->_request->getParam('comid');
                $activ = $activs->getEditComment($userID,$activID);
                if (isset($activ)){
                    $action = $ajax->setAction($userID, $activID, 'comment');
                    $html = '<div onclick="javascript:comment.comrestore('.$activID.')" class="comRestore" id="'.$activID.'">'.$this->view->translate('restore').'</div>';
                    $this->_helper->json(array('error'=>false,'html'=>$html));
                } else {
                    $this->_helper->json(array('error'=>true,'action'=>'stop','message'=>'no count'.$userID.'-'.$activID));
                }
            } elseif ($task == 'comspam'){
                $activID = (int)$this->_request->getParam('comid');
                //$activ = $activs->getEditComment($userID,$activID);
                $activ = $activs->getComment($activID);
                if (isset($activ)){
                    $type = $activ->type;
                    $id = $activ->contentid;
                    $spam = $activs->checkAdmin($userID, $id, $type);
                    
                    if($spam == TRUE){
                        $activs->delComment($activID);
                        $this->_helper->json(array('error'=>false,'html'=>''));
                    } else {
                        $this->_helper->json(array('error'=>true,'action'=>'stop','message'=>'no count'.$userID.'-'.$activID));
                    }
                } else {
                    $this->_helper->json(array('error'=>true,'action'=>'stop','message'=>'no count'.$userID.'-'.$activID));
                }                
            } elseif ($task == 'comrestore'){
                $activID = (int)$this->_request->getParam('comid');
                $activ = $activs->getEditComment($userID,$activID);
                if (isset($activ)){
                    $action = $ajax->delAction($userID, $activID, 'comment');
                    $this->_helper->json(array('error'=>false,'action'=>'restored'));
                } else {
                    $this->_helper->json(array('error'=>true,'action'=>'stop'));
                }
            } else {
                $this->_helper->json(array('error'=>true,'action'=>'stop'));
            }
        }
    }
}