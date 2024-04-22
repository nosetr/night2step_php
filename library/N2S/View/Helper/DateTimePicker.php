<?php

/**
 * DateTimePicker.php
 * Description of DateTimePicker
 *
 * @author Nikolay Osetrov
 * @copyright (C) 2012 by night2step.com - All rights reserved!
 * @license http://www.night2step.com Copyrighted Commercial Software
 * @since 21.09.2012 09:18:55
 * 
 */
class N2S_View_Helper_DateTimePicker extends ZendX_JQuery_View_Helper_UiWidget
{

    /**
     * @param String $id
     * @param String $value
     * @param array $params
     * @param array $attribs
     * @return String
     */
    public function DateTimePicker($id, $value = null, array $params = array(), array $attribs = array())
    {
        $attribs = $this->_prepareAttributes($id, $value, $attribs);

        ZendX_JQuery::encodeJson($params);

        $pr = array();
        foreach ($params as $key => $val){
            $pr[] = '"'.$key.'":'.ZendX_JQuery::encodeJson ( $val );
        }
        $pr = '{'.implode(",", $pr).'}';

        $js = sprintf('%s("#%s").datetimepicker(%s);',
                ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
                $attribs['id'],
                $pr
        );

        $this->jquery->addOnLoad($js);
        $this->jquery->addJavascriptFile('/js/jquery/addons/jquery-ui-timepicker-addon.js');

        return $this->view->formText($id, $value, $attribs);
    }
}

