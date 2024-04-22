<?php

/**
 * AjaxController.php
 * Description of AjaxController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 25.10.2012 16:48:16
 * 
 */
class Default_AjaxController extends Zend_Controller_Action
{
    public function init()
    {
        
    }
    
    public function postactivAction()
    {
        $auth = Zend_Auth::getInstance();
        if($this->_request->isXmlHttpRequest() && $auth->hasIdentity()) {
            $error = TRUE;
            
            $task = (string)$this->_request->getParam('task');
            $id = (int)$this->_request->getParam('id',0);
            
            if($id > 0 && isset($task)){
                switch ($task){
                    case 'event':
                        $app = 'events';
                        break;
                    case 'venue':
                        $app = 'venues';
                        break;
                    case 'profil':
                        $app = 'profile';
                        break;
                    default :
                        goto END;
                }
            } else {
                goto END;
            }
            
            $cid = (int)$this->_request->getParam('cid',0);
            $text = (string)$this->_request->getParam('cont');
            $target = (string)$this->_request->getParam('target');

            if((isset($target) && $cid > 0 && ($target == 'video' || $target == 'image')) || isset($text)){
                $error = FALSE;
                
                $activ = new Community_Model_Activities();
                $actData = array(
                    'actor'=>$auth->getIdentity()->userid,
                    'app'=>$app,
                    'action'=>'post',
                    'comment'=>'post',
                    'title' => trim($text)
                );
                ($app == 'profile') ? $actData['target'] = $id : $actData['cid'] = $id;
                if(isset($text)){
                    $actData['title'] = trim($text);
                }
                if(isset($target) && $cid > 0){
                    $html = '{'.$target.'}{cid}{/'.$target.'}';
                    $actData['content'] = $html;
                    $actData['params'] = $target.'='.$cid;
                }
                
                $activ->setActiv($actData);
            }
            
            END:
                
            $result = array('error'=>$error);
            $this->_helper->json($result);
        } else {
            $this->_helper->redirector('notfound','Error','default');
        }
    }

    public function getvideoAction()
    {
        $auth = Zend_Auth::getInstance();
        if($this->_request->isXmlHttpRequest() && $auth->hasIdentity()) {
            $error = TRUE;
            $cid = FALSE;
            $html = '';
            $newvideo = (string)$this->_request->getParam('videoUrl');
            if(isset($newvideo)){
                $userID = $auth->getIdentity()->userid;
                $img = $this->__videoupdate($userID,$newvideo);
                if($img != NULL){
                    $error = FALSE;
                    $cid = $img['newVidID'];
                    $html .= '<div class="vidimgArr">';
                    $html .= '<div class="vtitA">'.$img['title'].'</div>';
                    $html .= '<div class="n2s-vidimg">';
                    $htmlLink = '<a class="n2s-video n2lbox.iframe" href="http://www.youtube.com/embed/'.$img['id'].'?autoplay=1">';
                    $html .= '<span class="timestamp">'.$htmlLink.$this->view->duration($img['dura']).'</a></span>';
                    $html .= $htmlLink.'<img src="'.$img["thumbnail"].'" alt=""/>';
                    $html .= '</a>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
            }
            $result = array('error'=>$error,'html'=>$html,'cid'=>$cid);
            $this->_helper->json($result);
        } else {
            $this->_helper->redirector('notfound','Error','default');
        }
    }
    
    private function __videoupdate($user,$newvideo)
    {
        $video = NULL;
        
        $fx = explode( '.', $newvideo );
        if (in_array("youtube", $fx) || in_array("youtu", $fx)) {
            preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $newvideo, $matches);
            if(isset($matches[0])){
                $video = $this->__listYoutubeVideo($matches[0]);

                $url = $video["thumbnail"];
                $basepath = 'images/videos/'.$user.'/thumbs/';
                $path = $basepath.$video["id"].'.jpg';
                if (is_dir(BASE_PATH.$basepath) == true){
                    $this->__imgUpload($url,'/'.$path);
                } else {
                    if (is_dir(BASE_PATH.'/images/videos/'.$user) == FALSE){
                        mkdir(BASE_PATH.'/images/videos/'.$user, 0755);
                        copy (BASE_PATH.'/images/index.html', BASE_PATH.'/images/videos/'.$user.'/index.html');
                    }
                    if (is_dir(BASE_PATH.'/images/videos/'.$user.'/thumbs/') == FALSE){
                        mkdir(BASE_PATH.'/images/videos/'.$user.'/thumbs/', 0755);
                        copy (BASE_PATH.'/images/index.html', BASE_PATH.'/images/videos/'.$user.'/thumbs/index.html');
                    }
                    $this->__imgUpload($url,'/'.$path);
                    
                    $data = array(
                        'title' => $video['title'],
                        'type'  => 'youtube',
                        'video_id'  => $video['id'],
                        'creator'   => $user,
                        'published' => 1,
                        'duration'  => $video['dura'],
                        'thumb' => $path,
                        'path'  => $newvideo
                    );
                }
            }
        }
        
        if($video != NULL && isset($data)){
            $videos = new Default_Model_Videos();
            $sMov = $videos->setMovie($data);
            if($sMov > 0)
                $video['newVidID'] = $sMov;
        }

        return $video;
    }
    
