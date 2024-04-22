<?php

/**
 * VenuesController.php
 * Description of VenuesController
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.10.2012 18:26:14
 * 
 */
class Default_VenuesController extends Zend_Controller_Action
{
    public function init()
    {
        if ($this->_helper->FlashMessenger->hasMessages()) {
            $this->view->flashmessage = $this->_helper->FlashMessenger->getMessages();
        }
    }
    
    public function indexAction()
    {
        $gSearch = $this->_request->getParam('geosearch',null);
        if(is_array($gSearch))
            (count($gSearch)>0)?$gSearch=$gSearch[count($gSearch)-1]:$gSearch=$gSearch[0];
        
        $session = new Zend_Session_Namespace('geopos');
        
        if ($gSearch == null){
            $latitude = $session->latitude;
            $longitude = $session->longitude;
            $radius = $session->radius;
            $minlatitude = $session->minlatitude;
            $minlongitude = $session->minlongitude;
            $maxlongitude = $session->maxlongitude;
            $maxlatitude = $session->maxlatitude;
        } else {
            $geoS = $this->_geoSearch($gSearch);
            
            $latitude = $geoS['latitude'];
            $longitude = $geoS['longitude'];
            $radius = $geoS['radius'];
            $minlatitude = $geoS['minlatitude'];
            $minlongitude = $geoS['minlongitude'];
            $maxlongitude = $geoS['maxlongitude'];
            $maxlatitude = $geoS['maxlatitude'];
        }
                
        if(!$this->_request->isXmlHttpRequest()) { //without json. Only html
            $this->view->jQuery()->addJavascriptFile('/js/n2s.list.js');
            if ($gSearch == null){
                $link = 'last/';
            } else {
                $link = 'geosearch/'.$gSearch.'/last/';
            }
            $html = $this->view->geoSearch('/venues/index/'.$link,$gSearch).'<div id=radius></div><br/>';
            
            $this->view->html = $html;
            $this->view->link = $link;
            
            $this->view->headTitle($this->view->translate('Venues'), 'PREPEND');
            
        } else { //Only json
          $first =  (bool)$this->_request->getParam('first',false);
          $minLon = (float)$this->_request->getParam('minlon');
          $maxLon = (float)$this->_request->getParam('maxlon');
          $minLat = (float)$this->_request->getParam('minlat');
          $maxLat = (float)$this->_request->getParam('maxlat');
          
          $ajaxMod = new Default_Model_Adresses();
          
          if($first == true)//Firs jquery request
          {
            $lat = $latitude;
            $lon = $longitude;
            $searchRadius = $radius;//km
            
            if($minlatitude && $minlongitude
                      && $maxlatitude && $maxlongitude){
                $minLon = $minlongitude;
                $maxLon = $maxlongitude;
                $minLat = $minlatitude;
                $maxLat = $maxlatitude;
            } else {
                $minLon = $lon - $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $maxLon = $lon + $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $minLat = $lat - ($searchRadius / 69);
                $maxLat = $lat + ($searchRadius / 69);
            }
            
            $noRegion = $this->view->translate('It\'s seems to be no venues in your region');
            $noEvents = $this->view->translate('We have no venues to show for you. Sorry!');
                
            begin:
                
            $eventsData = $ajaxMod->getList($minLat, $maxLat, $minLon, $maxLon);
            if(count($eventsData) == 0 && $searchRadius < 40077){//40.076.592 m = äquatorlänge
                @set_time_limit(60);//1 minute
                ($searchRadius==2)?$searchRadius=$searchRadius+18:$searchRadius=$searchRadius+20;
                
                $minLon = $lon - $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $maxLon = $lon + $searchRadius / abs(cos(deg2rad($lat)) * 69);
                $minLat = $lat - ($searchRadius / 69);
                $maxLat = $lat + ($searchRadius / 69);
                
                goto begin; // Such weiter
            } elseif (count($eventsData) > 0) {
                $html = '<input id="minLon" type="hidden" value="'.$minLon.'"/>';
                $html .= '<input id="maxLon" type="hidden" value="'.$maxLon.'"/>';
                $html .= '<input id="minLat" type="hidden" value="'.$minLat.'"/>';
                $html .= '<input id="maxLat" type="hidden" value="'.$maxLat.'"/>';
                if ($gSearch != null)
                    $html .= '<input id="geo" type="hidden" value="'.$gSearch.'"/>';
                                
                ($searchRadius > $radius)?
                    $message = '<div>'.$noRegion.'</div>':
                    $message = false;
                $result = array('error'=>false,'message'=>$message,'html'=>$html);
                $this->_helper->json($result);
            } else {
                die($this->_helper->json(array('error'=>true,'action'=>'stop','message'=>$noEvents)));
            }
          } else { // Second jquery request
              $last =  (int)$this->_request->getParam('last',null);
              $count =  (int)$this->_request->getParam('count',null);
              if($count != null){
                  $count = floor ($count/100 * 2);
                  if ($count % 2 != 0)//ist ungerade
                      $count = $count + 1;
              }
              $html = '<div style="margin-right: 20px;border-bottom: 1px dotted rgb(153, 153, 153);">';
                            
              $eventsData = $ajaxMod->getList($minLat, $maxLat, $minLon, $maxLon, $last, $count);
              
              if(count($eventsData) == 0){
                    die($this->_helper->json(array('error'=>true,'action'=>'stop')));
              } else {
                  $events = $eventsData;
              }
              
                    //Main HTML
                  $lineCount = 0;
                  $photos = new Default_Model_Photos();
                  $locale = Zend_Registry::get('Zend_Locale');
                  $mainDist = 0;
                    foreach ($events as $event){
                        $dist = $this->view->distance($event->latitude, $event->longitude, $latitude, $longitude);
                        if($mainDist < $dist)
                                $mainDist = $dist;
                        $dist = new Zend_Measure_Length(round($dist,1),Zend_Measure_Length::KILOMETER,$locale);
                        $lineCount++;
                        if($lineCount == 1)
                            $html .= '<div class="EvLine" style="border-top: 1px dotted rgb(153, 153, 153);">';
                        //Event Photo
                        $photo = $photos->getPhotoID($event->photoid);
                        if (isset($photo) && file_exists($photo->thumbnail)){
                            $evimg = $photo->thumbnail;
                        } else {
                            $evimg = '/images/no-photo-marker-thumb.png';
                        }//END Event Photo
                        
                        $link = $this->view->url(array('module'=>'default',
                                        'controller'=>'venues','action'=>'show','id'=>$event->id),
                                        'default', true);
                        $title = $event->name;
                        
                        $html .= '<div class="LBox" id="'.$event->id.'" style="float: left;';
                        if($lineCount == 1)
                            $html .= 'border-right: 1px dotted rgb(153, 153, 153);';
                        $html .= 'padding: 5px; width: 48%;">';
                        $html .= '<div  style="margin-right: 15px;width:100px;height:100px;float:left;">';
                        $html .= '<a href="'.$link.'">';
                        $html .= '<img  style="width:100px;height:100px;" src="'.$evimg.'" alt=""/>';
                        $html .= '</a>';
                        $html .= '</div>';
                        $html .= '<div style="margin-left:115px;"><h3><a class="black" href="'.$link.'">';
                        $html .= $this->view->shortText($title,200).'</a></h3>';
                        $html .= '<div>'.$event->address.'</div>';
                        $html .= '<div class="TSmal">'.$dist.'</div>';
                        $html .= '</div></div>';
                        if($lineCount == 2){
                            $html .= '<div class="clear"></div></div>';
                            $lineCount = 0;
                        }
                        //END Event INFO
                    }
                    if($lineCount == 1)
                        $html .= '<div class="clear"></div></div>';
                    $html .= '<div class="clear"></div>';
                }
                
                
                $mainDist = new Zend_Measure_Length(ceil($mainDist),Zend_Measure_Length::KILOMETER,$locale);
                $result = array('error'=>false,'html'=>$html,'radius'=>'<b>'.$this->view->translate('Search radius').':</b> '.$mainDist);
                $this->_helper->json($result);
            }
    }
    
