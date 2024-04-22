var photos = {
    fadOutAlbs: function (id){
        $('#mBox' + id).fadeOut('fast', function() {
            $('html,body').animate({scrollTop: $(window).scrollTop()+5},'slow');
            $("#"+id).attr("onclick", "photos.fadInAlbs('"+id+"')");
        });
    },
    fadInAlbs: function (id){
        $('#mBox' + id).fadeIn('fast', function() {
            $('html,body').animate({scrollTop: $("#"+id).offset().top-20},'slow');
            $("#"+id).attr("onclick", "photos.fadOutAlbs('"+id+"')");
        });
    },
    setAlbs: function (id,date){
        var container = $('#'+id);
        var link = '/photos/index/task/show/count/14/last/'+id+'/date/'+date+'/place/to';
            $. get ( link , {format : 'html'} , function ( data ) {
                if (data !== '') {
                    container.after(data);
                    $('#'+id).css({'height' : '0', 'border' : '0 none'});
                    $('#'+id).empty();
                    $('html,body').animate({scrollTop: $("#"+id).offset().top-20},'slow');
                }
            } , 'html' ) ;
    },
    getlistedit: function(link,alb,last){
        window.bSuppressScroll=true;
        $('div#n2s-content-loading').show();
        $.getJSON(link,{album:alb,last:last},function(data){
            if(data.error){
                alert(data.message);
            }else{
                $('div#n2s-content-loading').hide();
                $('.n2s-ajaxpage').append(data.html);
                if(data.html !== '')
                    window.bSuppressScroll=false;
            }
        });
    },
    thumb: function (id,x1,x2,y1,y2){
        var div = '<div id="th_onload" style="width:134px;height:134px;position:absolute;background:url(\'/images/ajax/ajax-loader1.gif\') no-repeat scroll 45px 55px rgba(255,255,255,0.5);"></div>';
        $('#photo_orig_view'+id).prepend(div);
        $.n2lbox.close();
        $.getJSON('/ajax/thumbupload',{id:id,x1:x1,x2:x2,y1:y1,y2:y2},
            function(data){
                if(data.error){
                    alert(data.message);
                }else{
                    var img = $('#photo_orig_view'+id).find('img');
                    var img2 = $('#albcover_link').find('img');
                    img.attr('src', img.attr('src') + '?' + Math.random());
                    img2.attr('src', img2.attr('src') + '?' + Math.random());
                    $('#th_onload').remove();
                }
            });
    },
    goToByScroll: function (id,to){
        if($("#"+to).length > 0){
            $('html,body').animate({scrollTop: $("#"+to).offset().top-20},'slow');
        } else {
            var container = $('.mBox:last');
            var ID = $('.month:last').attr('id');
            var lID = $('.date:last').attr('id');
            var link = '/photos/index/task/show/count/14/last/'+lID+'/date/'+ID+'/jump/'+id;
            $. get ( link , {format : 'html'} , function ( data ) {
                if (data !== '') {
                    container.after(data) ;
                    window.bSuppressScroll = false;
                    $('html,body').animate({scrollTop: $("#"+to).offset().top-20},'slow');
                }
            } , 'html' ) ;
        }
    },
    moveImg: function(photo, hash, actalb) {
        $.post('/photos/ajax/task/phmove',{photo: photo, album: hash, actalbum: actalb},
            function(data){
                if (data !== '') {
                    $('#photo_orig_view' + photo).fadeOut("fast",function(){
                        $('#photo_after_view' + photo).html(data);
                        $('#photo_after_view' + photo).fadeIn();
                    });
                }
            }
        );
    },
    removeImg: function(photo, hash, actalb) {
        $.post('/photos/ajax/task/phmove',{photo: photo, album: hash, actalbum: actalb},
            function(data){
                if (data !== '') {
                    $('#photo_after_view' + photo).fadeOut("fast",function(){
                        $('#photo_after_view' + photo).html(data);
                        $('#photo_orig_view' + photo).fadeIn();
                    });
                }
            }
        );
    },
    delImg: function(photo, hash) {
        $.post('/photos/ajax/task/phdel',{photo: photo, album: hash},
            function(data){
                if (data !== '') {
                    $('#photo_orig_view' + photo).fadeOut("fast",function(){
                        $('#photo_after_view' + photo).html(data);
                        $('#photo_after_view' + photo).fadeIn();
                    });
                }
            }
        );
    },
    rotImg: function(photo, hash) {
        $.getJSON('/ajax/photorotate',{photo: photo, album: hash},
            function(data){
                if(data.error){
                    alert(data.message);
                }else{
                    var img = $('#photo_orig_view'+photo).find('img');
                    var img2 = $('#albcover_link').find('img');
                    img.attr('src', img.attr('src') + '?' + Math.random());
                    img2.attr('src', img2.attr('src') + '?' + Math.random());
                }
            });
    },
    restorImg: function(photo, hash) {
        $.post('/photos/ajax/task/phrestore',{photo: photo, album: hash},
            function(data){
                if (data !== '') {
                    $('#photo_after_view' + photo).fadeOut("fast",function(){
                        $('#photo_after_view' + photo).html(data);
                        $('#photo_orig_view' + photo).fadeIn();
                    });
                }
            }
        );
    },
    dirImg: function(photo, hash) {
        if ($('#img_delete' + photo).hasClass('checked')){
            $.post('/photos/ajax/task/phdel',{photo: photo, album: hash});
        } else {
            $.post('/photos/ajax/task/phrestore',{photo: photo, album: hash});
        }
    },
    saveDesc: function(photo, hash) {
        var dsc = $('#photo_caption' + photo).val();
        
        $('#photo_save_progress' + photo).show();
        $.post('/photos/ajax/task/phdscedit/',{photo: photo, album: hash, text: $.trim(dsc)},
            function(data){
                if (data !== '') {
                    $('#photo_save_progress' + photo).hide();
                    $('#photo_save_result' + photo).html(data);
                }
            });
    },
    removeCover: function(photo,hash) {
        $.getJSON( '/ajax/setalbcover',{photo:'0',album:hash} ,
            function ( data ) {
                if (data.error) {
                    alert(data.message);
                }else{
                    $(".coverd").each(function() {
                        $(this).empty().text(data.text).removeClass("coverd");
                        var click = $(this).attr("onclick").replace("photos.removeCover","photos.updateCover");
                        $(this).attr("onclick", click);
                    });
                    $("#coverCh"+photo).attr("onclick","photos.updateCover("+photo+","+hash+")").empty().text(data.text).removeClass("coverd");
                    $('#albcover_link').empty();
                    $('#albcover_link').html(data.html);
                    $('#albcover').removeClass('thex');
                }
            }) ;
    },
    updateCover: function(photo,hash) {
        var tx = $("#coverCh"+photo).text();
        $.getJSON( '/ajax/setalbcover',{photo: photo, album: hash} ,
            function ( data ) {
                if (data.error) {
                    alert(data.message);
                }else{
                    $(".coverd").each(function() {
                        $(this).empty().text(tx).removeClass("coverd");
                        var click = $(this).attr("onclick").replace("photos.removeCover","photos.updateCover");
                        $(this).attr("onclick", click);
                    });
                    $("#coverCh"+photo).attr("onclick","photos.removeCover("+photo+","+hash+")").empty().text(data.text).addClass("coverd");
                    $('#albcover_link').empty();
                    $('#albcover_link').html(data.html);
                    var img = $('#albcover_link').find('img');
                    img.attr('src', img.attr('src') + '?' + Math.random());
                    $('#albcover').addClass('thex');
                }
            }) ;
    },
    last_photo_function: function(link) {
        var loader='<div id="n2s-msg-loader"><b>Loading...</b><img src="images/ajax/ajax-loader3.gif" alt=""/></div>';
        var ID=$('.n2s-imgajaxpage:last').attr('id');
        var container = $('.n2s-imgajaxpage:last');
        $('div#n2s-content-loading').show();
        $('div#last_msg_loader').html(loader);
        $.getJSON( link , {page:ID, format : 'html'} , function ( data ) {
            if (!data.error) {
            container.after(data.html) ;
                window.aSuppressScroll = false;
                $('div#last_msg_loader').empty();
                $('div#n2s-content-loading').hide();
                $('.n2s-tooltip').tipTip({maxWidth: 'auto', defaultPosition: 'top'});
            }
        }) ;
    },
    last_msg_funtion: function(link) {
        var loader='<div id="n2s-msg-loader"><b>Loading...</b><img src="images/ajax/ajax-loader3.gif" alt=""/></div>';
        var ID=$('.n2s-ajaxpage:last').attr('id');
        var container = $('.n2s-ajaxpage:last');
        $('div#n2s-content-loading').show();
        $('div#last_msg_loader').html(loader);
        $.getJSON( link , {page:ID, format : 'html'} , function ( data ) {
            if (!data.error) {
            container.after(data.html) ;
                window.bSuppressScroll = false;
                $('div#last_msg_loader').empty();
                $('div#n2s-content-loading').hide();
                $('.n2s-tooltip').tipTip({maxWidth: 'auto', defaultPosition: 'top'});
                photos.cycle();
            }
        }) ;
    },
    cycle: function() {
	$('.n2s-albthumb').mouseenter(function(){
            $(this).addClass('hover');
            if($(this).find('.slides').length){
                $('.slides').cycle({fx:'none',speed:1500,timeout:1500}).cycle("pause");
                $(this).find('.slides').addClass('active').cycle('resume');
                $(this).find('.n2s-mainAlbPh').hide();
            }else{
                if($(this).hasClass('stop')===false){
                    var ID = $(this).attr('rel'),
                    obj = $(this);
                    setTimeout(function(){
                        if(obj.hasClass('hover')){
                            $.getJSON('/ajax/slides',{id:ID,target:'albums'},function(data){
                                if(data.error) {
                                    obj.addClass('stop');
                                }else{
                                    obj.append(data.html);
                                    $('.slides').cycle({fx:'none',speed:1500,timeout:1500}).cycle("pause");
                                    obj.find('.slides').addClass('active').cycle('resume');
                                    if(obj.find('.slides').length && obj.find('.slides').hasClass('active'))obj.find('.n2s-mainAlbPh').delay(800).hide();
                                }
                            }) ;
                        }
                    },800 );
                }
            }
	});
        $('.n2s-albthumb').mouseleave(function(){
            $(this).removeClass('hover');
            if($(this).find('.slides').length){
                $(this).find('.n2s-mainAlbPh').show();
                $(this).find('.slides').removeClass('active').cycle('pause');
            }
	});
    },
    cyclenext: function(obj){
        $('.slides').cycle({fx:'none',speed:1500,timeout:1500}).cycle("pause");

	obj.hover(function(){
                if(obj.find('.slides').length){
                    obj.find('.n2s-mainAlbPh').hide();
                    obj.find('.slides').addClass('active').cycle('resume');
                }
	}, function(){
                if(obj.find('.slides').length){
                    obj.find('.n2s-mainAlbPh').show();
                    obj.find('.slides').removeClass('active').cycle('pause');
                }
	});
    },
    delalbum: function(id){
        $('#confirmdelete').hide();
        $('#chAsub').hide();
        $('#prozessdelete').show();
        $.getJSON( '/photos/delete',{task:'delete',id:id},function(data){
            if(data){
                if(data.error){
                    $('#prozessdelete').html(data.html);
                }else{
                    $('#prozessdelete').find('li').addClass('check');
                    window.location = '/photos/myphotos';
                }
            }
        }) ;
    }
};