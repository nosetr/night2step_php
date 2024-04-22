<?php

/**
 * CSV.php
 * Description of CSV
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 07.05.2013 17:54:43
 * 
 */
class Default_Model_CSV
{
    public function getCsv($array, $fileName)
    {   
        $pathname = APPLICATION_PATH . '/../tmp/';
        $file = $pathname.$fileName.'_'.time().'.csv';
        $fp = fopen($file, 'w');
        foreach ($array as $content){
            fputcsv($fp, $content, ';', '"');
        }
        fclose($fp);
        
        return $file;
    }
}
