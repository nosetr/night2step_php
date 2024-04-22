<?php

class Default_ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }
        
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $params = $errors->request->getParams();
            $paramsString = "array (\n";
            foreach($params as $key=>$value) {
                $paramsString .= "'".$key."' => '".$value."'\n";
            }
            $paramsString .= ")";
            
            $log->log("ERROR = ".$this->view->message."\n"
                ."MESSAGE = ".$errors->exception->getMessage()."\n"
                ."STACK TRACE = \n".$errors->exception->getTraceAsString()."\n"
                ."REQUEST PARAMS = ".$paramsString, $priority);
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    public function noaccessAction()
    {
        $this->getResponse()->setHttpResponseCode(401);
        $this->view->html = '<h2>'.$this->view->translate('No Access!').'</h2>';        
    }

    public function notfoundAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
        $this->view->html = '<h2>'.$this->view->translate('Not Found').' 404</h2>';
    }
    
    public function removedAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
        $this->view->html = '<h2>'.$this->view->translate('The profile you requested was removed.').'</h2>';        
    }

    public function getLog()
    {
        /*
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
         * 
         */
        $log = Zend_Registry::get('logger');
        
        if(!$log){
            return false;
        }
        
        return $log;
    }


}

