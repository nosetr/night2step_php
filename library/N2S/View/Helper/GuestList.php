<?php

/**
 * GuestList.php
 * Description of GuestList
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 14.01.2013 14:23:27
 * 
 */
class N2S_View_Helper_GuestList extends Zend_View_Helper_Abstract
{
    function GuestList($id)
    {
        $html = '';
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $curuser = $auth->getIdentity()->userid;
        } else {
            $curuser = 0;
        }
        $events = new Default_Model_Events();
        $event = $events->getEvent($id);
        if($event->gastlist == 1 && $event->published == 1){
            $glist = new Default_Model_EventsGlist();
            $gcount = count($glist->getMembers($id));
            if($curuser != $event->creator && ($event->gastlistcount == 0 || $gcount < $event->gastlistcount)){
                $GLStart = new Zend_Date($event->glistStartDate);
                $GLEnd = new Zend_Date($event->glistEndDate);
                $now = Zend_Date::now();
                if($now->isEarlier($GLEnd) && $GLStart->isEarlier($now)){
                    if($curuser > 0)
                    {
                        $GLCheck = count($glist->checkMember($id, $curuser));

                        $html .= '<div id="setGList-loading'.$id.'" style="display: none;margin: 5px 0px;"><img src="images/ajax/ajax-loader1.gif" alt=""/></div>';
                        $html .= '<div id="setGList-text'.$id.'"';
                        if($GLCheck == 0)
                            $html .= ' style="display:none;"';
                        $html .= '><b>';
                        if($GLCheck > 0)
                            $html .= $this->view->translate('You\'re on the guest list');
                        $html .= '</b><span>';
                        $html .= '<a onclick="javascript:n2s.glist.set('.$id.',\'del\');" href="javascript:void(0);"><img id="resetLocButton" class="n2s-tooltip" title="'.$this->view->translate('Click here to reset').'" style="" src="/images/reset.png" alt=""/></a></span></div>';
                        $html .= '<a id="setGList-button'.$id.'"';
                        if($GLCheck > 0)
                            $html .= ' style="display:none;"';
                        $html .= ' onclick="javascript:n2s.glist.set('.$id.',\'set\');" href="javascript:void(0);">';
                        $html .= '<div id="archiveLink" class="n2s-tooltip" title="'.$this->view->shortText($event->glistdescription,80,FALSE,FALSE).'" style="margin: 3px 0px; float: left; background: url(/images/glist2.png) no-repeat scroll 0 0 transparent; padding: 0 10px 0 21px;">';
                        $html .= $this->view->translate('write you on the guest list');
                        $html .= '</div>';
                        $html .= '</a>';
                    } else {
                        $html .= '<div id="archiveLink" class="n2s-tooltip" title="'.$this->view->translate('Log in for guest list registration').'" style="cursor: default;color: #999999;font-weight: bold;margin: 3px 0px; float: left; background: url(/images/glist2.png) no-repeat scroll 0 0 transparent; padding: 0 10px 0 21px;">';
                        $html .= $this->view->translate('write you on the guest list');
                        $html .= '</div>';
                    }
                }
            } else {
                $html .= '<b><a href="';
                $html .= $this->view->url(array('module'=>'default',
                                        'controller'=>'events','action'=>'glist','id'=>$id),
                                        'default', true);
                $html .= '">'.$this->view->translate('Guest list count').':</a> '.$gcount;
                if($event->gastlistcount > 0)
                    $html .= ' '.$this->view->translate('from').' '.$event->gastlistcount;
                $html .= '</b>';
            }
        }
        /*else {
            $html = $this->view->translate('You can register on the guest list if you are logged');
        }
         * 
         */
        return $html;
    }
}