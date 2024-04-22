<?php

/**
 * Comments.php
 * Description of Comments
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 31.10.2012 12:57:36
 * 
 */
class N2S_View_Helper_Comments extends Zend_View_Helper_Abstract
{
    function comments($id,$type,$count=null,$fixIt=TRUE,$large=100)
    {
        $comments = new Default_Model_Comments();
        $result = $comments->getComments($id, $type);
        $spam = FALSE;
        
        $html = '<div id="comBox'.$id.'" class="n2s-commentsBox">';
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity()->userid;
            $spam = Default_Model_Comments::checkAdmin($user,$id,$type);

            $html .= '<ul class="comFixAr" id="fixAr'.$id.'"><li class="cmBAr comBoxEnterAr'.$id.'">'.$this->view->userThumb($auth->getIdentity()->userid,1,0,false);
            $html .= '<div style="margin-left: 45px;">';
            $html .= '<textarea onkeydown="javascript:comment.onchange(this)" class="'.$id.'" id="'.$id.'" cols="1" rows="1" ';
            $html .= 'style="width: 93%; height: 15px;" rel="'.$type.'" ';
            $html .= 'name="comment" placeholder="'.$this->view->translate('Write a comment...').'"></textarea>';
            $html .= '<div style="font-size: 8px; color: gray;">'.$this->view->translate('Press Enter to post.').'</div>';
            $html .= '</div>';
            $html .= '<div class="clear"></div>';
            $html .= '<div class="ajaxloadTop'.$id.'" ';
            $html .= 'style="display:none;padding: 3px 5px 0 0;"><img src="images/ajax/ajax-loader1.gif" alt=""/></div></li></ul>';
        }
        $html .= '<ul id="comList'.$id.'" class="comLBod">';
        if(count($result)>0){
            $i = 0;
            
            $check = new Default_Model_Ajaxaction();
            $removed = array();
            foreach ($result as $r)
            {
                $d = $check->getDelComment($r->id);
                if(isset($d))
                    $removed[] = $r->id;
            }
            
            $toView = count($result) - count($removed);
        
            foreach ($result as $r){
                $list = $check->getDelComment($r->id);
                if(!isset($list) && ($count == null || ($count != null && $i < $count))){
                    $i++;
                    $html .= '<li id="comCont'.$r->id.'" class="comCont'.$r->id.'">'.$this->view->userThumb($r->post_by,1,1);
                    $html .= '<div style="margin: 0px 10px 0px 45px;"><div>'.$this->view->shortText($this->view->escape($r->comment),$large,TRUE,FALSE).'</div>';
                    $html .= '<div style="float: left;color: #555555;font-size: 10px;"><b>'.$this->view->timeStamp($r->date).'</b>';
                    if($auth->hasIdentity() && $r->post_by == $user){
                        $html .= '<span onclick="javascript:comment.comdel('.$r->id.')" class="comDel" id="'.$r->id.'"><b> · '.$this->view->translate('delete').'</b></span>';
                    } elseif($spam == TRUE) {
                        $html .= '<span onclick="javascript:comment.comspam('.$r->id.')" class="comSpam" id="'.$r->id.'"><b> · '.$this->view->translate('spam').'</b></span>';
                    }
                    $html .= '</div></div><div class="clear"></div></li>';
                    $lastID = $r->id;
                }
            }
            if($count != null && $toView > $count && isset($lastID)){
                $html .= '<li onclick="javascript:comment.more(this)" class="comMore n2s-view" id="'.$id.'" rel="'.$type.'" last="'.$lastID.'">'.$this->view->translate('view all').'&nbsp;('.$toView.')</li>';
            }
        }
        $html .= '<li class="liLod"><div class="ajaxloadBottom'.$id.'" ';
        $html .= 'style="display:none;padding: 3px 5px 0px;"><img src="images/ajax/ajax-loader1.gif" alt=""/></div></li>';
        $html .= '</ul></div>';
        if ($fixIt == TRUE){
            $html .= '<script type="text/javascript">';
            $html .= 'var bWidth = $("#comBox'.$id.'").width();';
            $html .= 'var comBoxWidth = bWidth +"px";';
            $html .= '$(function () {';
            $html .= '$(window).bind("scroll", function(event) {';
            $html .= 'comment.fixIt('.$id.');});});</script>';
        }
        return $html;
    }
}