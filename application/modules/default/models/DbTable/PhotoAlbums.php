<?php

/**
 * PhotoAlbums.php
 * Description of PhotoAlbums
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 30.10.2012 19:39:02
 * 
 */
class Default_Model_DbTable_PhotoAlbums extends N2S_DbTable_Abstract
{

    protected $_name = 'photos_albums';
    
    public function getAlbum($id)
    {
        $select = $this->select()->from($this, array('id'=>'id','name'=>'name','description'=>'description'))
                                    ->where('id =? ',$id);

        return $this->fetchRow($select)->toArray();
    }

}