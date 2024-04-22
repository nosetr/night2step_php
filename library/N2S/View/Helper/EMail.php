<?php

/**
 * EMail.php
 * Description of EMail
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 28.09.2012 12:58:57
 * 
 */
class N2S_View_Helper_EMail extends Zend_View_Helper_Abstract
{
    function eMail($userID,$subject,$string,$text = FALSE,$emailTo = FALSE)
    {
        $config = array('auth' => 'login',
            'ssl' => Zend_Registry::get('config')->mail->smtp->ssl,
            'username' => Zend_Registry::get('config')->mail->smtp->username,
            'password' => Zend_Registry::get('config')->mail->smtp->password,
            'port' => Zend_Registry::get('config')->mail->smtp->port);
        $transport = new Zend_Mail_Transport_Smtp(Zend_Registry::get('config')->mail->smtp->host,$config);
            //Zend_Mail::setDefaultTransport($tr);
        
        $html = '<table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:98%;margin-bottom:15px;">';
        $html .= '<tbody><tr><td style="font-size:12px;font-family:Helvetica,Arial,FreeSans,sans-serif;color: #333333;">';
        $html .= '<table cellspacing="0" cellpadding="0" style="width: 620px; border-collapse: collapse;"><tbody><tr>';
        $html .= '<td style="background-color: rgb(51, 51, 51); color: rgb(255, 255, 255); font-weight: bold; vertical-align: baseline; letter-spacing: -0.03em; text-align: left; padding: 5px;">';
        $html .= '<a href="'.$this->view->serverUrl().'" style="color: rgb(255, 255, 255); font-weight: bold; font-size: 18px; text-decoration: none; font-style: italic;">';
        $html .= '<img src="'.$this->view->serverUrl().'/images/n2s-logos/n2s_logo.png" alt="night2step.com" style="max-height: 20px; margin-bottom: -3px;"/>';
        $html .= '</a></td></tr></tbody></table>';
        $html .= $string;
        $html .= '</td></tr></tbody></table>';
        
        $profil = new Community_Model_Users();
        $user = $profil->getUser($userID);
            
        $mail = new Zend_Mail('UTF-8');
        if ($text == TRUE){
            $mail->setBodyText($string); // Send only text
        } else {
            $mail->setBodyHtml($html); // Send html
        }
        if($user->emailPref != NULL){
            $emailPref = 'notification+'.$user->emailPref;
        } else {
            $cryptModel = new Community_Model_Access();
            $emailPref = 'notification+'.$cryptModel->setEmailPref($user->email);
        }
        
        $mail->setFrom($emailPref.Zend_Registry::get('config')->mail->setting->from->email,
                Zend_Registry::get('config')->mail->setting->from->name);
        
        if($emailTo == FALSE)
            $emailTo = $user->email;
        
        if (getenv('APPLICATION_ENV') == 'production'){
            $mail->addTo($emailTo, $user->name);
        } else {
            $mail->addTo('osetrov.n@gmail.com', $user->name);
        }
        
        $mail->setSubject($subject);
        $mail->send($transport);
    }
}