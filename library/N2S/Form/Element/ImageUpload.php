<?php

class N2S_Form_Element_ImageUpload extends Zend_Form_Element_File
{
    public $options;
  
    public function __construct($image_name, $attributes, $image_path=null, $thumb_path=null) {
        $this->options = $image_path;
        
        parent::__construct($image_name, $attributes);
        
        //
        // By default the description is rendered last, but we want to use
        // it to show the <img> when the user is editing it, so we rearrange
        // the order in which the things are rendered
        //
        $this->clearDecorators();
        $this
            ->addDecorator('File')
            ->addDecorator('Description', array('tag' => 'div',
                //'style' => 'float:right;clear: both;',
                'id' => 'n2s-thumbchange',
                'class' => 'description image-preview', 'escape' => false))
            ->addDecorator('Errors')
            ->addDecorator('HtmlTag', array('tag' => 'dd'))
            ->addDecorator('Label', array('tag' => 'dt'))
            //->setAttrib('onChange', 'handleFileSelect();')
                ;
        
        if ($image_path) {
            $this->setDescription('<div id="bildpreview">Current image:<br/><img id="thumbChange" class="thumb" src="'.$image_path.'" style="margin-left: 10px;" alt=""/>
                <div style="position: relative;overflow: hidden;width: 64px;height: 64px;float: left;">
                <img style="position: relative;margin-left:-10px;margin-top:-10px;max-width: 200px;" src="'.$image_path.'" alt=""/>
                <img id="curThumb" src="'.$thumb_path.'" style="position: absolute; top: 0pt; width: 65px; z-index: 1; left: 0pt;" alt=""/>
                </div></div>'.$this->_script());
        } else {
            $this->setDescription('<div id="bildpreview"></div>'.$this->_script());
        }
    }
    
    public function _script()
    {
        return '<script>
            function preview(img, selection) {
                var scaleX = 64 / (selection.width || 1);
                var scaleY = 64 / (selection.height || 1);

                $("#thumbChange + div > img").css({
                    width: Math.round(scaleX * 200) + \'px\',
                    marginLeft: \'-\' + Math.round(scaleX * selection.x1) + \'px\',
                    marginTop: \'-\' + Math.round(scaleY * selection.y1) + \'px\'
                });
                $("#curThumb").remove();
            }
            function handleFileSelect(evt) {
                var files = evt.target.files; // FileList object
                
                // Loop through the FileList and render image files as thumbnails.
                for (var i = 0, f; f = files[i]; i++) {
                    // Only process image files.
                    if (!f.type.match("image.*")) {
                        alert("FILE MUST BE .JPG, .GIF or .PNG");
                        continue;
                    }
                    $("#boxselector").remove();
                    $(".imgareaselect-outer").remove();
                    $(\'input[name="x1"]\').val("10");
                    $(\'input[name="y1"]\').val("10");
                    $(\'input[name="x2"]\').val("75");
                    $(\'input[name="y2"]\').val("75");
                    
                    var reader = new FileReader();
                    // Closure to capture the file information.
                    reader.onload = (function(theFile) {
                        return function(e) {
                            // Render thumbnail.
                            var span = document.createElement("div");
                            span.setAttribute("id","bildpreview");
                            span.innerHTML = [\'Current image:<br/><img id="thumbChange" class="thumb" style="margin-left: 10px;" src="\', e.target.result,
                                    \'" title="\', theFile.name, \'" alt=""/><div style="position: relative;overflow: hidden;width: 65px;height: 65px;float: left;"><img src="\', e.target.result,
                                    \'" style="position: relative;margin-left:-10px;margin-top:-10px;max-width: 200px;" alt=""/></div>\'].join(\'\');
                            document.getElementById("n2s-thumbchange").replaceChild(span,  document.getElementById("n2s-thumbchange").firstChild);
                            
                            $("#thumbChange").imgAreaSelect({
                                  x1: 10,
                                  y1: 10,
                                  x2: 75,
                                  y2: 75,
                                  handles: true,
                                  fadeSpeed: 200,
                                  minHeight:65,
                                  minWidth:65,
                                  aspectRatio: \'1:1\',
                                  onSelectChange: preview,
                                  onSelectEnd: function (img, selection) {
                                      $(\'input[name="x1"]\').val(selection.x1);
                                      $(\'input[name="y1"]\').val(selection.y1);
                                      $(\'input[name="x2"]\').val(selection.x2);
                                      $(\'input[name="y2"]\').val(selection.y2);
                                  }
                              });
                        };
                    })(f);
                    // Read in the image file as a data URL.
                    reader.readAsDataURL(f);
                }
            }
            
            $(document).ready(function() {
                $("#thumbChange").imgAreaSelect({
                                  //x1: 10,
                                  //y1: 10,
                                  //x2: 75,
                                  //y2: 75,
                                  handles: true,
                                  fadeSpeed: 200,
                                  minHeight:65,
                                  minWidth:65,
                                  aspectRatio: \'1:1\',
                                  //onSelectStart: $("#curThumb").remove(),
                                  onSelectChange: preview,
                                  onSelectEnd: function (img, selection) {
                                      $(\'input[name="x1"]\').val(selection.x1);
                                      $(\'input[name="y1"]\').val(selection.y1);
                                      $(\'input[name="x2"]\').val(selection.x2);
                                      $(\'input[name="y2"]\').val(selection.y2);
                                  }
                              });
            });
            
            document.getElementById("imgfile").addEventListener("change", handleFileSelect, false);
            </script>';
    }
}