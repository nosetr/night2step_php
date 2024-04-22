<?php
/**
 * Crop a given file and saves the created file
 *
 * @category   N2S
 * @package    N2S_Filter
 */
class N2S_Filter_File_Multicropthumb implements Zend_Filter_Interface
{
    protected $_thumbwidth = null;
    protected $_thumbheight = null;
    protected $_directory = null;
    //protected $_x = 0;
    //protected $_y = 0;
    protected $_name = null;
    
    public function __construct($options = array())
    {
        if (!is_array($options)) {
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }
        
        if (!isset($options['thumbwidth']) && !isset($options['thumbheight']) && !isset($options['width'])) {
            throw new Zend_Filter_Exception('At least one of width or height must be defined');
        }
        
        if (isset($options['thumbwidth'])) {
            $this->_thumbwidth = $options['thumbwidth'];
        }
        if (isset($options['thumbheight'])) {
            $this->_thumbheight = $options['thumbheight'];
        }
        if (isset($options['directory'])) {
            $this->_directory = $options['directory'];
        }
        if (isset($options['name'])) {
            $this->_name = $options['name'];
        }
    }
    
    public function filter($value)
    {
        if ($this->_name) {
            $name = $this->_name;
            if ($this->_directory) {
                $target = $this->_directory . '/' . $name . basename($value);
            } else {
                $path_parts = pathinfo($value);
                $target = $path_parts['dirname']. '/' . $name . basename($value);
            }
        } else {
            if ($this->_directory) {
                $target = $this->_directory . '/' . basename($value);
            } else {
                $target = $value;
            }
        }
        
        list($width_orig, $height_orig, $type) = getimagesize($value);
        
        switch ($type) {
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($value);
                break;
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($value);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($value);
                break;
        }
        
        //$source = imagecreatefromstring(file_get_contents($value));
        
        $ImageWidth = $width_orig;
        $ImageHeight = $height_orig;
        
        if($width_orig < $height_orig){
            $newImageWidth = ceil($this->_thumbwidth);
            $newImageHeight = ceil($this->_thumbwidth * $ImageHeight / $ImageWidth);
            $x = 0;
            $y = ceil(($ImageHeight - $ImageWidth) / 4);
        } else {
            $newImageHeight = ceil($this->_thumbheight);
            $newImageWidth = ceil($this->_thumbheight * $ImageWidth / $ImageHeight);
            $x = ceil(($ImageWidth - $ImageHeight) / 2);
            $y = 0;
        }
        
	$newImage = imagecreatetruecolor($this->_thumbwidth,$this->_thumbheight);
        ImageColorAllocate ($newImage, 255, 255, 255);//Weise Hintergrund

        imagealphablending($newImage, false);
        imagesavealpha($newImage, false);
        
	imagecopyresampled($newImage,$source,0,0,$x,$y,$newImageWidth,$newImageHeight,$ImageWidth,$ImageHeight);
	//imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        switch ($type) {
            case IMAGETYPE_PNG:
                imagepng($newImage, $target);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $target);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $target);
                break;
        }
	return $target;
    }
}