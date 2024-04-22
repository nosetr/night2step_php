<?php
/**
 * Resizes a given file and saves the created file
 *
 * @category   N2S
 * @package    N2S_Filter
 */
abstract class N2S_Filter_File_Resize_Adapter_Abstract
{
    abstract public function resize($width, $height, $keepRatio, $file, $target, $keepSmaller = true);
 
    protected function _calculateWidth($oldWidth, $oldHeight, $width, $height)
    {
        // now we need the resize factor
        // use the bigger one of both and apply them on both
        $factor = max(($width/$oldWidth), ($height/$oldHeight));
        return array($oldWidth*$factor, $oldHeight*$factor);
    }
}
