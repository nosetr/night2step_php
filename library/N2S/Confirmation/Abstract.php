<?php

/**
 * Abstract.php
 * Description of Abstract
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 01.03.2013 11:43:19
 * 
 */
class N2S_Confirmation_Abstract extends Zend_Controller_Plugin_Abstract
{
    public function  preDispatch(Zend_Controller_Request_Abstract $request) {
        $confirm = (string)$request->getParam('newset_confirm');
        $task = (string)$request->getParam('conftask');
        if(isset($confirm) && isset($task))
        {
            $parts = explode( '_', $confirm );
            $code = @$parts[0];
            $userID = @$parts[1];
            if(isset($code) && isset($userID)){
                switch ($task){
                    case 'email':
                        $model = new Community_Model_UserEmailConfirm();
                        $new = $model->delConfirm($userID, $code);
                        if($new == TRUE)
                            $message = 'E-Mail was updated.';
                        break;
                    default :
                        break;
                }
                if(isset($message)){
                    $view = Zend_Layout::getMvcInstance()->getView();
                    $view->flashmessage = $view->translate($message);
                    /*$flashmessenger = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'FlashMessenger' );
                    $flashmessenger->addMessage ($message);
                     * 
                     */
                }
            }
        }
    }
}