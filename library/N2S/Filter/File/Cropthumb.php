<?php
/**
 * Crop a given file and saves the created file
 *
 * @category   N2S
 * @package    N2S_Filter
 */
class N2S_Filter_File_Cropthumb implements Zend_Filter_Interface
{
    protected $_width = null;
    protected $_thumbwidth = null;
    protected $_thumbheight = null;
    protected $_directory = null;
    protected $_name = null;
    protected $_x = 0;
    protected $_y = 0;
    protected $_w = 0;
    
    public function __construct($options = array())
    {
        if (!is_array($options)) {
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }
        
        if (!isset($options['thumbwidth']) && !isset($options['thumbheight']) && !isset($options['width'])) {
            throw new Zend_Filter_Exception('At least one of width or height must be defined');
        }
        
        if (isset($options['width'])) {
            $this->_width = $options['width'];
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
        if (isset($options['x'])) {
            $this->_x = $options['x'];
        }
        if (isset($options['y'])) {
            $this->_y = $options['y'];
        }
        if (isset($options['w'])) {
            $this->_w = $options['w'];
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
        
        $ImageWidth = $width_orig;
        $ImageHeight = $height_orig;
        
        $kor = $width_orig / $this->_width;
        $this->_x = $this->_x * $kor;
        $this->_y = $this->_y * $kor;
        
        $scal =  $this->_width / $this->_w;
        $newImageWidth = ceil($this->_thumbwidth * $scal);
        $newImageHeight = ceil($newImageWidth * $ImageHeight / $ImageWidth);
        
	$newImage = imagecreatetruecolor($this->_thumbwidth,$this->_thumbheight);
        ImageColorAllocate ($newImage, 255, 255, 255);

        imagealphablending($newImage, false);
        imagesavealpha($newImage, false);
        
	imagecopyresampled($newImage,$source,0,0,$this->_x,$this->_y,$newImageWidth,$newImageHeight,$ImageWidth,$ImageHeight);
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