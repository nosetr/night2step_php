<?php

/**
 * SearchController.php
 * Description of SearchController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 08.10.2013 11:43:38
 * 
 */
class Default_SearchController extends Zend_Controller_Action
{
    public function init()
    {
        if ($this->_helper->FlashMessenger->hasMessages()) {
            $this->view->flashmessage = $this->_helper->FlashMessenger->getMessages();
        }
    }
    
    public function indexAction()
    {
        $query = trim((string)$this->_request->getParam('q'));
        $page = $this->_request->getParam( 'page' , 1 );
        
        $html = '';
        $toSearch = FALSE;
        
        if(!$this->_request->isXmlHttpRequest()){
            $this->view->headLink()->appendStylesheet('/css/search.css');
            $this->view->jQuery()->addJavascriptFile('/js/n2s.search.js');
            $this->view->headTitle($query, 'PREPEND');
            
            $sFeld = new ZendX_JQuery_Form_Element_AutoComplete('multisearch',array('name' => 'multisearch','onfocus' => 'srch.press(this);','label' => 'Location','placeholder' => $this->view->translate('Search...')));
            $sFeld->setJQueryParams(array('minLength'=>2,'source'=>'/tags/terms',
                'select'=>new Zend_Json_Expr('function(event,ui){
                    window.location.replace("/search/index/q/"+ui.item.value);
                    }')));
            $sFeld->removeDecorator('label')
                    ->removeDecorator('HtmlTag')
                    ->setValue($query);
            
            $html .= '<div id="multiIDXSch">';
            $html .= $sFeld;
            $html .= '<a onclick="javascript:srch.goto(event);" href="javascript:void(0);" id="idxSearch">SEARCH</a>';
            $html .= '<div class="clear"></div></div>';
        }
        
        $path = APPLICATION_PATH . '/../search/index_multi';
        $index = Zend_Search_Lucene::open($path);
        
        Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(1);        
        $query = trim(preg_replace('/[^\ \p{L}]/u', ' ',$query));
        if($query && strlen($query) > 1){
            $results = $index->find(
                    $q = Zend_Search_Lucene_Search_QueryParser::parse(
                             $query.'*','utf-8'));
            $toSearch = TRUE;
        }
        
        if($query && isset($results) && count($results) > 0){
            $paginator = Zend_Paginator::factory($results);
            $paginator->setItemCountPerPage(10);
            $paginator->setCurrentPageNumber($page);
            $html .= $this->view->paginationControl($paginator, 'Sliding', '_partials/searchpagination-1.phtml');
            $html .= '<div id="idxCMain" class="left"><ul id="idxCont">';
            foreach ($paginator as $result) {
                $names = $result->getDocument()->getFieldNames();
                $html .= '<li>';
                $html .= '<h3><a class="black" href="'.$result->link.'">';
                $html .= $q->highlightMatches($result->title,'utf-8');
                $html .= '</a></h3>';

                if($result->art == 'SEARCH_user'){
                    $html .= '<div class="left idxTpN">';
                    $html .= $this->view->userThumb($result->itemID,1,0,FALSE);
                    $html .= '</div>';
                } else {
                    if (in_array('thumb', $names) && $result->thumb != NULL && file_exists($result->thumb)) {
                        $evimg = $result->thumb;

                        $html .= '<div class="left idxTpN">';
                        $html .= '<div class="n2s-thumb">';
                        $html .= '<a href="'.$result->link.'">';
                        $html .= '<img class="thumb-avatar" alt="" src="'.$evimg.'" width="64" height="64"/>';
                        $html .= '</a>';
                        $html .= '</div>';
                        $html .= '</div>';
                    }
                }
                
                $html .= '<div class="idxRgt">';
                $html .= '<div class="idxInf">';
                $html .= '<div class="evDtS">';
                $html .= '<span>'.$this->view->translate($result->art).'</span>';
                if(in_array('time', $names) && $result->time != NULL){
                    $html .= ' •<span class="INFO_date idxSSm idxTp">';
                    $Time = new Zend_Date($result->time);
                    $html .= $Time->get(Zend_Date::DATE_FULL);
                    $html .= '</span>';
                }
                if(in_array('local', $names) && $result->local != NULL){
                    $html .= ' •<span class="INFO_hometown idxSSm">';
                    $html .= $q->highlightMatches($result->local,'utf-8');
                    $html .= '</span>';
                }
                if(in_array('address', $names) && $result->address != NULL){
                    $html .= ' •<span class="INFO_map idxSSm">';
                    $html .= $this->view->addressSchemaHtml($result->address);
                    $html .= '</span>';
                }
                $html .= '</div>';
                $html .= '</div>';
                if(in_array('content', $names) && $result->content != NULL){
                    $html .= $q->highlightMatches($result->content,'utf-8');
                }
                $html .= '</div>';
                $html .= '<div class="clear"></div></li>';
            }
            $html .= '</ul></div>';
            $html .= '<div class="right" style="height:50px;width:400px;text-align:right;">'.$this->view->reklameChitika(2).'</div>';
            $html .= '<div class="clear"></div>';
            $html .= $this->view->paginationControl($paginator, 'Sliding', '_partials/searchpagination-2.phtml');
        } elseif ($toSearch == TRUE) {
            $html .= '<div id="resultStats">'.$this->view->translate('No search results').'</div>';
        }
        
        $this->view->html = $html;
    }
    
    private function delTree($dir)
    { 
        $files = array_diff(scandir($dir), array('.','..')); 

        foreach ($files as $file) { 
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file"); 
        }

        return rmdir($dir); 
    }

    public function ajaxAction()
    {
        if(!$this->_request->isXmlHttpRequest()) {
            $this->_forward('notfound', 'Error', 'default');
        } else {
            $path1 = APPLICATION_PATH . '/../search/index_user';
            $path2 = APPLICATION_PATH . '/../search/index_event';
            $path3 = APPLICATION_PATH . '/../search/index_album';
            $path4 = APPLICATION_PATH . '/../search/index_local';

            $index = new Zend_Search_Lucene_MultiSearcher();

            $index->addIndex(Zend_Search_Lucene::open($path1));
            $index->addIndex(Zend_Search_Lucene::open($path2));
            $index->addIndex(Zend_Search_Lucene::open($path3));
            $index->addIndex(Zend_Search_Lucene::open($path4));

            Zend_Search_Lucene::setResultSetLimit(3);
            Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(1);

            $query = trim(preg_replace('/[^\ \p{L}]/u', ' ',(string)$this->_request->getParam('q')));
            if($query && strlen($query) > 1){
                $results = $index->find(
                        $q = Zend_Search_Lucene_Search_QueryParser::parse(
                                 $query.'*','utf-8'));
            }
            $search_array = NULL;
            if($query && isset($results) && count($results) > 0){
                $html = '<div id="idxSChk">';
                foreach ($results as $result) {
                    $names = $result->getDocument()->getFieldNames();
                    if($search_array != $result->art){
                        $html .= '<div class="idxSTit">';
                        $html .= $this->view->translate($result->art).'</div>';
                        $search_array = $result->art;
                    }
                    $html .= '<div class="idxFn">';
                    $html .= '<div class="left">';

                    if($result->art == 'SEARCH_user'){
                        $html .= $this->view->userThumb($result->itemID,1,0,FALSE);
                    } else {
                        if (in_array('thumb', $names) && $result->thumb != NULL && file_exists($result->thumb)) {
                            $evimg = $result->thumb;
                        } else {
                            if($result->art == 'SEARCH_local'){
                                $evimg = 'images/marker.png';
                            } else {
                                $evimg = 'images/no-photo-thumb.png';
                            }
                        }
                        $html .= '<div class="n2s-thumb">';
                        $html .= '<a href="'.$result->link.'">';
                        $html .= '<img class="thumb-avatar" alt="" src="'.$evimg.'" width="64" height="64"/>';
                        $html .= '</a>';
                        $html .= '</div>';
                    }

                    $html .= '</div>';
                    $html .= '<div class="idxRgt"><a class="black" href="'.$result->link.'">';
                    $html .= $q->highlightMatches($result->title,'utf-8');
                    $html .= '</a>';
                    $html .= '<div class="idxInf">';
                    if(in_array('local', $names) && $result->local != NULL){
                        $html .= '<span class="INFO_hometown idxSSm">';
                        $html .= $q->highlightMatches($result->local,'utf-8');
                        $html .= '</span>';
                    }
                    if(in_array('time', $names)){
                        $html .= '<span class="INFO_date idxSSm idxTp">';
                        $Time = new Zend_Date($result->time);
                        $html .= $Time->get(Zend_Date::DATE_FULL);
                        $html .= '</span>';
                    }
                    if(in_array('address', $names)){
                        $html .= '<span class="INFO_map idxSSm">';
                        $html .= $this->view->addressSchemaHtml($result->address);
                        $html .= '</span>';
                    }
                    $html .= '</div></div></div>';
                }
                $html .= "</div>";
                $result = array('error'=>FALSE,'html'=>$html);
            } else {
                $result = array('error'=>true,'message'=>'no result');
            }
            if (isset($result))
                $this->_helper->json($result);
        }
    }
    
    //public function indexAction()
    //{
        /*
        $path = APPLICATION_PATH . '/../tmp/lucene';
        /*
        $index = Zend_Search_Lucene::create($path);
 
        $document = new Zend_Search_Lucene_Document();
        $document->addField(Zend_Search_Lucene_Field::Text('title', 'Titel 1 des Dokuments'));
        $document->addField(Zend_Search_Lucene_Field::Text('content', 'Hier ist ein toller Text'));
        $index->addDocument($document);

        $document = new Zend_Search_Lucene_Document();
        $document->addField(Zend_Search_Lucene_Field::Text('title', 'Das hier ist der zweite Titel'));
        $document->addField(Zend_Search_Lucene_Field::Text('content', 'Und hier steht der Inhalt eines Buches'));
        $index->addDocument($document);
         * 
         */
        /*
        Zend_Search_Lucene_Field::keyword('art', 'event');
        $index = Zend_Search_Lucene::open($path);
 
        $queries = array('TIT*','Buch', 'toller', 'ist', 'title:ist AND und', 'content:eines Buches', 'hier');

        $html = '';
        foreach ($queries as $query) {
            $results = $index->find(
                    Zend_Search_Lucene_Search_QueryParser::parse($query)
            );
            $html .= "<h3>Suche: " . $query . " ";
            $html .= "| Ergebnisse: ".count($results)."</h3><br/>";
            foreach ($results as $result) {
                $html .= '<div style="border-bottom: 1px solid;">';
                $html .= '<b>Inhalt:</b> ' . $result->content . "<br/>";
                $html .= '<b>Score:</b> ' . $result->score . "<br/>";
                $html .= '</div>';
            }
            $html .= "<br/>";
        }
        */
        //$url = 'http://www.yiiframework.com/wiki/248/adding-search-to-yii-blog-example-using-zend-lucene/';
        //$doc = Zend_Search_Lucene_Document_Html::loadHTMLFile($url);
        //$links = $doc->getLinks();
        //$links = $doc->getHtmlBody();
        /*
        $html = '';
        foreach ($links as $link){
            $html .= $link.'<br/>';
        }
         * 
         */
        //$this->view->html = $links;
    //}
}
