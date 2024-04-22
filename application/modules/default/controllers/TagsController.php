<?php

/**
 * TagsController.php
 * Description of TagsController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.11.2012 12:03:17
 * 
 */
class Default_TagsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $results = Default_Model_Tags::search($this->_getParam('term'));
        $this->_helper->json(array_values($results)); 
    }
    
    public function adressAction()
    {
        $results = Default_Model_Adresses::search($this->_getParam('term'));
        $this->_helper->json(array_values($results)); 
    }
    
    public function albumAction()
    {
        $results = Default_Model_PhotoAlbums::search($this->_getParam('album_id'));
        $this->_helper->json($results); 
    }
    
    public function termsAction()
    {
        $query = trim(preg_replace('/[^\ \p{L}]/u', ' ',$this->_getParam('term')));
        $rArray = array();
        $path = APPLICATION_PATH . '/../search/index_multi';
        $index = Zend_Search_Lucene::open($path);
        
        Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(1);
        Zend_Search_Lucene::setResultSetLimit(5);
        Zend_Search_Lucene::setDefaultSearchField('title');
        
        if($query && strlen($query) > 1){
            $results = $index->find(
                    $q = Zend_Search_Lucene_Search_QueryParser::parse(
                             $query.'*','utf-8'));
        }
        
        if($query && isset($results) && count($results) > 0){
            foreach ($results as $result){
                $ar = array('value' => $result->title);
                $rArray[] = $ar;
                //$rArray['label'] = $result->title;
            }
        }
        $this->_helper->json(array_values($rArray));
    }
}