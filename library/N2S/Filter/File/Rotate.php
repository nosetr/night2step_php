<?php

/**
 * Rotate.php
 * Image Rotate
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 17.09.2013 18:34:25
 * 
 */
class N2S_Filter_File_Rotate implements Zend_Filter_Interface
{
    protected $_degrees = null;
    
    public function __construct($options = array())
    {
        if (!is_array($options)) {
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }
        
        if (isset($options['degrees'])) {
            $this->_degrees = $options['degrees'];
        }
    }
    
    public function filter($id)
    {
        $photos = new Default_Model_Photos();
        $photo = $photos->getPhotoID($id);
        
        $error = TRUE;
        if(isset($photo) && $this->_degrees != NULL){
            if(file_exists(BASE_PATH.'/'.$photo->original)){
                $value = BASE_PATH.'/'.$photo->original;
                $this->__getFrom($value);
                
                $error = FALSE;
            }
            if(file_exists(BASE_PATH.'/'.$photo->thumbnail)){
                $value = BASE_PATH.'/'.$photo->thumbnail;
                $this->__getFrom($value);
                
                $error = FALSE;
            }
            if(file_exists(BASE_PATH.'/'.$photo->image)){
                $value = BASE_PATH.'/'.$photo->image;
                $this->__getFrom($value);
                
                $error = FALSE;
            }
        }
        
        return $error;
    }
    
    protected function __getFrom($value)
    {
        list($width_orig, $height_orig, $type) = getimagesize($value);
        
        switch ($type) {
            case IMAGETYPE_PNG:
                //header('Content-type: image/png');
                $source = imagecreatefrompng($value);
                $rotate = imagerotate($source, $this->_degrees, 0);
                imagepng($rotate, $value);
                break;
            case IMAGETYPE_JPEG:
                //header('Content-type: image/jpeg');
                $source = imagecreatefromjpeg($value);
                $rotate = imagerotate($source, $this->_degrees, 0);
                imagejpeg($rotate, $value);
                break;
            case IMAGETYPE_GIF:
                //header('Content-type: image/gif');
                $source = imagecreatefromgif($value);
                $rotate = imagerotate($source, $this->_degrees, 0);
                imagegif($rotate, $value);
                break;
        }
    }
}
