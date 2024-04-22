<?php

/**
 * PhotoController.php
 * Description of PhotoController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 05.12.2012 16:19:13
 * 
 */
class Default_PhotoController extends Zend_Controller_Action
{
    public function init()
    {
        /*$this->view->headLink()->appendStylesheet($this->view->baseUrl().'css/photo.css');
        //setzt er den Suffix auf 'ajax.phtml'
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('view', 'html')
                    ->initContext();
         * 
         */
        $this->view->headMeta()->setName('robots','noimageindex');
    }
    
    public function viewAction()
    {
        if($this->_request->isXmlHttpRequest()) {
            $this->_helper->layout()->disableLayout();
        }
        $id = (int)$this->_request->getParam('id');
        $task = $this->_request->getParam('task');
        $show = (string)$this->_request->getParam('show');
        $activ = (int)$this->_request->getParam('act');
        $fullscreen = (string)$this->_request->getParam('full','false');
        $photos = new Default_Model_Photos();
        $photo = $photos->getPhotoID($id);
        $permis = Community_Model_Permissions::getPermissions($photo->creator);
        if($photo->permissions > $permis)
            $this->_helper->redirector('noaccess','Error','default');
        $phList = null;
        switch ($show){
            case 'user':
                $album = null;
                break;
            case 'album':
                $album = null;
                if(isset($activ)){
                    $activs = new Community_Model_Activities();
                    $act = $activs->getActivID($activ);
                    if(isset($act)){
                        $params = new N2S_Params();
                        $param = $params->get($act->params);
                        if(isset($param['photoid']) && isset($param['count']) && $param['count'] > 1){
                            $phList = explode(',',$param['photoid']);
                        }
                    }
                }
                $album = $photo->albumid;
                break;
            default :
                $album = null;
        }
        $json = FALSE;
        switch ($task){
            case 'next':
                $photo = $photos->getNextPhoto($id,$photo->creator,$album,$permis,$phList);
                if($this->_request->isXmlHttpRequest())
                        $json = TRUE;
                break;
            case 'prew':
                $photo = $photos->getPrewPhoto($id,$photo->creator,$album,$permis,$phList);
                if($this->_request->isXmlHttpRequest())
                        $json = TRUE;
                break;
            default:
                $json = FALSE;
                break;
        }
        if(isset($photo) && isset($photo->original) && file_exists($photo->original)){
            $image = $photo->original;
        } elseif(isset($photo) && isset($photo->image) && file_exists($photo->image)){
            $image = $photo->image;
        } else {
            $this->_helper->redirector('notfound','Error','default');
        }
        $profil = new Community_Model_Users();
        $user = $profil->getUser($photo->creator);
        $albums = new Default_Model_PhotoAlbums();
        $albumInfo = $albums->getComAlbumInfo($photo->albumid);
        
        if($album != null && $albumInfo->photocount > 1 && $this->_request->isXmlHttpRequest()){
            $rtL = array("id"=>$photo->id);
            if(isset($activ))
                $rtL['act'] = $activ;
            $nRtL = $pRtL = $rtL;
            $pRtL['task'] = 'prew';
            $nRtL['task'] = 'next';
            $navHtml = '<a class="n2lBoxPhNav" id="n2lBoxPhNav-prev" href="'.$this->view->url($pRtL).'" rel="nav">';
            $navHtml .= '<div class="phArrow" id="phNaviBack" style="height: 100%; position: relative; right: 0px; float: left; width: 20%;"><img style="width: 30%;" src="images/arrow_left.png" alt=""/></div>';
            $navHtml .= '</a><a class="n2lBoxPhNav" id="n2lBoxPhNav-next" href="'.$this->view->url($nRtL).'" rel="nav">';
            $navHtml .= '<div class="phArrow" id="phNaviNext" style="height: 100%; position: relative; right: 0px; float: right; width: 20%;"><img style="width: 30%;" src="images/arrow_right.png" alt=""/></div>';
            $navHtml .= '</a>';
        } else {
            $navHtml = '';
        }
        
        $imgHtml = '<img src="'.$image.'" alt=""/>';
        $userHtml = '<div id="phBoxUser" style="margin: 20px; border-bottom: 1px solid rgb(153, 153, 153); min-height: 70px;">'.$this->view->userThumb($photo->creator,1,0);
        $userHtml .= '<div style="margin-left: 60px;"><a href="'.$this->view->userLink($photo->creator).'">'.$user->name.'</a>';
        $userHtml .= '<div>'.$this->view->timeStamp($photo->created).'</div>';
        $userHtml .= '<div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%;"><a class="bllink" href="'.$this->view->url(array("module"=>"default","controller"=>"photos","action"=>"useralbums","view"=>$photo->albumid,"id"=>$photo->creator),'default', true).'">';
        if($albumInfo->type == 'eventimg' || $albumInfo->type == 'profimg'){
            $userHtml .= $this->view->translate($albumInfo->name);
        } else {
            $userHtml .= $albumInfo->name;
        }
        $userHtml .= '</a></div>';
        if($albumInfo->locid > 0)
            $userHtml .= '<div style="padding-left: 15px;background: url(/images/home.png) no-repeat scroll 0 2px transparent;overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%;"><a class="bllink" href="'.$this->view->url(array("module"=>"default","controller"=>"venues","action"=>"show","id"=>$albumInfo->locid),'default', true).'">'.$albumInfo->location.'</a></div>';
        $userHtml .= '</div></div>';
        $comm = '<div style="margin:0 20px 20px;">'.$this->view->comments($photo->id,'photos',3,FALSE).'</div>';
        
        $html = '<div id="n2l-boxPH" style="';
        if($this->_request->isXmlHttpRequest()) {
            $html .= 'width: 100%; height: 100%;';
        } else {
            $this->view->jQuery()->addJavascriptFile('/js/n2s.comment.js');
            $pageN = $this->view->navigation()->findOneBy('label', 'Photos');
            if($pageN)$pageN->setActive(TRUE);
            $html .= 'width:980px;';
        }
        $html .= '">';
        $html .= '<div class="phBoxInfo"';
        if(!$this->_request->isXmlHttpRequest() && $fullscreen == 'false')
            $html .= ' style="overflow: visible; height: auto;"';
        if($fullscreen == 'true')
            $html .= ' style="display:none;"';
        $html .= '>'.$userHtml.$comm;
        //$html .= '<div style="margin-bottom:10px;text-align:center;width:100%;">'.$this->view->reklame().'</div>';
        $html .= '</div>';
        $html .= '<div class="phBoxCol"';
        if($fullscreen == 'true')
            $html .= ' style="width:100%;"';
        $html .= '><span';
        if($fullscreen == 'true')
            $html .= ' id="fullactiv"';
        $html .= ' class="n2s-fscreen" title="';
        if($fullscreen == 'true'){
            $html .= $this->view->translate('Press Esc to exit fullscreen');            
        } else {
            $html .= $this->view->translate('Enter Fullscreen');
        }
        $html .= '" onclick="parent.n2s.full();"></span>';
        $html .= $imgHtml.$navHtml.'<div class="clear"></div></div>';
        $html .= '</div>';
        $html .= '<div id="n2lbox-loading" style="display:none;background: url(\'/images/ajax/ajax-loader11.gif\') no-repeat scroll 0px 0px transparent;';
        ($fullscreen == 'true')?$html .= 'left:50%;':$html .= 'left:40%;';
        $html .= '"></div>';
        $photos->setHit($photo);
        if(!$this->_request->isXmlHttpRequest()) {
            $html .= '<script type="text/javascript">$(window).load(function(){$("body,html").scrollTop($(".n2s-container").offset().top);return false;});$(document).ready(function(){$("#n2l-boxPH").css({"height":$(window).height()});});</script>';
        }
        if($json == TRUE){
            $result = array('error'=>FALSE,'html'=>$html);
            $this->_helper->json($result);
        } else {
            $html .= '<input type="hidden" name="full" value="'.$this->view->translate('Press Esc to exit fullscreen').'"/>';
            $html .= '<input type="hidden" name="nfull" value="'.$this->view->translate('Enter Fullscreen').'"/>';            
            $this->view->html = $html;
        }
    }
}