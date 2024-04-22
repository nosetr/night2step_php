var uploader = new plupload.Uploader({
        runtimes: 'html5,flash',
        contains: 'plupload',
        browse_button: 'browse',
        drop_element: 'droparea',
        url: '/ajax/multialbupload',
        flash_swf_url: 'js/plupload/plupload.flash.swf',
        multipart: true,
        urlstream_upload: true,
        multipart_params:{userid: $('input#userid').val(),albumid: $('input#albumid').val()},
        resize: {width: 960,height:960,quality:85},
        filters: [
            {title: 'Images', extensions: 'jpg,png,gif,jpeg'}
        ]
});

uploader.bind('Init', function(up, params){
    if(params.runtime !== 'html5'){
        $('#droparea').find('p,span').remove();
        $('#browse').css('margin','20px');
    }
});

uploader.init();

uploader.bind('FilesAdded', function(up,files){
    var filelist = $('ul.n2s-ajaxpage');
   for(var i in files){
       var file = files[i];
       filelist.prepend('<li id="'+file.id+'" class="file">'+file.name+' ('+plupload.formatSize(file.size)+')'+'<div class="progressbar"><img src="/images/ajax/ajax-loader3.gif" alt=""/></div></li>');
   }
   $('#droparea').removeClass('hover');
   uploader.start();
   uploader.refresh();
});

uploader.bind('Error', function(up,err){
    alert(err.message);
    $('#droparea').removeClass('hover');
    uploader.refresh();
});

uploader.bind('FileUploaded', function(up,file,response){
    data = $.parseJSON(response.response);
    if(data.error){
        alert(data.message);
        $('#'+file.id).remove();
    } else {
        $('#'+file.id).replaceWith(data.html);
    }
});

jQuery(function($){
    $('#droparea').bind({
        dragover: function(e){
            $(this).addClass('hover');
        },
        dragleave: function(e){
            $(this).removeClass('hover');
        }
    });
});