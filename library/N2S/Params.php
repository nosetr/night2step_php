<?php

/**
 * Params.php
 * Description of Params
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 04.04.2013 10:25:14
 * 
 */
class N2S_Params
{
    protected $_registry = NULL;
    
    public function __construct($registry = 'N2S_Params')
    {
        $this->_registry = $registry;
    }

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}
    
    public function set($reg, $value, $add = FALSE)
    {
        $curent = array();
        
        if(Zend_Registry::isRegistered($this->_registry))
            $curent = Zend_Registry::get($this->_registry);
        
        if(isset($curent[$reg]) && is_array($curent[$reg])){
            if(in_array($value,$curent[$reg])){
                $value = $curent[$reg][0];
            } else {
                $value = $curent[$reg][0].','.$value;
            }
        }
        $curent[$reg] = array($value,$add);
        
        Zend_Registry::set($this->_registry, $curent);
        
        return $curent;
    }

    public function get($string = NULL)
    {
        $nA = null;
        
        if(isset($string))
        {
            $string = str_replace(chr(10), "&", $string);
            parse_str($string,$nA);
        }
        return $nA;
    }

    public function toString($string = NULL)
    {
        $result = $string;
        
        $sA = array();
        if(isset($string)){
            $sA = $this->get($string);
        }
        
        if(Zend_Registry::isRegistered($this->_registry)){
            $result = '';
            $curent = Zend_Registry::get($this->_registry);

            if(is_array($curent)){
                
                $addArray = array();
                $valArray = array();
                
                foreach ($curent as  $key => $a2){
                    if($a2[1] == TRUE){
                        $valArray[$key] = $a2[0];
                    } else {
                        $addArray[$key] = $a2[0];
                    }
                }
                
                $sA = array_merge($sA,$addArray);
                
                if(count($sA) > 0)
                    $valArray = array_merge_recursive($valArray,$sA);
                    
                foreach ($valArray as $key => $value){
                    if(is_array($value))
                        $value = implode (',', $value);
                    $result = $result.$key.'='.$value.chr(10);
                }
            }
        }
        return $result;
    }
}