    public function ajaxAction()
    {
        if (!$this->_request->isXmlHttpRequest())
            $this->_helper->redirector('notfound', 'Error', 'default');
        $auth = Zend_Auth::getInstance();
        if(!$auth->hasIdentity())
            die($this->_helper->json(array('error'=>true,'message'=>'Error')));
        $task = (string)$this->_request->getParam('task');
        $id =  (int)$this->_request->getParam('id',0);
        if($task == 'admin' && $id > 0){
            $adresses = new Default_Model_Adresses();
            $adress = $adresses->getAdress($id);
            if(!isset($adress) || $adress->creator > 0){
                die($this->_helper->json(array('error'=>true,'message'=>'Error')));
            } else {
                $confirms = new Default_Model_AdressesAdminConfirm();
                $confirm = $confirms->setConfirm($id);
                $message = '<div style=\"text-align:center;\"><b>'.$this->view->translate('Your request has been saved.').'</b>';
                $message .= '<br/>'.$this->view->translate('Within 2 weeks we send a letter to you.').'</div>';
                ($confirm == FALSE)?$result=array('error'=>true,'message'=>'Error'):$result=array('error'=>FALSE,'message'=>$message);
                $this->_helper->json($result);
            }
        } elseif($task == 'addadminreq' && $id > 0){
            $user =  (int)$this->_request->getParam('user',0);
            $type =  (string)$this->_request->getParam('type');
            $rqs = new Default_Model_AdressesAdminRequest();
            $r = $rqs->setRequest($user, $id, $type);
            if($r == true){
                $message = '<div><span>'.$this->view->translate('Admin request was sended').'</span>';
                $message .= '<span><a href="javascript:void(0);" onclick="javascript:vens.addadminreq('.$user.','.$id.',\''.$type.'\',\'removeadminreq\');"><img id="resetLocButton" title="'.$this->view->translate('Click here to reset').'" class="n2s-tooltip" src="/images/reset.png" alt=""/></a></span>';
                $message .= '</div>';
                $result=array('error'=>FALSE,'message'=>$message);
            } else {
                $result=array('error'=>TRUE,'message'=>$this->view->translate('Error'));
            }
            $this->_helper->json($result);
        } elseif($task == 'removeadminreq' && $id > 0){
            $user =  (int)$this->_request->getParam('user',0);
            $type =  (string)$this->_request->getParam('type');
            $rqs = new Default_Model_AdressesAdminRequest();
            $r = $rqs->delRequest($user, $id, $type);
            if($r == true){
                $message = '<a href="javascript:void(0);" ';
                $message .= 'onclick="javascript:vens.addadminreq('.$user.','.$id.',\''.$type.'\',\'addadminreq\');"';
                $message .= ' class="ajaxlink left" style="margin-bottom:5px;padding:0px 20px;">'.$this->view->translate('Set as admin').'</a>';
                $result=array('error'=>FALSE,'message'=>$message);
            } else {
                $result=array('error'=>TRUE,'message'=>$this->view->translate('Error'));
            }
            $this->_helper->json($result);
        } elseif($task == 'accadminreq' && $id > 0){
            $user =  (int)$this->_request->getParam('user',0);
            $type =  (string)$this->_request->getParam('type');
            $rqs = new Default_Model_AdressesAdminRequest();
            $r = $rqs->acceptRequest($user, $id, $type);
            if($r == true){
                if($auth->getIdentity()->type != 'profil' ){
                    Zend_Auth::getInstance()->clearIdentity();
                    Zend_Session::forgetMe();
                    $changedSess = Zend_Registry::get('config')->authsession->changed->key;
                    Zend_Session::namespaceUnset($changedSess);
                }
                $result=array('error'=>FALSE,'message'=>'success');
            } else {
                $result=array('error'=>TRUE,'message'=>$this->view->translate('Error'));
            }
            $this->_helper->json($result);
        } elseif($task == 'remselfadmin' && $id > 0){
            $user =  (int)$this->_request->getParam('user',0);
            $type =  (string)$this->_request->getParam('type');
            $rqs = new Community_Model_Admins();
            $r = $rqs->delAdmin($user, $id, $type);
            if($r == true){
                if($auth->getIdentity()->userid == $id ){
                    Zend_Auth::getInstance()->clearIdentity();
                    Zend_Session::forgetMe();
                    $changedSess = Zend_Registry::get('config')->authsession->changed->key;
                    Zend_Session::namespaceUnset($changedSess);
                }
                $result=array('error'=>FALSE,'message'=>'success');
            } else {
                $result=array('error'=>TRUE,'message'=>$this->view->translate('Error'));
            }
            $this->_helper->json($result);
        } elseif($task == 'removeaddress' && $id > 0){
            $adresses = new Default_Model_Adresses();
            $adress = $adresses->getAdress($id);
            if(!isset($adress) || $adress->creator == 0){
                die($this->_helper->json(array('error'=>true,'message'=>'Error')));
            } else {
                $checkAdmin = new Community_Model_Admins();
                $curuser = $checkAdmin->getCuruser($adress->creator, 'venue');
                if($curuser != $adress->creator){
                    die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                } else {
                    $confirms = new Default_Model_AdressesAddressConfirm();
                    $confirm = $confirms->delConfirm($curuser,$id);
                    $message = '<div style=\"text-align:center;\"><b>'.$this->view->translate('Your request was deleted.').'</b></div>';
                    $result=array('error'=>FALSE,'message'=>$message);
                    $this->_helper->json($result);
                }
            }
        } elseif($task == 'checkaddress' && $id > 0){
            $adresses = new Default_Model_Adresses();
            $adress = $adresses->getAdress($id);
            if(!isset($adress) || $adress->creator == 0){
                die($this->_helper->json(array('error'=>true,'validator'=>false,'message'=>'Error')));
            } else {
                $checkAdmin = new Community_Model_Admins();
                $curuser = $checkAdmin->getCuruser($adress->creator, 'venue');
                if($curuser != $adress->creator){
                    die($this->_helper->json(array('error'=>true,'validator'=>false,'message'=>'Error')));
                } else {
                    $newAd = (string)$this->_request->getParam('new');
                    $geoloc = N2S_GeoCode_GoogleGeocode::googleGeocode($newAd);
                    if(is_array($geoloc) && count($geoloc) > 0 &&
                            isset($geoloc['route']) &&
                            isset($geoloc['formatted_address'])){
                        $formAdress = $geoloc['formatted_address'];
                        
                        if(isset($geoloc['short_country'])){
                            $country = $geoloc['short_country'];
                            $rtz = Zend_Locale::getLocaleToTerritory($country);
                            $locale = new Zend_Locale($rtz);
                            $rtz = $locale->getLanguage();
                            $geoloc = N2S_GeoCode_GoogleGeocode::googleGeocode($newAd,$rtz);
                            $formAdress = $geoloc['formatted_address'];
                        }
                    }
                    if(!isset($formAdress)){
                        $result = array('error'=>true,'validator'=>true,'message'=>$this->view->translate('Address is not valid'));
                    } elseif(isset($formAdress) && $formAdress == $adress->address){
                        $result = array('error'=>true,'validator'=>true,'message'=>$this->view->translate('Address is the same as currently'));
                    } else {
                        $result = array('error'=>false,'validator'=>false,'message'=>$formAdress);
                    }
                    $this->_helper->json($result);
                }
            }
        } elseif($task == 'address' && $id > 0){
            $adresses = new Default_Model_Adresses();
            $adress = $adresses->getAdress($id);
            if(!isset($adress) || $adress->creator == 0){
                die($this->_helper->json(array('error'=>true,'message'=>'Error')));
            } else {
                $checkAdmin = new Community_Model_Admins();
                $curuser = $checkAdmin->getCuruser($adress->creator, 'venue');
                if($curuser != $adress->creator){
                    die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                } else {
                    $newAd = (string)$this->_request->getParam('new');
                    $geoloc = N2S_GeoCode_GoogleGeocode::googleGeocode($newAd);
                    
                    if(isset($geoloc['route ']) &&
                            isset($geoloc['formatted_address']))
                        $formAdress = $geoloc['formatted_address'];
                    
                    if(!isset($formAdress)){
                        $result = array('error'=>true,'message'=>$this->view->translate('Address is not valid'));
                    } elseif(isset($formAdress) && $formAdress == $adress->address){
                        $result = array('error'=>true,'message'=>$this->view->translate('Address is the same as currently'));
                    } else {
                        $country = NULL;
                        if(isset($geoloc['short_country'])){
                            $country = $geoloc['short_country'];
                            $rtz = Zend_Locale::getLocaleToTerritory($country);
                            $locale = new Zend_Locale($rtz);
                            $rtz = $locale->getLanguage();
                            $geoloc = N2S_GeoCode_GoogleGeocode::googleGeocode($newAd,$rtz);
                            $formAdress = $geoloc['formatted_address'];
                        }
                        $confirms = new Default_Model_AdressesAddressConfirm();
                        $confirm = $confirms->setConfirm($curuser,$id,$formAdress,$country);
                        $message = '<div style=\"text-align:center;\"><b>'.$this->view->translate('Your new address has been saved.').'</b>';
                        $message .= '<br/>'.$this->view->translate('Within 2 weeks we send a letter to you.').'</div>';
                        ($confirm == FALSE)?$result=array('error'=>true,'message'=>'Error'):$result=array('error'=>FALSE,'message'=>$message);
                    }
                    $this->_helper->json($result);
                }
            }
        } elseif($task == 'confirmaddress' && $id > 0){
            $code = (string)$this->_request->getParam('code');
            $adresses = new Default_Model_Adresses();
            $adress = $adresses->getAdress($id);
            if(!isset($adress) || $adress->creator == 0){
                die($this->_helper->json(array('error'=>true,'message'=>'Error')));
            } else {
                $checkAdmin = new Community_Model_Admins();
                $curuser = $checkAdmin->getCuruser($adress->creator, 'venue');
                if($curuser != $adress->creator){
                    die($this->_helper->json(array('error'=>true,'message'=>'Error')));
                } else {
                    $confirms = new Default_Model_AdressesAddressConfirm();
                    $confirm = $confirms->checkConfirm($curuser,$id, $code);
                    if(isset($confirm)){
                        $message = '<b>'.$this->view->translate('Gratulation! Du hast die Adresse erfolgreich bestätigt!').'</b>';
                        $result = array('error'=>FALSE,'message'=>$message);
                    } else {
                        $result = array('error'=>true,'message'=>'wrong');
                    }
                    $this->_helper->json($result);
                }
            }
        } elseif($task == 'confirm' && $id > 0){
            $code = (string)$this->_request->getParam('code');
            $adresses = new Default_Model_Adresses();
            $adress = $adresses->getAdress($id);
            if(!isset($adress) || $adress->creator > 0){
                die($this->_helper->json(array('error'=>true,'message'=>'Error')));
            } else {
                $confirms = new Default_Model_AdressesAdminConfirm();
                $confirm = $confirms->checkConfirm($id, $code);
                if(count($confirm)>0){
                    $message = '<b>'.$this->view->translate('Gratulation! Du hast dich erfolgreich als Admin bestätigt!').'</b>';
                    $result = array('error'=>FALSE,'message'=>$message);
                } else {
                    $result = array('error'=>true,'message'=>'wrong');
                }
                $this->_helper->json($result);
            }
        } else {
            die($this->_helper->json(array('error'=>true,'message'=>'Error')));
        }
    }

