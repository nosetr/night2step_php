<?php
/**
 * Resizes a given file with the gd adapter and saves the created file
 *
 * @category   N2S
 * @package    N2S_Filter
 */
class N2S_Filter_File_Resize_Adapter_Gd extends
    N2S_Filter_File_Resize_Adapter_Abstract
{
    public function resize($width, $height, $keepRatio, $file, $target, $keepSmaller = true)
    {
        list($oldWidth, $oldHeight, $type) = getimagesize($file);
 
        switch ($type) {
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($file);
                break;
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($file);
                break;
        }
 
        if (!$keepSmaller || $oldWidth > $width || $oldHeight > $height) {
            if ($keepRatio) {
                list($width, $height) = $this->_calculateWidth($oldWidth, $oldHeight, $width, $height);
            }
        } else {
            $thumb = imagecreatetruecolor($oldWidth, $oldHeight);
            ImageColorAllocate ($thumb, 255, 255, 255);//Weise Hintergrund

            imagealphablending($thumb, false);
            imagesavealpha($thumb, false);

            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $oldWidth, $oldHeight, $oldWidth, $oldHeight);
            
            switch ($type) {
                case IMAGETYPE_PNG:
                    imagepng($thumb, $target);
                    break;
                case IMAGETYPE_JPEG:
                    imagejpeg($thumb, $target);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($thumb, $target);
                    break;
            }
            return $target;
        }
 
        $thumb = imagecreatetruecolor($width, $height);
        ImageColorAllocate ($thumb, 255, 255, 255);//Weise Hintergrund
 
        imagealphablending($thumb, false);
        imagesavealpha($thumb, false);
 
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width, $height, $oldWidth, $oldHeight);
        
        //$path_parts = pathinfo($target);
        //$newtarget = $path_parts['dirname'].'/'.$path_parts['filename'].'.jpg';
        
        switch ($type) {
            case IMAGETYPE_PNG:
                imagepng($thumb, $target);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $target);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $target);
                break;
        }
        //imagejpeg($thumb, $target);
        //imagedestroy($thumb);
        return $target;
    }
}