    private function __listYoutubeVideo($id)
    {
        $video = array();

        try {   
            $yt = new Zend_Gdata_YouTube();

            $videoEntry = $yt->getVideoEntry($id);

                $videoThumbnails = $videoEntry->getVideoThumbnails();
                $video = array(
                    'thumbnail' => $videoThumbnails[0]['url'],
                    'title' => $videoEntry->getVideoTitle(),
                    'description' => $videoEntry->getVideoDescription(),
                    'tags' => implode(', ', $videoEntry->getVideoTags()),
                    'url' => $videoEntry->getVideoWatchPageUrl(),
                    'flash' => $videoEntry->getFlashPlayerUrl(),
                    'dura' => $videoEntry->getVideoDuration(),
                    'id' => $videoEntry->getVideoId()
                );

        } catch (Exception $e) {
            /*
            echo $e->getMessage();
            exit();
            */
        }

        return $video;
    }
    
    protected function __imgUpload($url,$path)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);

        curl_close($ch);

        //file_put_contents($path, $data);
                    
        $im = imagecreatefromstring($data);
        $im2 = imagecreatetruecolor(480,360);
        imagecopyresampled($im2,$im,0,0,0,0,480,360,imagesx($im),imagesy($im));
        imagedestroy($im);
        //// and now do whatever you want with this image... write it to disk... whatever
        imagejpeg($im2, BASE_PATH.$path);
        // remember to free resources
        imagedestroy($im2);
    }

    public function imguploadAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()){
            $this->view->headLink()->appendStylesheet('/css/uploader.css');
            $this->view->jQuery()->addJavascriptFile('/js/plupload/plupload.js');
            $this->view->jQuery()->addJavascriptFile('/js/plupload/plupload.flash.js');
            $this->view->jQuery()->addJavascriptFile('/js/plupload/plupload.html5.js');
            $this->view->jQuery()->addJavascriptFile('/js/n2s.imgupload.js');
        }
    }
    
    public function slidesAction()
    {
        if($this->_request->isXmlHttpRequest()) {
            $albumID = (int)$this->_request->getParam( 'id' , 0 );
            $target = (string)$this->_request->getParam('target');
            if($target=='albums'){
                $albums = new Default_Model_PhotoAlbums();
                $album = $albums->getComAlbumInfo($albumID);
                if($album){
                    $photos = new Default_Model_Photos();
                    $lPhotos = $photos->getLastAlbumImg($album->creator, $albumID, 5, $album->photoid);
                    if(count($lPhotos)>=2){
                        $html = '<ul class="slides">';
                        foreach ($lPhotos as $ph){
                            (file_exists(BASE_PATH.'/'.$ph->original))?$albimg=$ph->original:$albimg=$ph->image;
                            $width = $ph->width;
                            $height = $ph->height;
                            if($width == 0 && $height == 0){
                                $width = 345;
                                $height= 196;
                            }
                            if($width > $height){
                                $height = ($height*345)/$width;
                                $width = 345;
                                if($height < 196){
                                    $width = ($width*196)/$height;
                                    $height = 196;
                                }
                            } else {
                                $width = ($width*196)/$height;
                                $height = 196;
                                if($width < 345){
                                    $height = ($height*345)/$width;
                                    $width = 345;
                                }
                            }
                            ($height>196)?$top=(196-$height)/4:$top=0;
                            $html .= '<li><img style="margin-top:'.$top.'px;width:'.$width.'px;height:'.$height.'px;" src="'.$albimg.'" alt=""/></li>';
                        }
                        $html .= '</ul>';
                        $result = array('error'=>FALSE,'html'=>$html);
                    }else{
                        $result = array('error'=>true);
                    }
                }else{
                    $result = array('error'=>true);
                }
            }else{
                $result = array('error'=>true);
            }
            $this->_helper->json($result);
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
    }
    
    public function photorotateAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity() || !$this->_request->isXmlHttpRequest()) {
            $this->_helper->redirector('notfound','Error','default');
        }else{
            $user = $auth->getIdentity()->userid;
        }
        
        $id = (int)$this->_request->getParam('photo');
        
        $filter = new N2S_Filter_File_Rotate(array(
            'degrees' => -90
        ));
        $error = $filter->filter($id);
        
        if($error == TRUE){
            $result = array('error'=>TRUE,'message'=> $this->view->translate('Error'));
        } else {
            $photos = new Default_Model_Photos();
            $photo = $photos->setAfterRotateSize($id);
            $result = array('error'=>FALSE);
        }
        $this->_helper->json($result);
    }

    public function thumbuploadAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity() || !$this->_request->isXmlHttpRequest()) {
            $this->_helper->redirector('notfound','Error','default');
        }else{
            $user = $auth->getIdentity()->userid;
        }
        
        $id = (int)$this->_request->getParam('id');
        $x = (int)$this->_request->getParam('x1');
        $x2 = (int)$this->_request->getParam('x2');
        $y = (int)$this->_request->getParam('y1');
        $w = $x2 - $x;
        
        $photos = new Default_Model_Photos();
        $photo = $photos->getPhotoID($id);
        if($photo && $photo->creator==$user){
            list($width, $height) = getimagesize(BASE_PATH.'/'.$photo->original);
            if($width >= $height && $width > 550){
                $width = 550;
            } elseif($height > $width && $height > 550){
                $width = 550 / $height * $width;
            }
            $path = pathinfo(BASE_PATH.'/'.$photo->thumbnail);
            $filter = new N2S_Filter_File_Cropthumb(array(
                'width' => $width,
                'thumbwidth' => 134,
                'thumbheight' => 134,
                'x'=>$x,
                'y'=>$y,
                'w'=>$w,
                'directory'=>$path['dirname'],
                'name'  => 'thumb_'
            ));
            $filter->filter(BASE_PATH.'/'.$photo->original);
            //$html = '<img src="'.$photo->thumbnail.'" style="float:left;" alt=""/>';
            $result = array('error'=>FALSE);
        }else{
            $result = array('error'=>TRUE,'message'=> $this->view->translate('Error'));
        }
        $this->_helper->json($result);
    }

    public function thumbAction()
    {
        $this->_helper->layout()->disableLayout();
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_helper->redirector('notfound','Error','default');
        }else{
            $user = $auth->getIdentity()->userid;
        }
        $target = (string)$this->_request->getParam('target');
        $photos = new Default_Model_Photos();
        $photo = $photos->getPhotoID($target);
        if($photo && $photo->creator==$user){
            $this->view->title = $this->view->translate('Change a thumbnail');
            list($width, $height) = getimagesize(BASE_PATH.'/'.$photo->original);
            if($width > $height && $width > 550){
                $height = 550 / $width * $height;
                $width = 550;
            } elseif($height > $width && $height > 550){
                $width = 550 / $height * $width;
                $height = 550;
            } elseif($width == $height && $width > 550){
                $width = 550;
                $height = 550;
            }
            
            //if($height < 134){
            //    $this->view->max = $height;
            //}else{
                $this->view->max = 134;
            //}
            $this->view->photo = $this->view->baseUrl().$photo->original;
            $this->view->id = $target;
            
            $this->view->width = $width;
            $this->view->height = $height;            
        }
    }

    public function imgupAction()
    {
        $this->_helper->layout()->disableLayout();
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity() || !$this->_request->isXmlHttpRequest()) {
            $this->_helper->redirector('notfound','Error','default');
        }
        $task = (string)$this->_request->getParam('task');
        $target = (string)$this->_request->getParam('target');
        if($target){
            $this->view->target = '/target/'.$target;
        } else {
            $this->view->target = '';
        }
        
        switch ($task){
            case 'avatarchange':
                $this->view->title = $this->view->translate('Choose a photo');
                $this->view->js = 'changeavat';
                break;
            case 'usbannchange':
                $this->view->title = $this->view->translate('Choose a background photo');
                $this->view->js = 'changebg';
                break;
            default:
                $this->_helper->redirector('notfound','Error','default');
        }
    }
    
    public function getphotoAction()
    {
        if($this->_request->isXmlHttpRequest()) {
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity())
                die ($this->_helper->json(array('error'=>TRUE)));
            $photoID = (int)$this->_request->getParam('id',0);
            $target = (string)$this->_request->getParam('target');
            $objID = (int)$this->_request->getParam('obj',0);
            $task = (string)$this->_request->getParam('task');
            $userID = $auth->getIdentity()->userid;
            $photos = new Default_Model_Photos();
            $photo = $photos->getEditPhoto($userID, $photoID);
            if($target == 'event'){
                $events = new Default_Model_Events();
                $event = $events->getEvent($objID);
                $creator = $event->creator;
                $curUserID = $auth->getIdentity()->userid;
            } elseif ($target == 'venue') {
                $events = new Default_Model_Adresses();
                $event = $events->getAdress($objID);
                $creator = $event->creator;
                $checkAdmin = new Community_Model_Admins();
                $curUserID = $checkAdmin->getCuruser($event->creator, 'venue');
            } elseif ($target == 'profil'){
                $users = new Community_Model_Users();
                $event = $users->getUser($objID);
                $creator = $event->userid;
                $curUserID = $auth->getIdentity()->userid;
            }
            if(count($photo)>0 && count($event)>0 && $creator == $curUserID){
                if($task == 'avatar'){
                    $html = '<div id="avSn2s">';
                    $html .= '<a class="n2s-phBox n2lbox.ajax" href="';
                    $html .= $this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$photoID),'default', true);
                    $html .= '">';
                    if($target == 'event'){
                        $events->eventImgUpdate($objID, $photoID);
                        $html .= '<img id="n2simg-avat_surround" src="'.$photo->image.'" alt=""/>';
                    } elseif ($target == 'venue') {
                        $users = new Community_Model_Users();
                        $users->updateAvatar($creator, $photoID);
                        $events->adressImgUpdate($objID, $photoID);
                        $html .= '<img id="n2simg-avat_surround" src="'.$photo->image.'" alt=""/>';
                    } elseif ($target == 'profil') {
                        $users->updateAvatar($creator, $photoID);
                        $html .= '<img id="n2simg-avat_surround" src="'.$photo->image.'" alt=""/>';
                    }
                    $html .= '</a></div>';
                }else{
                    if ($target == 'venue') {
                        $userID = $curUserID;
                    }
                    $banners = new Default_Model_Background();
                    $banners->setImg($userID,$target,$objID,$photoID);
                    list($width, $height) = getimagesize($photo->original);
                    $p = 740/$width;
                    $h = $height*$p;
                    $html = '<img id="n2s-moveable" rel="'.$h.'" style="position:absolute;width:740px;top:0px;" src="'.$photo->original.'" alt=""/>';
                }
                $result = array('error'=>FALSE,'html'=>$html);
            } else {
                $result = array('error'=>TRUE,'message'=> '<div>'.$this->view->translate('There are no photos in this album').'</div>');
            }
            $this->_helper->json($result);
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
    }

    public function photosAction()
    {
        if($this->_request->isXmlHttpRequest()) {
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity())
                die ($this->_helper->json(array('error'=>TRUE)));
            $albumID = (int)$this->_request->getParam( 'album' , 0 );
            $page = (int)$this->_request->getParam( 'page' , 1 );
            $userID = $auth->getIdentity()->userid;
            $photos = new Default_Model_Photos();
            $photo = $photos->getAllAlbumPhotos($userID, $albumID);
            if(count($photo)>0){
                $albums = new Default_Model_PhotoAlbums();
                $album = $albums->getComAlbumInfo($albumID);
                $paginator = Zend_Paginator::factory($photo);
                $paginator->setItemCountPerPage(12);
                $paginator->setCurrentPageNumber($page);
                $html = '<div style="padding: 5px;">';
                $html .= '<h3>'.$album->name.'</h3>';
                $html .= $this->view->paginationControl($paginator, 'Sliding', '_partials/ajaxpagination.phtml');
                foreach ($paginator as $alb)
                {
                    $html .= '<div rel="'.$alb->id.'" class="n2lbox-albthumbphbox" onclick="$(this).toggleClass(\'checked-albthumbphbox\').siblings().removeClass(\'checked-albthumbphbox\');n2s.n2lbox.check(this);"><img style="width: 134px; height: 134px;" src="'.$alb->thumbnail.'" alt=""/></div>';
                }
                $html .= '</div>';
                $result = array('error'=>FALSE,'html'=>$html);
            } else {
                $result = array('error'=>TRUE,'message'=> '<div>'.$this->view->translate('There are no photos in this album').'</div>');
            }
            $this->_helper->json($result);
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
    }
    
    public function removephotoAction()
    {
        if($this->_request->isXmlHttpRequest()) {
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity())
                die ($this->_helper->json(array('error'=>TRUE)));
            $task = (string)$this->_request->getParam('task');
            $target = (string)$this->_request->getParam( 'target');
            $objID = (int)$this->_request->getParam( 'obj' , 0 );
            if($task=='avatar'){
                $html = '<div id="avSn2s">';
                if($target == 'event'){
                    $events = new Default_Model_Events();
                    $event = $events->getEvent($objID);
                    if($event->creator == $auth->getIdentity()->userid){
                        $events->eventImgUpdate($objID, 0);
                        $html .= '<img id="n2simg-avat_surround" src="'.$this->view->baseUrl().'images/no-photo.png" alt=""/>';
                    }else{
                        die ($this->_helper->json(array('error'=>TRUE)));
                    }
                } elseif ($target == 'venue'){
                    $venues = new Default_Model_Adresses();
                    $venue = $venues->getAdress($objID);
                    $checkAdmin = new Community_Model_Admins();
                    $curUserID = $checkAdmin->getCuruser($venue->creator, 'venue');
                    if($venue->creator == $curUserID){
                        $users = new Community_Model_Users();
                        $users->updateAvatar($venue->creator, 0);
                        $venues->adressImgUpdate($objID, 0);
                        $html .= '<img id="n2simg-avat_surround" src="'.$this->view->baseUrl().'images/no-photo-marker.png" alt=""/>';
                    }else{
                        die ($this->_helper->json(array('error'=>TRUE)));
                    }
                } elseif ($target == 'profil'){
                    $users = new Community_Model_Users();
                    $user = $users->getUser($objID);
                    if($user->userid == $auth->getIdentity()->userid){
                        $users->updateAvatar($user->userid, 0);
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
                        $html .= '<img id="n2simg-avat_surround" src="'.$this->view->baseUrl().'images/avatar/default/'.$gender.'.jpg" alt=""/>';
                    }else{
                        die ($this->_helper->json(array('error'=>TRUE)));
                    }
                } else {
                    die ($this->_helper->json(array('error'=>TRUE)));
                }
                $html .= '</div>';
            }else{
                $banners = new Default_Model_Background();
                $banners->delImg($objID, $target);
                $html = '<div id="n2s-newloadban"><div id="browse" style="position:absolute;width:740px;height:198px;border:1px solid #999999;"><a class="n2s-imgup n2lbox.ajax" href="/ajax/imgup/task/usbannchange"><p style="font-weight: bold; font-size: 20px; text-align: center; color: rgb(170, 170, 170); padding: 86px 0px;">'.$this->view->translate('Click to add a photo').'</p></a></div></div>';
            }
            $result = array('error'=>FALSE,'html'=>$html);
            $this->_helper->json($result);
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
    }
    
    public function removeAction()
    {
        if($this->_request->isXmlHttpRequest()) {
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity())
                die ($this->_helper->json(array('error'=>TRUE)));
            $html = '<div id="n2lbox-remove" style="text-align: center; font-size: 20px; font-weight: bold; padding: 60px 0px 0px; color: rgb(170, 170, 170);">'.$this->view->translate('To remove a photo click save below').'</div>';
            $html .= '<script type="text/javascript">$("#n2lbox-submit").removeAttr("disabled");</script>';
            $result = array('error'=>FALSE,'html'=>$html);
            $this->_helper->json($result);
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
    }
    
    public function uploadAction()
    {
        if($this->_request->isXmlHttpRequest()) {
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity())
                die ($this->_helper->json(array('error'=>TRUE)));
            $target = (string)$this->_request->getParam('target');
            if($target == 'avatar'){
                $url = '/events/ajax/act/'.$target;
            }else{
                $url = '/events/ajax/act/upbanner';
            }
            //$userID = $auth->getIdentity()->userid;
            $html = '<div id="n2lbox-upload" style="width:100%;height:100%;"><div id="n2l-droparea"><p>'.$this->view->translate('Drag & Drop Image Files').'</p><span class="or">'.$this->view->translate('or').'</span><a id="n2l-browse" href="#" style="position: relative; z-index: 0;">'.$this->view->translate('select').'</a></div></div>';
            $html .= '<script type="text/javascript">';
            $html .= '$(document).ready(function() {';
            $html .= 'if($("#browse").length) $("#browse").css({"top":$("#proPr1").position().top+"px"});';
            $html .= 'var targetid = $(\'input[name="active-id"]\').val();';
            $html .= 'var forval = $(\'input[name="active-show"]\').val();';
            $html .= 'var uploader = new plupload.Uploader({runtimes: "html5,flash",contains: "n2s-backImg",browse_button: "n2l-browse",multi_selection:false,drop_element: "n2l-droparea",url: "'.$url.'",flash_swf_url: "js/plupload/plupload.flash.swf",multipart: true,urlstream_upload: true,multipart_params:{id:targetid,for:forval},resize: {width: 740,height:960,quality:100},filters: [{title: "Images", extensions: "jpg,png,gif,jpeg"}]});uploader.init();';
            $html .= 'uploader.bind("FilesAdded", function(up,files){$("#n2lbox-loading").show();uploader.start();uploader.refresh();});';
            $html .= 'uploader.bind("Error", function(up,err){alert(err.message);uploader.refresh();});uploader.bind("FileUploaded", function(up,file,response){data = $.parseJSON(response.response);if(data.error){alert(data.message);} else {';
            if($target == 'avatar'){
                $html .= '$("#n2s-useravatar").find("#avSn2s").remove();$("#n2s-useravatar").prepend(data.html);';
            }else{
                $html .= 'if($("#n2s-backImg #n2s-moveable").length) $("#n2s-backImg #n2s-moveable").remove();if($("#n2s-newloadban").length) $("#n2s-newloadban").remove();$("#n2s-backImg").prepend(\'<img id="n2s-moveable" rel="\'+data.height+\'" style="position:absolute;top:0;width:740px;" src="\'+data.img+\'" alt=""/>\').bind(n2s.ajax.banner());';
            }
            $html .= '$.n2lbox.close();}});';
            $html .= '});</script>';
            $result = array('error'=>FALSE,'html'=>$html);
            $this->_helper->json($result);
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
    }
    
    public function multialbuploadAction()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_helper->redirector('notfound','Error','default');
            die();
        }
        
        $user = $auth->getIdentity()->userid;
        
        if ($this->_request->isPost())
        {
            $userID = $this->_request->getPost('userid',0);
            if ($userID != $user) {
                $result = array('error'=>true,'message'=>'Error');
                $this->_helper->json($result);
            }
            
            $albumID = $this->_request->getPost('albumid',0);
            $albums = new Default_Model_PhotoAlbums();
            $album = $albums->getComAlbumInfo($albumID);
            if(isset($album) && $album->creator==$user){
                $basePath   = BASE_PATH.'/albums/';
                $index      = $basePath.'index.html';
                $albPath    = $basePath.$albumID;
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
            } else {
                $result = array('error'=>true,'message'=>'Error');
                $this->_helper->json($result);
            }
            
            @set_time_limit(5 * 60);//5 minutes
            try
            {
                $adapter = new Zend_File_Transfer_Adapter_Http();
                $adapter->addValidator('Count',false, array('min'=>1, 'max'=>100))
                        ->addValidator('Size',false,$this->view->returnBytes(ini_get('upload_max_filesize')))
                        ->addValidator('Extension',false,array('extension' => 'JPG,JPEG,PNG,GIF,jpg,jpeg,png,gif','case' => true));

                $adapter->setDestination($origPath);

                $files = $adapter->getFileInfo();
                $count = $album->photocount;
                foreach($files as $fieldname=>$fileinfo)
                {
                    if (($adapter->isUploaded($fileinfo['name']))&& ($adapter->isValid($fileinfo['name'])))
                    {
                         // Clean the fileName for security reasons
                        $fileName = md5(preg_replace('/[^\w\._]+/', '_', $fileinfo['name']));
                        $filenameF = $fileName.'_'.time();
                        $filename = $filenameF.'.png';

                        $adapter->addFilter('Rename',array('target'=>$origPath.$filename,'overwrite'=>false))
                            ->addFilter(new N2S_Filter_File_Resize(array(
                                        'width' => 200,
                                        'height' => 960,
                                        'keepRatio' => true,
                                        'directory' => $photoPath
                                    )))
                            ->addFilter(new N2S_Filter_File_Multicropthumb(array(
                                        'thumbwidth' => 134,
                                        'thumbheight' => 134,
                                        'directory' => $photoPath,
                                        'name'  => 'thumb_'
                                    )));
                        $adapter->receive($fileinfo['name']);
                        
                        if ($adapter->receive()) {
                            $count++;
                            $baseImgPath = 'albums/'.$albumID;
                        
                            $imgPath = $baseImgPath.'/photos/'.$filename;
                            chmod($imgPath, 0644);
                            $thumbPath = $baseImgPath.'/photos/thumb_'.$filename;
                            chmod($thumbPath, 0644);
                            $origimgPath = $baseImgPath.'/originalphotos/'.$filename;
                            chmod($origimgPath, 0644);

                            list($width, $height) = getimagesize($origimgPath);
                            
                            $photos = new Default_Model_Photos();
                            $photo = $photos->setPhoto($userID,$albumID,$imgPath,$thumbPath,$origimgPath,$filenameF,$width,$height,1);
                            
                            //Set Activity "AlbumUpload"
                            $params = new N2S_Params();
                            $params->set('photoid', $photo,TRUE);
                            $pString = NULL;
                            $paramCount = 1;
                            $update = FALSE;
                            $activs = new Community_Model_Activities();
                            $activ = $activs->getDayActiv($user, $albumID, 'albums', 'uploaded');
                            if(count($activ) > 0){
                                $update = TRUE;
                                foreach ($activ as $act){
                                    $actID = $act->id;
                                    $pString = $act->params;
                                    $param = $params->get($act->params);
                                    if(isset($param['count']) && $param['count'] > 0){
                                        $paramCount = $param['count'] +1;
                                    }
                                    break;
                                }
                            }
                            $params->set('count',$paramCount);
                            $paramstr = $params->toString($pString);
                            $titel = '{actor} has uploaded {multiple}{count} new photos{/multiple}{single}a new photo{/single} in album {cid}';

                            $actData = array(
                                    'actor'=>$user,
                                    'title'=>$titel,
                                    'app'=>'albums',
                                    'action'=>'uploaded',
                                    'cid'=>$albumID,
                                    'locid'=>$album->locid,
                                    'comment'=>'albums',
                                    'params'=>$paramstr,
                                    'permission'=>$album->permissions
                                    );
                            ($update == TRUE)?$activs->updateActiv($actID, $actData, TRUE):$activs->setActiv($actData);
                            
                            $selectalbum = $albums->getComAlbum($userID);
                            
                            $select = '<div class="n2s-movephoto">
                                        <div class="n2s-movelink">'.$this->view->translate('Move in album').'</div><ul class="showcasten">';
                            foreach ($selectalbum as $alb){
                                if($alb->id != $albumID){
                                    $jsMove = "'".$photo."','".$alb->id."','".$albumID."'";
                                    $select .= '<li onclick="photos.moveImg('.$jsMove.')">'.$alb->name.'</li>';
                                }
                            }
                            $select .= '</ul></div>';
                            
                            $html = '<li class="newsfeed-item"><div id="photo_orig_view'.$photo.'">';
                            $html .= '<a class="n2s-phBox n2lbox.ajax" href="';
                            $html .= $this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$photo,'show'=>'album')).'">';
                            $html .= '<img src="'.$thumbPath.'" style="float:left;" alt=""/>';
                            $html .= '</a>';
                            $html .= '<div style="margin-left: 150px;min-height: 135px;">';
                            $html .= '<div id="photo_save_result'.$photo.'">'.$this->view->translate('Description').'</div>';
                            $html .= '<div id="photo_save_progress'.$photo.'" class="photo_save_progress" style=""></div>';
                            $html .= '<textarea id="photo_caption'.$photo.'" class="photo_edit_caption" style="overflow: hidden; min-height: 40px; height: 39.7667px; width: 550px;" onchange="photos.saveDesc(\''.$photo.'\',\''.$albumID.'\')"></textarea>'.$select;
                            $html .= '<a class="n2s-imgup n2lbox.iframe black delPh" href="';
                            $html .= '/ajax/thumb/target/'.$photo;
                            $html .= '">'.$this->view->translate('Edit thumbnail').'</a>';
                            $html .= '<div id="coverCh'.$photo.'" class="delPh" onclick="photos.updateCover('.$photo.','.$albumID.')">'.$this->view->translate('Set as cover').'</div>';
                            $html .= '<div style="float: left; width: 100%;">';
                            $html .= '<div class="delPh deleter" onclick="photos.delImg(\''.$photo.'\',\''.$albumID.'\')">'.$this->view->translate('Delete').'</div>';
                            $html .= '<div class="delPh rotor" onclick="photos.rotImg(\''.$photo.'\',\''.$albumID.'\')">'.$this->view->translate('Rotate').'</div>';
                            $html .= '</div>';
                            $html .= '<div class="clear"></div></div></div>';
                            $html .= '<div style="display: none;" id="photo_after_view'.$photo.'">'.$this->view->translate('restore').'</div></li>';
                            
                            $result = array('error'=>false,'html'=>$html);
                        } else {
                            $result = array('error'=>true,'message'=>'Error');
                        }
                    } else {
                        $result = array('error'=>true,'message'=>'Error');
                    }
                }
                $data = array('photocount'=>$count);
                $albums->updateAlbum($albumID, $data);
                if($album->partypics == 1){
                    $ajaxList = new Default_Model_Ajaxlist();
                    $check = $ajaxList->checkList($albumID, 'photo');
                    if(count($check)>0){
                        $date = new Zend_Date($check->time);
                        $today = Zend_Date::now();
                        if($date->isEarlier($today)){
                            $ajaxList->updateSpecial($albumID, 'photo', $count);
                        }
                    }
                }
            }
            catch (Exception $ex)
            {
                echo "Exception!\n";
                echo $ex->getMessage();
            }
            $this->_helper->json($result);
        }
    }
    
    public function setalbcoverAction()
    {
        if($this->_request->isXmlHttpRequest()) {
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity()){
                die ($this->_helper->json(array('error'=>TRUE,'message'=>'Error')));
            }else{
                $userID = $auth->getIdentity()->userid;
                $photoID = (int)$this->_request->getParam('photo');
                $albumID = (int)$this->_request->getParam('album');
                $albums = new Default_Model_PhotoAlbums();
                $album = $albums->getComAlbumInfo($albumID);
                if ($userID == $album->creator){
                    if($photoID > 0){
                        $photos = new Default_Model_Photos();
                        $photo = $photos->getEditPhoto($userID,$photoID);
                        if(count($photo)>0){
                            $thH = $photo->thumbnail;
                            $text = $this->view->translate('Remove cover');
                        }else{
                            die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                        }
                    }else{
                        $thH = 'images/no-photo-thumb.png';
                        $text = $this->view->translate('Set as cover');
                    }
                    $albums->updateComProfilImgAlbum($albumID,$photoID);
                    $html = '<img src="'.$thH.'" alt=""/>';
                    $result = array('error'=>false,'html'=>$html,'text'=>$text);
                } else {
                    $result = array('error'=>true,'message'=>'Error');
                }
                $this->_helper->json($result);
            }
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
    }

    public function albumsAction()
    {
        if($this->_request->isXmlHttpRequest()) {
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity())
                die ($this->_helper->json(array('error'=>TRUE)));
            $page = $this->_request->getParam( 'page' , 1 );
            $userID = $auth->getIdentity()->userid;
            $albums = new Default_Model_PhotoAlbums();
            $album = $albums->getComAlbum($userID,NULL,TRUE);
            if(count($album) > 0){
                $paginator = Zend_Paginator::factory($album);
                $paginator->setItemCountPerPage(12);
                $paginator->setCurrentPageNumber($page);
                $photos = new Default_Model_Photos();
                $html = '<div style="padding: 5px;">';
                $html .= $this->view->paginationControl($paginator, 'Sliding', '_partials/ajaxpagination.phtml');
                foreach ($paginator as $alb)
                {
                    $aPhoto = $photos->getLastAlbumImg($userID, $alb->id,1);
                    if($aPhoto){
                        $photo = $photos->getPhotoID($alb->photoid);
                        $html .= '<a class="n2lbox-albinfoboxlink" href="/ajax/photos/album/'.$alb->id.'"><div class="n2lbox-albinfobox"><img style="width: 134px; height: 134px;" class="n2lbox-albthumbbox" src="';
                        if($photo){
                            $html .= $photo->thumbnail;
                        }else{
                            $html .= $aPhoto->thumbnail;
                        }
                        $html .= '" alt=""/><div class="n2lbox-albiname">'.$alb->name.'</div></div></a>';
                    }
                }
                $html .= '</div>';
                
                $result = array('error'=>FALSE,'html'=>$html);
            } else {
                $result = array('error'=>TRUE,'message'=> '<div>'.$this->view->translate('You have no photos').'</div>');
            }
            $this->_helper->json($result);
        }else{
            $this->_helper->redirector('notfound','Error','default');
        }
    }
}