    public function addressAction()
    {
        $auth = Zend_Auth::getInstance();
            
        if (!$this->_request->isXmlHttpRequest() || !$auth->hasIdentity())
            $this->_helper->redirector('notfound', 'Error', 'default');
        $this->_helper->layout()->disableLayout();
        $id =  (int)$this->_request->getParam('id',0);
        $adresses = new Default_Model_Adresses();
        $adress = $adresses->getAdress($id);
        $onclick = '';
        
        if(!isset($adress))
            $this->_helper->redirector('notfound', 'Error', 'default');
        
        $checkAdmin = new Community_Model_Admins();
        $curuser = $checkAdmin->getCuruser($adress->creator, 'venue');
        if($adress->creator == 0 || $curuser != $adress->creator){
            $html = '<div style="text-align:center;padding:20% 0;"><b>';
            $html .= $this->view->translate('You have no access to edit this venue!');
            $html .= '</b></div>';
        } else {
            $send = 0;
            $newSend = 0;
            $confirms = new Default_Model_AdressesAddressConfirm();
            $confirm = $confirms->getConfirm($curuser, $id);
            if(count($confirm) > 0){
                $sendDate = 0;
                foreach($confirm as $i){
                    if($i->send == 1){
                        $send = 1;
                        if($sendDate == 0 || $i->senddate > $sendDate)
                            $sendDate = $i->senddate;
                    } else {
                        $newSend = 1;
                    }
                }
            }
            $html = '<div id="n2s-mForm"';
            if($send == 0){
                if($newSend == 1){
                    $html .= ' style=" margin-top: 10%;text-align:center;">';
                    $html .= '<b>'.$this->view->translate('Dein Auftrag für Adresseänderung ist in Bearbeitung.').'</b><br/>';
                    $html .= '<button id="im_requestcancel_button" style="margin: 10px;font-size: inherit; font-weight: bold; padding: 2px 10px;">';
                    $html .= $this->view->translate('Auftrag abrechen');
                    $html .= '</button>';
                } else {
                    $html .= ' style="text-align:center;">';
                    $html .= '<b>'.$this->view->translate('Die, von dir angegebene Adresse benötigt eine Bestätigung.').'</b><br/>';
                    $html .= $this->view->translate('Nach dem du den button unten klickst, werden wir eine Bestättigungscode an die neue Adresse vom Local per Post senden').'<br/>';
                    $html .= $this->view->translate('Sobald du den Post von uns erhälst, muss du den Code auf diese Seite eingeben.');
                    $html .= '<div><input id="newad" type="text" value="" placeholder="'.$this->view->translate('New address ...').'" name="albname"/></div>';
                    $html .= '<div style="margin-top: 20px;"><button id="im_check_button" style="font-size: inherit; font-weight: bold; padding: 2px 10px;">';
                    $html .= $this->view->translate('send');
                    $html .= '</button></div>';

                    $html .= '</div><div id="n2s-aForm" style="display:none;text-align:left;">';
                    $html .= '<b>'.$this->view->translate('New formated address').' :</b><br/>';
                    $html .= '<div id="nFormAd"></div>';
                    $html .= '<div style="text-align:center;margin-top: 20px;"><button id="im_send_button" style="font-size: inherit; font-weight: bold; padding: 2px 10px;">';
                    $html .= $this->view->translate('Send confirmation code');
                    $html .= '</button><button id="im_cancel_button" style="margin-left: 10px;font-size: inherit; font-weight: bold; padding: 2px 10px;">';
                    $html .= $this->view->translate('Reset');
                    $html .= '</button></div>';
                }
            } else {
                $html .= '">';
                $sendDay = new Zend_Date($sendDate);
                $html .= '<b>'.sprintf($this->view->translate('Ein Confirmcode wurde am %s an dich per post gesendet'), $sendDay->get(Zend_Date::DATE_FULL)).'</b>';
                $html .= '<dl class="n2s_form">';
                $html .= '<dt id="albname-label">';
                $html .= '<label class="required" for="albname">';
                $html .= $this->view->translate('Confirmcode eingeben:');
                $html .= '</label>';
                $html .= '</dt>';
                $html .= '<dd id="albname-element">';
                $html .= '<input id="albname" placeholder="######" style="font-size: 54px; width: 85%;" type="text" name="albname"/>';
                $html .= '</dd>';
                $html .= '</dl>';
                $html .= '<div style="margin-top: 20px;text-align: center;"><button id="im_confirm_button" style="font-size: inherit; font-weight: bold; padding: 2px 10px;">';
                $html .= $this->view->translate('Send');
                $html .= '</button>';
                if($newSend == 0){
                    $html .= '<button id="im_requestcancel_button" style="margin-left: 10px;font-size: inherit; font-weight: bold; padding: 2px 10px;">';
                    $html .= $this->view->translate('Auftrag abrechen');
                    $html .= '</button>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
            $success = '<b>'.$this->view->translate('Your new address has been saved.').'</b>';
            $error = '<ul id=\"errorMsgConfGtt\" class=\"errors\"><li>';
            $error .= $this->view->translate('The code you have put is incorect');
            $error .= '</li></ul>';
            $onclick = '$(function () {';
            if($send == 1)
                $onclick .= '$("#im_confirm_button").click(function(){var txt = $("#albname").val();if(txt==""){$("#albname").focus();}else{$("#errorMsgConfGtt").remove();$("#ajaxload").show();$("#n2s-mForm").hide();$.getJSON("/venues/ajax/task/confirmaddress",{id:'.$id.',code:txt},function(data){if(data){$("#ajaxload").hide();if(data.error){if(data.message=="wrong"){$("#albname").val("").after("'.$error.'");$("#n2s-mForm").show();}else{$("#n2s-mForm").empty().show().append(data.message);setTimeout(function(){parent.$.n2lbox.close();},3000);}}else{$("#n2s-mForm").empty().show().append(data.message);setTimeout(function(){window.location.reload();},3000);}}});}});';
            $onclick .= '$("#im_cancel_button").click(function(){$("#newad").val("");$("ul.errors").remove();$("#n2s-aForm").hide();$("#n2s-mForm").show();});';
            $onclick .= '$("#im_requestcancel_button").click(function(){$("#ajaxload").show();$("#n2s-mForm").hide();$.getJSON("/venues/ajax/task/removeaddress",{id:'.$id.'},function(data){if(data){$("#ajaxload").hide();$("#n2s-mForm").empty().append(data.message).show();setTimeout(function(){parent.$.n2lbox.close();},3000);}});});';
            $onclick .= '$("#im_check_button").click(function(){var newad = $("#newad").val();if(newad==""){$("#newad").focus();}else{$("ul.errors").remove();$("#ajaxload").show();$("#n2s-mForm").hide();$.getJSON("/venues/ajax/task/checkaddress",{id:'.$id.',new:newad},function(data){if(data){$("#ajaxload").hide();if(!data.error){$("#nFormAd").empty().append(data.message);$("#n2s-aForm").show();}else{if(data.validator){$("#newad").after("<ul class=\"errors\"><li>"+data.message+"</li></ul>");}else{$("#n2s-mForm").empty().append(data.message);}$("#n2s-mForm").show();}}});}});';
            $onclick .= '$("#im_send_button").click(function(){var newad = $("#newad").val();if(newad != ""){$("#ajaxload").show();$("#n2s-aForm").hide();$.getJSON("/venues/ajax/task/address",{id:'.$id.',new:newad},function(data){if(data){$("#ajaxload").hide();$("#n2s-mForm").empty().show().append(data.message);if(!data.error)$("#venButt").empty().append("'.$success.'");setTimeout(function(){parent.$.n2lbox.close();},3000);}});}});});';
        }
        $this->view->onclick = $onclick;
        $this->view->html = $html;
    }

    public function adminAction()
    {
        $auth = Zend_Auth::getInstance();
            
        if (!$this->_request->isXmlHttpRequest() || !$auth->hasIdentity())
            $this->_helper->redirector('notfound', 'Error', 'default');
        $this->_helper->layout()->disableLayout();
        $id =  (int)$this->_request->getParam('id',0);
        $adresses = new Default_Model_Adresses();
        $adress = $adresses->getAdress($id);
        $onclick = '';
        
        if(!isset($adress))
            $this->_helper->redirector('notfound', 'Error', 'default');
        if($adress->creator > 0){
            $html = '<div style="text-align:center;padding:20% 0;"><b>';
            $html .= $this->view->translate('This venue has still an admin. You have no access to register as admin.');
            $html .= '</b></div>';
        } else {
            $curuser = N2S_User::curuser();

            $send = 0;
            $newSend = 0;
            $confirms = new Default_Model_AdressesAdminConfirm();
            $confirm = $confirms->getConfirm($curuser, $id);
            if(count($confirm) > 0){
                $sendDate = 0;
                foreach($confirm as $i){
                    if($i->send == 1){
                        $send = 1;
                        if($sendDate == 0 || $i->senddate > $sendDate)
                            $sendDate = $i->senddate;
                    } else {
                        $newSend = 1;
                    }
                }
            }
            $html = '<div id="n2s-mForm"';
            if($send == 0){
                $html .= ' style="text-align:center;">';
                $html .= '<b>'.$this->view->translate('Wenn du ein Betreiber dieses Local bist, hast du die möglichkeit sich als Admin zu registrieren.').'</b><br/>';
                $html .= $this->view->translate('Nach dem du den button unten klickst, werden wir eine Bestättigungscode an die Adresse vom Local per Post senden').'<br/>';
                $html .= $this->view->translate('Sobald du den Post von uns erhälst, muss du den Code auf diese Seite eingeben.');
                $html .= '<div style="margin-top: 20px;"><button id="im_send_button" style="font-size: inherit; font-weight: bold; padding: 2px 10px;">';
                $html .= $this->view->translate('Send confirmation code');
                $html .= '</button></div>';
            } else {
                $html .= '">';
                $sendDay = new Zend_Date($sendDate);
                $html .= '<b>'.sprintf($this->view->translate('Ein Confirmcode wurde am %s an dich per post gesendet'), $sendDay->get(Zend_Date::DATE_FULL)).'</b>';
                $html .= '<dl class="n2s_form">';
                $html .= '<dt id="albname-label">';
                $html .= '<label class="required" for="albname">';
                $html .= $this->view->translate('Confirmcode eingeben:');
                $html .= '</label>';
                $html .= '</dt>';
                $html .= '<dd id="albname-element">';
                $html .= '<input id="albname" placeholder="######" style="font-size: 54px; width: 85%;" type="text" name="albname"/>';
                $html .= '</dd>';
                $html .= '</dl>';
                $html .= '<div style="margin-top: 20px;text-align: center;"><button id="im_confirm_button" style="font-size: inherit; font-weight: bold; padding: 2px 10px;">';
                $html .= $this->view->translate('Send');
                $html .= '</button>';
                if($newSend == 0){
                    $html .= '<button id="im_send_button" style="margin-left: 10px;font-size: inherit; font-weight: bold; padding: 2px 10px;">';
                    $html .= $this->view->translate('Confirmation code erneut senden');
                    $html .= '</button>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
            $success = '<b>'.$this->view->translate('Your request as administrator has been saved.').'</b>';
            $error = '<ul id=\"errorMsgConfGtt\" class=\"errors\"><li>';
            $error .= $this->view->translate('The code you have put is incorect');
            $error .= '</li></ul>';
            $onclick = '$(function () {';
            if($send == 1)
                $onclick .= '$("#im_confirm_button").click(function(){var txt = $("#albname").val();if(txt==""){$("#albname").focus();}else{$("#errorMsgConfGtt").remove();$("#ajaxload").show();$("#n2s-mForm").hide();$.getJSON("/venues/ajax/task/confirm",{id:'.$id.',code:txt},function(data){if(data){$("#ajaxload").hide();if(data.error){if(data.message=="wrong"){$("#albname").val("").after("'.$error.'");$("#n2s-mForm").show();}else{$("#n2s-mForm").empty().show().append(data.message);setTimeout(function(){parent.$.n2lbox.close();},3000);}}else{$("#n2s-mForm").empty().show().append(data.message);setTimeout(function(){window.location.reload();},3000);}}});}});';
            $onclick .= '$("#im_send_button").click(function(){$("#ajaxload").show();$("#n2s-mForm").hide();$.getJSON("/venues/ajax/task/admin",{id:'.$id.'},function(data){if(data){$("#ajaxload").hide();$("#n2s-mForm").empty().show().append(data.message);if(!data.error)$("#venButt").empty().append("'.$success.'");setTimeout(function(){parent.$.n2lbox.close();},3000);}});});});';
        }
        $this->view->onclick = $onclick;
        $this->view->html = $html;
    }

    public function showAction()
    {
        $panelOwner = false;
        $this->view->jQuery()->addJavascriptFile('/js/n2s.venues.js');
        $id =  (int)$this->_request->getParam('id',0);
        $public =  $this->_request->getParam('pub',FALSE);
        $auth = Zend_Auth::getInstance();
        $adresses = new Default_Model_Adresses();
        $adress = $adresses->getAdress($id);
        
        if(!isset($adress))
            $this->_helper->redirector('notfound', 'Error', 'default');
        
        $showAdminPanel = FALSE;
        if ($auth->hasIdentity()) {
            if($public == FALSE && $auth->getIdentity()->userid == $adress->creator)
                $panelOwner = TRUE;
            $checkAdmin = new Community_Model_Admins();
            $curUserID = $checkAdmin->getCuruser($adress->creator, 'venue');
            if($adress->creator > 0 && $curUserID == $adress->creator)
                $showAdminPanel = TRUE;
            if($public == TRUE && $this->_request->isXmlHttpRequest()){
                $curuser = 0;
            } else {                
                $curuser = $curUserID;
            }
        } else {
            $curuser = 0;
        }
        if($adress->creator > 0){
            ($curuser == $adress->creator)?$owner = TRUE:$owner = FALSE;
        } else {
            $owner = FALSE;
        }
        
        if($showAdminPanel == FALSE){
            $hits = $adress->hits + 1;
            $data = array('hits'=>$hits);
            $adresses->updateAdress($id, $data);
        }
        
        $this->view->reqadmin = '';
        if($owner == FALSE && $curuser > 0){
            $adminReqs = new Default_Model_AdressesAdminRequest();
            $checkAdminReq = $adminReqs->getRequest($curuser, $adress->creator);
            if(isset($checkAdminReq)){
                $users = new Community_Model_Users();
                $inviter = $users->getUser($checkAdminReq->request_from);
                $reqhtml = '<div id="reqadhtmlbut">';
                $reqhtml .= '<div class="viewNotButton" style="cursor:default;">';
                $reqhtml .= '<div style="text-align:center;">';
                $reqhtml .= $inviter->name;
                $reqhtml .= ' '.$this->view->translate('invites you to become a manager of this page').'. ';
                $reqhtml .= '<a style="color:rgb(255,255,255);text-decoration:underline;" href="javascript:void(0);" ';
                $reqhtml .= 'onclick="javascript:vens.accadminreq('.$curuser.','.$adress->creator.',\'venue\');">';
                $reqhtml .= $this->view->translate('Accept the invitation');
                $reqhtml .= '</a>&nbsp;&nbsp;&nbsp;';
                $reqhtml .= '<a style="color:rgb(255,255,255);text-decoration:underline;" href="javascript:void(0);" ';
                $reqhtml .= 'onclick="javascript:vens.addadminreq('.$curuser.','.$adress->creator.',\'venue\',\'removeadminreq\');">';
                $reqhtml .= $this->view->translate('Decline');
                $reqhtml .= '</a>';
                $reqhtml .= '</div>';
                $reqhtml .= '</div>';
                $reqhtml .= '</div>';
                $this->view->reqadmin = $reqhtml;
            }
        }
        
        if(!$adress || $id == 0)
            $this->_helper->redirector('notfound', 'Error', 'default');
        
        $pos = $adress->address;
        
        $this->view->mTitle = '';
        if($auth->hasIdentity()){
            $this->view->mTitle = '<div id="venButt" style="float:right;width:240px;text-align:center;">';
            if(0 == $adress->creator){
                $checkAdminReq = new Default_Model_AdressesAdminConfirm();
                $isReq = $checkAdminReq->getConfirm($curuser, $id);
                if(count($isReq) > 0){
                    $send = 0;
                    foreach($isReq as $i){
                        if($i->send == 1)
                            $send = 1;
                    }
                    if($send == 1){
                        $this->view->mTitle .= '<a class="red n2s-message n2lbox.ajax" href="';
                        $this->view->mTitle .= $this->view->url(array('module'=>'default','controller'=>'venues','action'=>'admin','id'=>$id),'default', true);
                        $this->view->mTitle .= '" id="archiveLink" style="margin:3px 0;">';
                        $this->view->mTitle .= $this->view->translate('Input confirmcode').'</a>';
                    } else {
                        $this->view->mTitle .= '<b>'.$this->view->translate('Your request as administrator is processing.').'</b>';
                    }
                }else{
                    $this->view->mTitle .= '<a class="red n2s-message n2lbox.ajax" href="';
                    $this->view->mTitle .= $this->view->url(
                            array(
                                'module'=>'default',
                                'controller'=>'venues',
                                'action'=>'admin',
                                'id'=>$id),
                            'default', true);
                    $this->view->mTitle .= '" id="archiveLink" style="margin:3px 0;">';
                    $this->view->mTitle .= $this->view->translate('Register as admin').'</a>';
                }
            }
            $this->view->mTitle .= '</div>';
        }
        $this->view->mTitle .= '<h1 style="width:740px;margin:0px;min-height:45px;line-height:40px;">'.$adress->name;
        $this->view->mTitle .= '</h1>';
        $backGrBanner = $this->view->userBanner('venue',$id,$owner);
        if($backGrBanner['map']==FALSE){
            if($this->_request->isXmlHttpRequest()){
                $map = '<iframe class="n2lbox-iframe" scrolling="no" frameborder="0" src="/events/map/static/1/id/'.$id.'" hspace="0"></iframe>';
            } else {
                $map = $this->view->simpleMaps($id);
            }
        } else { $map = ''; }
        $this->view->userbanner = $backGrBanner['html'];
        
        $panel = $this->view->showPanel($adress->creator,'venue',$panelOwner,$adress->id);
        $bgPos = $panel['width']+30;
        
        $html = '<div style="background: url(/images/bg-line2.png) repeat-y scroll '.$bgPos.'px 0px rgb(250, 250, 250);">';
        $html .= '<div style="width:'.$panel['width'].'px;margin:0 5px;float:left;padding:10px;"><ul style="margin: 0px;">';
        $html .= '<li><span style="background: url(/images/auge.png) no-repeat scroll 3px 4px transparent;color: #999999;cursor: default;font-weight: bold;padding: 0 5px 0 24px;">';
        $html .= $adress->hits;
        $html .= '</span></li>';
        
        $html .= '<li><b>'.$this->view->translate('Address').': </b><br/>'.$pos;
        if($auth->hasIdentity() && $curuser == $adress->creator){
            $adressURL = $this->view->url(array('module'=>'default',
                            'controller'=>'venues','action'=>'address','id'=>$adress->id),'default', true);
            $html .= '<div><a class="red n2s-message n2lbox.ajax left ajaxlink" href="'.$adressURL.'" style="padding:0px 15px;margin-top:5px;">'.$this->view->translate('Edit address').'</a></div>';
        }
        $html .= '<div class="clear"></div></li>';
        
        if($adress->creator > 0 && $auth->hasIdentity() && $auth->getIdentity()->userid != $adress->creator && $curuser > 0){
            $html .= '<li style="margin-top: 5px;">';
            $html .= '<a class="red n2s-message n2lbox.ajax ajaxlink left" href="/message/send/uid/'.$adress->creator.'"';
            $html .= ' style="padding: 2px 10px 2px 25px; background: url(/images/mail.png) no-repeat scroll 7px 3px rgb(255, 255, 255);">';
            if($showAdminPanel == FALSE){
                $html .= $this->view->translate('send a message');
            } else {
                $html .= $this->view->translate('message to all admins');
            }
            $html .= '</a><div class="clear"></div></li>';
        }
        $html .= '</ul></div>';
        $html .= $panel['html'];
        $html .= '<div class="clear"></div></div>';
        $html .= '<div class="clear"></div><div id="mapTest" style="margin: 15px 0;">'.$map.'</div>';
                
        if($adress->description)
            $html .= '<div style="margin:0 5px;"><b>'.$this->view->translate('Description').':</b><br/>'.$this->view->urlReplace($this->view->escape($adress->description),TRUE).'</div>';
        $html .= $this->view->socButtons();
        $html .= '<input type="hidden" name="active-show" value="venue"/>';
        $html .= '<input type="hidden" name="active-id" value="'.$id.'"/>';
        
        $this->view->headTitle($adress->name.' - '.$pos, 'PREPEND');
        $this->view->html = $html;
        $this->view->jQuery()->addJavascriptFile('/js/n2s.comment.js');
        $this->view->comment = $this->view->comments($id,'venues',3);
        
        
        $photos = new Default_Model_Photos();
        $photo = $photos->getPhotoID($adress->photoid);
        if ($photo && file_exists($photo->image)){
            $evimg = $photo->image;
            $thumbexist = 1;
            $permis = Community_Model_Permissions::getPermissions($photo->creator);
        } else {
            $evimg = $this->view->baseUrl().'images/no-photo-marker.png';
            $thumbexist = 0;
        }
        
        if($thumbexist > 0 && $photo->permissions > $permis)
            $thumbexist = 0;
        
        $thumb = '<div id="n2s-useravatar" style="z-index:2;box-shadow:-1px 2px 3px rgba(0, 0, 0, 0.3);border: 1px solid rgb(153, 153, 153); width: 238px; margin-bottom: 10px; min-height: 225px; background-color: rgb(255, 255, 255);">';
        $thumb .= '<div id="avSn2s">';
        if($thumbexist > 0){
            $thumb .= '<a class="n2s-phBox n2lbox.ajax" href="';
            $thumb .= $this->view->url(array('module'=>'default','controller'=>'photo','action'=>'view','id'=>$adress->photoid),'default', true);
            $thumb .= '">';
        }
        $thumb .= '<img id="n2simg-avat_surround" ';
        $thumb .= 'src="'.$evimg.'" alt=""/>';
        if($thumbexist > 0)
            $thumb .= '</a>';
        $thumb .= '</div>';
        if($auth->hasIdentity() && $curuser == $adress->creator){
            $thumb .= '<a class="n2s-transpb n2s-imgup n2lbox.ajax" style="text-align: center; padding: 10px 0px; top: 1px;" href="';
            $thumb .= $this->view->url(array(
                'module'=>'default','controller'=>'ajax','action'=>'imgup',
                'task'=>'avatarchange','target'=>'avatar'),'default', true);
            $thumb .= '">'.$this->view->translate('Change avatar').'</a>';
        }
        $thumb .= '</div>';
        $this->view->thumb = $thumb;
        
        $pubStand = '';
        $pubview = '';
        if($showAdminPanel == TRUE){
            if($public == TRUE && $this->_request->isXmlHttpRequest()){
                $pubview = '<div onclick="javascript:n2s.publicview();" class="viewNotButton"><h3 style="text-align:center;">';
                $pubview .= $this->view->translate('Close public view').'</h3>';
                $pubview .= '</div>';
                $pubStand = 'var pubview = 0;';
            } else {
                $pubview = '<a class="ajaxlink adpan-top left" onclick="javascript:n2s.publicview();" href="javascript:void(0);">'.$this->view->translate('public view').'</a>';
                $pubview .= '<a class="n2s-userlist n2lbox.ajax ajaxlink adpan-top left" href="'.$this->view->url(array('module'=>'default','controller'=>'venues','action'=>'adminlist','id'=>$adress->id), 'default', true).'">'.$this->view->translate('administrators').'</a>';
                $pubview .= '<a class="ajaxlink adpan-top left" href="'.$this->view->url(array('module'=>'default','controller'=>'venues','action'=>'edit','id'=>$adress->id), 'default', true).'">'.$this->view->translate('Edit venue').'</a>';
                if($auth->getIdentity()->type == 'profil'){
                    $messages = new Community_Model_MsgRecepient();
                    $unreadMsg = $messages->getAjaxMsg($adress->creator);
                    $pubview .= '<div class="left" style="position:relative;">';
                    $pubview .= '<a class="ajaxlink adpan-top left" href="'.$this->view->url(array('module'=>'community','controller'=>'messages','action'=>'index','view'=>$adress->creator), 'default', true).'">';
                    $pubview .= $this->view->translate('Messages').'</a>';
                    if(count($unreadMsg) > 0){
                        (count($unreadMsg) > 99)?$countMsg = '>99':$countMsg = count($unreadMsg);
                        $pubview .= '<a class="tpAbsCnt" href="'.$this->view->url(array('module'=>'community','controller'=>'messages','action'=>'index','view'=>$adress->creator), 'default', true).'">';
                        $pubview .= $countMsg;
                        $pubview .= '</a>';
                    }
                    $pubview .= '</div>';
                    $pubview .= '<a class="ajaxlink adpan-top left" id="'.$adress->creator.'" onclick="javascript:n2s.access.change(this);" opt="changeprofil" href="javascript:void(0);">'.$this->view->translate('profile switch to this page').'</a>';
                }
                $pubStand = 'var pubview = 1;';
            }
        }
        
        $this->view->activity = $this->view->action('index','activities','default',array('task'=>'venue','cid'=>$adress->id));
        $this->view->public = $pubStand;
        $this->view->pubview = $pubview;
        if($this->_request->isXmlHttpRequest())
            $this->_helper->layout()->disableLayout();
    }
    
    public function adminlistAction()
    {
        $id = (int)$this->_request->getParam('id',0);
        $task = (string)$this->_request->getParam('task');
        $show = (string)$this->_request->getParam('show');
        if($this->_request->isXmlHttpRequest() && $id > 0){
            $curuser = N2S_User::curuser();
            $checkAdmin = new Community_Model_Admins();
            if($task == 'exitadmin'){
                $checkAdmin->getAccess($curuser, $id);
                if(isset($checkAdmin)){
                    $html = '<div id="exAdConfTo" style="text-align:center;margin-top:15%;"><div style="margin-bottom: 20px;">';
                    $html .= $this->view->translate('Du you realy want to exit the administration?');
                    $html .= '</div>';
                    $html .= '<button id="im_check_button" style="font-size: inherit; font-weight: bold; padding: 2px 10px;" ';
                    $html .= 'onclick="javascript:vens.remselfadmin('.$curuser.','.$id.',\'venue\');">';
                    $html .= $this->view->translate('confirm');
                    $html .= '</button>';
                    $html .= '<button id="im_check_button" onclick="$.n2lbox.close();" style="margin-left: 10px;font-size: inherit; font-weight: bold; padding: 2px 10px;">';
                    $html .= $this->view->translate('cancel');
                    $html .= '</button>';
                    $html .= '</div>';
                    $this->view->html = $html;
                    $this->_helper->layout()->disableLayout();
                } else {
                    $this->_helper->redirector('notfound', 'Error', 'default');
                }
            } else {
                $adresses = new Default_Model_Adresses();
                $adress = $adresses->getAdress($id);
                if(isset($adress)){
                    $curUserID = $checkAdmin->getCuruser($adress->creator, 'venue');
                    if($curuser > 0 && $adress->creator > 0 && $curUserID == $adress->creator){
                        $page = $this->_request->getParam( 'page' , 1 );
                        $admins = new Community_Model_Admins();
                        $admin = $admins->findAdmins($adress->creator, 'venue');
                        $joinList = array();
                        $frList = array();
                        if(count($admin) > 0){
                            foreach ($admin as $j){
                                $joinList[] = $j->userid;
                            }
                        }
                        $new = FALSE;
                        if($task == 'add'){
                            $new = TRUE;
                            $friends = new Community_Model_FrRequest();
                            $friend = $friends->getFriendsList($curuser);
                            if(count($friend) > 0){
                                foreach ($friend as $fr){
                                    $frList[] = $fr->connect_from;
                                }
                                $result = array_diff($frList, $joinList);
                            }
                        } else {
                            $result = $joinList;
                        }

                        if(count($result) > 0){
                            $listHtml = $this->_adminslist($result,5,$page,$adress->creator,$new);
                        } else {
                            $listHtml = $this->view->translate('There are no users.');
                        }

                        $html = '';
                        if($show !== 'ajax'){
                            $html .= '<div class="n2Module"><h3>';
                            if($task == 'add'){
                                $html .= $this->view->translate('Add administrators');
                            } else {
                                $html .= $this->view->translate('Administrators');
                            }
                            $html .= '</h3>';
                            if($task != 'add')
                                $html .= '<div><a class="ajaxlink adpan-top n2s-userlist n2lbox.ajax" href="'.$this->view->url(array('module'=>'default','controller'=>'venues','action'=>'adminlist','task'=>'add','id'=>$adress->id), 'default', true).'">'.$this->view->translate('Add administrators').'</a></div>';
                            $html .= '<div id="n2s-listin-box">';
                        }
                        $html .= $listHtml;
                        if($show !== 'ajax'){
                            $html .= '</div></div>';
                            $this->view->html = $html;
                            $this->_helper->layout()->disableLayout();
                        } else {
                            $result = array('error'=>FALSE,'html'=>$html);
                            $this->_helper->json($result);
                        }
                    } else {
                        $this->_helper->redirector('notfound', 'Error', 'default');
                    }
                } else {
                    $this->_helper->redirector('notfound', 'Error', 'default');
                }
            }
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }
    }
    
    public function _adminslist($list,$count,$page,$object,$new = FALSE)
    {
        $users = new Community_Model_Users();
        $user = $users->getUsersInList($list);
        $curuser = N2S_User::curuser();
        $html = '<ul>';
        if(count($user) > 0 && $curuser > 0){
            $paginator = Zend_Paginator::factory($user);
            $paginator->setItemCountPerPage($count);
            $paginator->setCurrentPageNumber($page);
            $adReqs = new Default_Model_AdressesAdminRequest();
            $html .= $this->view->paginationControl($paginator, 'Sliding', '_partials/ajaxpagination.phtml');
            foreach ($paginator as $u)
            {
                
                $html .= '<li class="newsfeed-item"><div class="newsfeed-avatar">';
                $html .= $this->view->userThumb($u->userid,1,0);
                $html .= '</div>';
                $html .= '<div class="newsfeed-content">';
                $html .= '<div clas="newsfeed-content-top"><a class="black" href="'.$this->view->userLink($u->userid).'">'.$u->name.'</a></div>';
                if($u->userid == $curuser && $new == FALSE && count($list) > 1){
                    $html .= '<div class="newsfeed-meta small">';
                    $html .= '<div id="dAdRq'.$u->userid.'">';
                    $html .= '<a class="ajaxlink adpan-top n2s-message n2lbox.ajax" href="';
                    $html .= $this->view->url(array(
                                "module"=>"default",
                                "controller"=>"venues",
                                "action"=>"adminlist",
                                "id"=>$object,
                                "task"=>"exitadmin"),'default', true);
                    $html .= '">';
                    $html .= $this->view->translate('Exit administration').'</a>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
                if($new == true){
                    $checkAd = $adReqs->getRequest($u->userid, $object);
                    $html .= '<div class="newsfeed-meta small">';
                    $html .= '<div id="dAdRq'.$u->userid.'">';
                    if(isset($checkAd)){
                        $html .= '<div style=\"text-align:center;\"><span>'.$this->view->translate('Admin request was sended').'</span>';
                        $html .= '<span><a href="javascript:void(0);" onclick="javascript:vens.addadminreq('.$u->userid.','.$object.',\'venue\',\'removeadminreq\');"><img id="resetLocButton" title="'.$this->view->translate('Click here to reset').'" class="n2s-tooltip" src="/images/reset.png" alt=""/></a></span>';
                        $html .= '</div>';
                    } else {
                        $html .= '<a class="ajaxlink adpan-top left" onclick="javascript:vens.addadminreq('.$u->userid.','.$object.',\'venue\',\'addadminreq\');" href="javascript:void(0);">';
                        $html .= $this->view->translate('Set as admin');
                        $html .= '</a>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                }
                $html .= '</div>';
                $html .= '</li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }

    public function editAction()
    {
        $auth = Zend_Auth::getInstance();
        $id = (int)$this->_request->getParam('id',0);
        if ($auth->hasIdentity() && $id > 0) {
            $curuser = $auth->getIdentity()->userid;
            $profil = new Community_Model_Users();
            $user = $profil->getUser($curuser);
            if (0 == count($user)) {
                $this->_helper->redirector('notfound', 'Error', 'default');
            } else {
                $events = new Default_Model_Adresses();
                $event = $events->getAdress($id);
                $checkAdmin = new Community_Model_Admins();
                $curUserID = $checkAdmin->getCuruser($event->creator, 'venue');
                if(!isset($event) || $event->creator == 0 || $event->creator != $curUserID){
                    $this->_helper->redirector('notfound','Error','default');
                } else {
                    $curuser = $curUserID;
                    $request = $this->getRequest();
                    $form = new Default_Form_VenueEdit();
                    $returnURL = $this->view->url(array('module'=>'default','controller'=>'venues','action'=>'show','id'=>$id),'default', true);
                    $form->getElement('cancel')->setAttrib('onclick', 'javascript:window.location.replace("'.$returnURL.'");');
                    $this->view->returnURL = $returnURL;
                    if ($request->isPost() && $form->isValid($_POST))
                    {
                        $data = $form->getValues();
                        $newname = trim($data['albname']);
                        $updata = array(
                            'name'=>$newname,
                            'description'=>trim($data['albdescription'])
                        );
                        $events->updateAdress($id, $updata);
                        
                        $userdata = array(
                            'name'=>$newname
                        );
                        $profil->updateProfil($event->creator, $userdata);
                        $this->_helper->FlashMessenger($this->view->translate('Venue was updated.'));
                        $this->_helper->redirector('show','venues','default', array('id' => $id));
                    }
                    
                    $evVals = array(
                            //'permissions'=>$event->permission,
                            'albdescription'=>$event->description,
                            //'specdescription'=>$event->specials,
                            'albname'=>$event->name
                        );
                    $form->setDefaults($evVals);
                    $form = '<div style="float: left; box-shadow: 0px 1px 10px rgba(0, 0, 0, 0.3); padding: 0px 3%; margin: 15px 0px 15px 5%; width: 85%;">'.$form.'</div>';
                    $this->view->html = $form;
                    $this->view->headTitle($this->view->translate('Edit venue').' - '.$event->name, 'PREPEND');
                }
            }
        } else {
            $this->_helper->redirector('notfound', 'Error', 'default');
        }
    }
        
    private function _geoSearch($pos)
    {
        $sessionlang = new Zend_Session_Namespace('userlanguage');
        (isset($sessionlang->language))?$lang = $sessionlang->language:$lang = null;
        
        $geoloc = N2S_GeoCode_GoogleGeocode::googleGeocode($pos,$lang);
        
        if(is_array($geoloc) && count($geoloc) > 0){
            $a_lat = $geoloc['ne_lat'];
            $a_long = $geoloc['ne_lng'];
            $b_lat = $geoloc['sw_lat'];
            $b_long = $geoloc['sw_lng'];
            $eradius = 6378.137;

            $distanz = acos(sin($b_lat/180*M_PI)*sin($a_lat/180*M_PI) + cos($b_lat/180*M_PI)*cos($a_lat/180*M_PI)*cos($b_long/180*M_PI-$a_long/180*M_PI) ) * $eradius;

            if($a_lat > $b_lat){
                $min_lat = $b_lat;
                $max_lat = $a_lat;
            } else {
                $min_lat = $a_lat;
                $max_lat = $b_lat;
            }

            if($a_long > $b_long){
                $min_long = $b_long;
                $max_long = $a_long;
            } else {
                $min_long = $a_long;
                $max_long = $b_long;
            }

            $searchLat = $geoloc['lat'];
            $searchLon = $geoloc['lng'];

            if(ceil($distanz)/2 < 2){
                $searchRadius = 2;
                $min_lat = $searchLat - ($searchRadius / 69);
                $min_long = $searchLon - $searchRadius / abs(cos(deg2rad($searchLat)) * 69);
                $max_lat = $searchLat + ($searchRadius / 69);
                $max_long = $searchLon + $searchRadius / abs(cos(deg2rad($searchLat)) * 69);
            } else {
                $searchRadius = ceil($distanz)/2;
            }
            
            $result = array(
                'radius' => $searchRadius,
                'latitude' => $searchLat,
                'longitude' => $searchLon,
                'minlatitude' => $min_lat,
                'minlongitude' => $min_long,
                'maxlatitude' => $max_lat,
                'maxlongitude' => $max_long
            );
            return $result;
        }
    }
}