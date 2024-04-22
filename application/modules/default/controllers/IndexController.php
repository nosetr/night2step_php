<?php

class Default_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        if ($this->_helper->FlashMessenger->hasMessages()) {
            $this->view->flashmessage = $this->_helper->FlashMessenger->getMessages();
        }
    }

    public function indexAction()
    {
        //META
        $this->view->headMeta($this->view->serverUrl().'/images/n2s-logos/n2s_logo.png','og:image','property');
        $keywords = $this->view->translate('Party photos,Partyphotos,Eventphotos,Events,Clubs,Disko,Disco,Party,nights,night2step');
        $this->view->headMeta($keywords,'keywords','name');
        $ogDesc = $this->view->translate('night2step is the community for going out in your near. Find events, clubs and party pictures! Find out where the best parties take place in your city at the weekend.');
        $ogDesc = $this->view->shortText($ogDesc,160);
        $this->view->headMeta($ogDesc,'description','name');
        //END META
        $this->view->headLink()->appendStylesheet('/css/home.css');
        $html = '<div class="modSurH">';
        $html .= $this->view->homeEvents();
        $html .= '</div>';
        
        $html .= '<div class="modSurH left smlMod">';
        $html .= $this->view->homeAlbums();
        $html .= '</div>';
        /*
        $html .= '<div class="modSurH right smlMod">';
        $html .= $this->view->homeReklame();
        $html .= '</div>';
         * 
         */
        
        $html .= '<div class="modSurH right smlMod">';
        $html .= $this->view->homeVenues();
        $html .= '</div>';
        
        $html .= '<div class="modSurH right smlMod">';
        $html .= $this->view->homeUsers();
        $html .= '</div>';
        
        $this->view->html = $html;
    }
}