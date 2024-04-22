<?php

/**
 * Adresses.php
 * Description of Adresses
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 23.10.2012 19:01:34
 * 
 */
class Default_Model_DbTable_Adresses extends N2S_DbTable_Abstract
{
    protected $_name = 'adresses';
    
    public function getAdress($term)
    {
        $select = $this->select()->from($this, array('value'=>'name','label'=>'label','address'=>'address','country'=>'country','id'=>'id'))
                                    ->where('name LIKE ? ','% '.$term.'%')
                                    ->orWhere('name LIKE ? ',$term.'%')
                                    ->limit(5);

        return $this->fetchAll($select)->toArray();
    }
}