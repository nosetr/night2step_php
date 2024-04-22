<?php

/**
 * Upload.php
 * Description of Upload
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 20.11.2013 11:48:38
 * 
 */
class N2S_Photo_Upload
{
    protected $_albumID     = null;
    protected $_basePath    = null;
    
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            //require_once 'Zend/Filter/Exception.php';
            throw new N2S_Photo_Exception('Invalid options argument provided to upload');
        }
 
        if (!isset($options['albumID'])) {
            //require_once 'Zend/Filter/Exception.php';
            throw new N2S_Photo_Exception('At least albumid must be defined');
        }
        
        if (isset($options['albumID'])) {
            $this->_albumID = $options['albumID'];
        }
        
        if (isset($options['basePath'])) {
            $this->_basePath = $options['basePath'];
        } else {
            $this->_basePath = BASE_PATH.'/albums/';
        }
    }
    
    public function upload()
    {
        $albums = new Default_Model_PhotoAlbums();
        $album = $albums->getComAlbumInfo($this->_albumID);
        
        $index      = $this->_basePath.'index.html';
        $albPath    = $this->_basePath.$this->_albumID;
        $origPath   = $albPath.'/originalphotos/';
        $photoPath   = $albPath.'/photos/';
        
        if (is_dir($albPath) == FALSE){
            mkdir($albPath, 0755);
            copy ($index, $albPath.'/index.html');
        }
        if (is_dir($origPath) == FALSE){
            mkdir($origPath, 0755);
            copy ($index, $origPath.'/index.html');
        }
        if (is_dir($photoPath) == FALSE){
            mkdir($photoPath, 0755);
            copy ($index, $photoPath.'/index.html');
        }
        
        @set_time_limit(5 * 60);//5 minutes
        try {
            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->addValidator('Count',false, array('min'=>1, 'max'=>100))
                    ->addValidator('Size',false,$this->_returnBytes(ini_get('upload_max_filesize')))
                    ->addValidator('Extension',false,array('extension' => 'JPG,JPEG,PNG,GIF,jpg,jpeg,png,gif','case' => true));

            $adapter->setDestination($origPath);

            $files = $adapter->getFileInfo();
            var_dump($files);
            $count = $album->photocount;
            foreach($files as $fieldname=>$fileinfo)
            {
                if (($adapter->isUploaded($fileinfo['name'])) && ($adapter->isValid($fileinfo['name'])))
                {
                    // Clean the fileName for security reasons
                    $exts = split("[/\\.]", $fileinfo['name']) ;
                    $n = count($exts)-1;
                    $ext = strtolower($exts[$n]);
                    $fileName = md5(preg_replace('/[^\w\._]+/', '_', $fileinfo['name']));
                    $filenameF = $fileName.'_'.time();
                    $filename = $filenameF.'.'.$ext;
                    
                    $adapter->addFilter('Rename',array('target'=>$origPath.$filename,'overwrite'=>false))
                        ->addFilter(new N2S_Filter_File_Resize(array(
                                    'width' => 200,
                                    'height' => 960,
                                    'keepRatio' => true,
                                    'directory' => $photoPath
                                )))
                        ->addFilter(new N2S_Filter_File_Multicropthumb(array(
                                    'thumbwidth' => 134,
                                    'thumbheight' => 134,
                                    'directory' => $photoPath,
                                    'name'  => 'thumb_'
                                )));
                    $adapter->receive($fileinfo['name']);
                    
                    if ($adapter->receive()) {
                        $count++;
                        $data = array('photocount'=>$count);
                        $albums->updateAlbum($this->_albumID, $data);
                        $baseImgPath = 'albums/'.$this->_albumID;
                        
                        $imgPath = $baseImgPath.'/photos/'.$filename;
                        chmod($imgPath, 0644);
                        $thumbPath = $baseImgPath.'/photos/thumb_'.$filename;
                        chmod($thumbPath, 0644);
                        $origimgPath = $baseImgPath.'/originalphotos/'.$filename;
                        chmod($origimgPath, 0644);
                        
                        list($width, $height) = getimagesize($origimgPath);
                        
                        return array(
                            'img'=>$imgPath,
                            'thumb'=>$thumbPath,
                            'orig'=>$origimgPath,
                            'title'=>$filenameF,
                            'width'=>$width,
                            'height'=>$height
                        );
                    }
                }
            }
        } catch (Exception $ex) {
            echo "Exception!\n";
            echo $ex->getMessage();
        }
    }
        
    protected function _returnBytes($val) {
        $val = trim($val);
        $last = strtolower($val{strlen($val)-1});
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
     }
}
