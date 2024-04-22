<?php

/*
 * @author Nikolay Osetrov
 * @copyright (C) 2013 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 18.10.2013 19:24:05
 * 
 */
#!/usr/local/bin/php -q
require_once 'init.php';

$model = new Default_Model_Search();
$model->setMultiList();
