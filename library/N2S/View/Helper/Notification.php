<?php
class N2S_View_Helper_Notification extends Zend_View_Helper_Abstract
{
    function notification($userID)
    {
        $fr = new Community_Model_FrRequest();
        $frCount = count($fr->getAjaxRead($userID));
        $msg = new Community_Model_MsgRecepient();
        $msgCount = count($msg->getAjaxRead($userID));
        $glob = new Community_Model_Notifications();
        $globCount = count($glob->getAjaxRead($userID));
        
        $lhtml = '<div id="notif">';
        $lhtml .= '<div id="notifGlob">';
        $lhtml .= '<a class="n2s-tooltip jsGlobalsNot jsIr" title="'.$this->view->translate('Global notifications').'" href="javascript:void(0);" onclick="javascript:noti.showGlob();">';
        $lhtml .= '<div id="glCNT" class="notCount">';
        if($globCount > 0){
            if($globCount < 1000){
                $lhtml .= $globCount;
            } else {
                $lhtml .= '>999';
            }
        }
        $lhtml .= '</div>'.$this->view->translate('Global notifications');
        $lhtml .= '</a></div>';
        
        $lhtml .= '<div id="notifFriends">';
        $lhtml .= '<a class="n2s-tooltip jsGlobalsNot jsIr" title="'.$this->view->translate('Friendship requests').'" href="javascript:void(0);" onclick="javascript:noti.showFr();">';
        $lhtml .= '<div id="frCNT" class="notCount">';
        if($frCount > 0){
            if($frCount < 1000){
                $lhtml .= $frCount;
            } else {
                $lhtml .= '>999';
            }
        }
        $lhtml .= '</div>'.$this->view->translate('Friendship requests');
        $lhtml .= '</a></div>';
        
        $lhtml .= '<div id="notifMessage">';
        $lhtml .= '<a class="n2s-tooltip jsGlobalsNot jsIr" title="'.$this->view->translate('New messages').'" href="javascript:void(0);" onclick="javascript:noti.showMsg();">';
        $lhtml .= '<div id="msgCNT" class="notCount">';
        if($msgCount > 0){
            if($msgCount < 1000){
                $lhtml .= $msgCount;
            } else {
                $lhtml .= '>999';
            }
        }
        $lhtml .= '</div>'.$this->view->translate('New messages');
        $lhtml .= '</a></div>';
        
        $lhtml .= '<div id="notifList">';
        $lhtml .= '<img id="notiLoad" src="/images/ajax/ajax-loader1.gif" style="margin: 15px 43%; display: none;" />';
        $lhtml .= '<div id="notifArrow"></div>';
        $lhtml .= '<div id="nList"></div>';
        $lhtml .= '<div id="nLink">';
        $lhtml .= '<a id="notiShowAllLink" href="">'.$this->view->translate('view all').'</a>';
        $lhtml .= '</div></div></div>';
        $lhtml .= '<script  type="text/javascript">$(window).load(function(){noti.check();});</script>';
        return $lhtml;
    }
}