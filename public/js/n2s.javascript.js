/* Copyright 2012 inDeLA Sagl. All Rights Reserved. */
var n2s = {
    plchldr:{
        check:function(){
            function add() {
                if($(this).val() === ''){
                    $(this).val($(this).attr('placeholder')).addClass('placeholder');
                }
            }
            function remove() {
                if($(this).val() === $(this).attr('placeholder')){
                    $(this).val('').removeClass('placeholder');
                }
            }
            if (!('placeholder' in $('<input>')[0])) {
                $('input[placeholder], textarea[placeholder]').blur(add).focus(remove).each(add);
                $('form').submit(function(){
                    $(this).find('input[placeholder], textarea[placeholder]').each(remove);
                });
            }
        }
    },
    noti:{
        changeVal: function (el,data){
            $(el).empty().append(data);
        }
    },
    flash:{
        display:function(){
            $('#n2s-message').delay(800).animate({"left": "+=300px"}, 800).delay(30000);
            n2s.flash.remove();
        },
        click:function(){
            $('#n2s-message').clearQueue();
            n2s.flash.remove();
        },
        remove:function(){
            $('#n2s-message').animate({"left": "-=300px"}, 800);
        }
    },
    full:function(){
        $('#n2l-boxPH').toggleFullScreen();
        $(document).bind("fullscreenchange", function() {
            var f = $('input[name="full"]').val();
            var n = $('input[name="nfull"]').val();
            if($(document).fullScreen()){
                window.fullscr = true;
                $('.n2s-fscreen').attr('id', 'fullactiv');
                $('.n2s-fscreen').attr('title', f);
                $('.phBoxInfo').hide();
                $('.phBoxCol').width('100%');
                $('#n2lbox-loading').css('left','50%');
            } else {
                window.fullscr = false;
                $('.n2s-fscreen').removeAttr('id');
                $('.n2s-fscreen').attr('title', n);
                $('.phBoxInfo').show();
                $('.phBoxCol').removeAttr('style');
                $('#n2lbox-loading').css('left','40%');
            }
        });
    },
    arrowfade:function(){
        if($('.phArrow').length){
            var c;
            $('.phArrow').show();
            clearTimeout(c);
            c = setTimeout(function() {
                $('.phArrow').fadeOut();
            }, 3000);
            $('.phBoxCol').mousemove(function(){
                $('.phArrow').show();
                clearTimeout(c);
                c = setTimeout(function() {
                    $('.phArrow').fadeOut();
                }, 3000);
            });
        }
    },
    publicview:function(){
        var url = document.URL;
        $('#rCol').fadeOut();
        $('#pubArray').empty();
        $.get(url,{pub:window.pubview},
            function(data){
                $('#pubArray').append(data);
                $(".n2s-tooltip").tipTip({maxWidth: "auto", defaultPosition: "top"});
                var img = $('#n2s-backImg').find('img');
                img.attr('src', img.attr('src') + '?' + Math.random());
            });
    },
    access:{
        logout:function(){
            $.getJSON('/community/index/logout',function(data){
                if(data.logout){
                    window.location.replace('/');
                }
            });
        },
        change:function(obj){
            var ajlink = $(obj).attr('opt');
            var ajid = $(obj).attr('id');
            var repl = true;
            if($(obj).attr('repl')){
                repl = false;
            }               
            $('div.n2s-ushelpcont').empty().append('<img style="margin: 7px 15px 0px 0px;" src="/images/ajax/ajax-loader1.gif" alt=""/>');
            $('.n2s-editlink').css({'background-color':'#fff','border-color':'#FFFFFF #FFFFFF #FFFFFF #999999','border-style':'solid'});
            $('.showcasten').hide();
            $.getJSON('/community/index/changeaccount/'+ajlink+'/'+ajid,function(data){
                if(data.changed){
                    if(data.link && repl === true){
                        window.location.replace(data.link);
                    }else{
                        window.location.reload();
                    }
                }
            });
        }
    },
    n2lbox:{
        photo:function(obj){
            if(window.commaction === true){
                window.commaction = false;
                $('#n2lbox-loading').show();
                var ajlink = $(obj).attr('href');
                $.getJSON(ajlink,{full:window.fullscr},function(data){
                            if(data){
                                $('#n2lbox-loading').hide();
                                $('#n2l-boxPH').empty().html(data.html);
                                n2s.arrowfade();
                                $(".n2s-tooltip").tipTip({maxWidth: "auto", defaultPosition: "top"});
                                $('textarea[name=comment]').change(function() {
                                }).keypress(function(e) {
                                    var comm = $.trim($(this).val());
                                    var type = $(this).attr("rel");
                                    var commID = $(this).attr("id");
                                    if (e.which === 13 && comm !== ''){
                                        comment.set(commID,type,comm);
                                    }
                                });
                                $('.comMore').live('click', function(){
                                    var last = $(this).attr("last");
                                    var type = $(this).attr("rel");
                                    var commID = $(this).attr("id");
                                    comment.more(commID,type,last);
                                });
                                $('.comDel').live('click', function(){
                                    var commID = $(this).attr("id");
                                    comment.comdel(commID);
                                });
                                $('.comRestore').live('click', function(){
                                    var commID = $(this).attr("id");
                                    comment.comrestore(commID);
                                });
                                $('.n2lBoxPhNav').live("click", function(e){e.preventDefault();n2s.n2lbox.photo(this);});

                                window.commaction = true;
                            }
                        });
            }
        },
        navi:function(obj){
            if(window.jrtz === true){
                window.jrtz = false;
                $('#n2lbox-loading').show();
                var ajlink = $(obj).attr('href');
                $('#n2s-listin-box').empty();
                $.getJSON(ajlink,{show:'ajax'},function(data){
                        if(data){
                            $('#n2lbox-loading').hide();
                            $('#n2s-listin-box').append(data.html);
                            $(".n2s-tooltip").tipTip({maxWidth: "auto", defaultPosition: "top"});
                            window.jrtz = true;
                        }
                    });
            }
        },
        link:function(obj){
            if(window.subscribe===false){
                window.subscribe = true;
                $('#n2s-imgup-box').empty();
                $('#n2lbox-loading').show();
                $(obj).parent().parent().find(".active-n2l").removeClass("active-n2l").addClass("noactive-n2l");
                $(obj).removeClass("noactive-n2l").addClass("active-n2l");
                $("#n2lbox-submit").attr('disabled', 'disabled');
                var ajlink = $(obj).attr('href');
                $.getJSON(ajlink,function(data){
                        if(data){
                            $('#n2lbox-loading').hide();
                            $('#n2s-imgup-box').append(data.html);
                            window.subscribe = false;
                        }
                    });
            }
        },
        check:function(obj){
            if($(obj).hasClass('checked-albthumbphbox')){
                $("#n2lbox-submit").removeAttr("disabled");
            } else {
                $("#n2lbox-submit").attr('disabled', 'disabled');
            }
        },
        changebg:function(){
            if(window.subscribe===false){
                window.subscribe = true;
                var n2limgid = 0,
                target = $('input[name="active-show"]').val(),
                targetid = $('input[name="active-id"]').val();
                if($('#n2s-imgup-box').find(".checked-albthumbphbox").length)
                    n2limgid = $('#n2s-imgup-box').find(".checked-albthumbphbox").attr('rel');
                if(n2limgid === 0){
                    $.getJSON('/ajax/removephoto',{target:target,obj:targetid},function(data){
                            if(data){
                                if($("#n2s-newloadban").length) $("#n2s-newloadban").remove();
                                $('#n2s-backImg').find('#n2s-moveable').remove();
                                $('#n2s-backImg').after(data.html);
                                $("#browse").css({"top":$("#proPr1").position().top+"px"});
                                $.n2lbox.close();
                                window.subscribe = false;
                            }
                        });
                }else{
                    $.getJSON('/ajax/getphoto',{id:n2limgid,target:target,obj:targetid},function(data){
                            if(data){
                                if($("#n2s-newloadban").length) $("#n2s-newloadban").remove();
                                $('#n2s-backImg').find('#n2s-moveable').remove();
                                $('#n2s-backImg').prepend(data.html).bind(n2s.ajax.banner());
                                $.n2lbox.close();
                                window.subscribe = false;
                            }
                        });
                }
            }
        },
        changeavat:function(){
            if(window.subscribe===false){
                window.subscribe = true;
                var n2limgid = 0,
                remlink = '/ajax/removephoto',
                target = $('input[name="active-show"]').val(),
                targetid = $('input[name="active-id"]').val();
                if($('#n2s-imgup-box').find(".checked-albthumbphbox").length){
                    n2limgid = $('#n2s-imgup-box').find(".checked-albthumbphbox").attr('rel');
                    remlink = '/ajax/getphoto';
                }
                $.getJSON(remlink,{id:n2limgid,target:target,obj:targetid,task:'avatar'},function(data){
                        if(data){
                            $("#n2s-useravatar").find("#avSn2s").remove();
                            $("#n2s-useravatar").prepend(data.html);
                            $.n2lbox.close();
                            window.subscribe = false;
                        }
                    });
            }
        },
        imgup:function(){
            $('#n2s-imgup-mbox').css({'margin-left':$('#n2lbox-menu').width()+41});
            $('#n2lbox-menu').css({'height' : $('.n2lbox-inner').height()-$('#n2lbox-title').height()-21});
            $('#n2s-imgup-box').css({'width':$('.n2lbox-inner').width()-$('#n2lbox-menu').width()-41,'height' : $('.n2lbox-inner').height()-$('#n2lbox-title').height()-51});
            var ajlink = $('#n2lbox-menu').find(".active-n2l").attr('href');
            $.getJSON(ajlink,function(data){
                    if(data.error){
                        $('#n2lbox-menu').find(".active-n2l").remove();
                        var next = $('#n2lbox-menu').find(".noactive-n2l:first");
                        n2s.n2lbox.link(next);
                    } else {
                        $('#n2lbox-loading').hide();
                        $('#n2s-imgup-box').append(data.html);
                    }
                });
        }
    },
    glist:{
        set:function(id,task){
            $('#setGList-loading'+id).show();
            $('#setGList-text'+id).hide();
            $('#setGList-button'+id).hide();
            $.getJSON('/events/ajax/act/glist',{id:id,task:task},
                    function(data){
                        if(data){
                            $('#setGList-loading'+id).hide();
                            if(data.message === 'deljoin'){
                                $('#setGList-button'+id).show();
                            } else {
                                $('#setGList-text'+id).find('b').empty().append(data.message);
                                $('#setGList-text'+id).show();
                            }
                        }
                    }); 
        }
    },
    edit:{
        checkloc:function(){
            var locid=$('#locid'),
            locname=$('#locname'),
            loc=$('#loc');
            if (($("input#albaddress").attr("disabled")&&loc.val()!==locname.val())||($("input#albaddress").attr("disabled")&&loc.val()==="")){
                $("input#albaddress").removeAttr("disabled");
                locid.val("0");
            }
        }
    },
    search:{
        goto: function(event){
            event.preventDefault();
            var inp = $('input[name=indexsearch]');
            if(inp.val()===''){
                inp.focus();
            } else {
                var lnk = '/search/index/q/'+inp.val();
                window.location.replace(lnk);
            }
        },
        auto: function(obj){
            $(document).click(function(event){
                if(($(event.target).parents().index($('#idxS'))===-1)){
                    $('#idxSFld').hide();
                } else {
                    if($('#idxSFldT').find('#idxSChk').length)
                        $('#idxSFld').show();
                }
            });
            $(obj).bind('keyup',function(e){
                if(window.indsrc === false){
                    var q = $(this).val();
                    if(e.which===13 && q.length > 1){
                        window.location.replace("/search/index/q/"+$(obj).val());
                    } else {
                        if(q.length > 1){
                            window.indsrc = true;
                            $.getJSON('/search/ajax',{q:q},
                                function(data){
                                    if(data.error){
                                        $('#idxSFld').hide();
                                        $('#idxSFldT').empty();
                                        window.indsrc = false;
                                    } else {
                                        $('#idxSFldT').empty().prepend(data.html);
                                        $('#idxSFld').show();
                                        $('#idxSFldT').scrollTop(0);
                                        window.indsrc = false;
                                    }
                                });
                        } else {
                            $('#idxSFld').hide();
                            $('#idxSFldT').empty();
                            window.indsrc = false;
                        }
                    }
                }
            });
        }
    },
    lbox:{autoHeight:function(){$('.n2lbox-inner').css({'height':$('#n2lcontResult').height()+30+'px'});}},
    geolocation : {
		autoDetect: function(){
                    var gl;
 
                    function displayPosition(position) {
                        var geocoder;
                        var locality;
                        var admin;
                        var country;
                        var geolink;
                        geocoder = new google.maps.Geocoder();
                        
                        lat = parseFloat(position.coords.latitude);
                        lng = parseFloat(position.coords.longitude);

                        latlng = new google.maps.LatLng(lat, lng);
                        geocoder.geocode({'latLng': latlng}, function(results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                                if (results[0]) {
                                    var pos = results[0].address_components;
                                    for (var i = 0; i < pos.length; i++) {
                                        var object = pos[i];
                                        for (property in object) {
                                            if(object.types[0] === "locality" ){
                                                locality = object.long_name;
                                            }
                                            if(object.types[0] === "administrative_area_level_1" ){
                                                admin = object.long_name;
                                            }
                                            if(object.types[0] === "country" ){
                                                country = object.long_name;
                                            }
                                        }
                                    }
                                    if(typeof country === 'undefined' && typeof admin !== 'undefined'){
                                        country = admin;
                                    }
                                    if(typeof locality !== 'undefined' && typeof country !== 'undefined'){
                                        geolink = locality+','+country;
                                    }else if(typeof locality !== 'undefined'){
                                        geolink = locality;
                                    }else if(typeof country !== 'undefined'){
                                        geolink = country;
                                    }else{
                                        geolink = results[0].formatted_address;
                                    }
                                    window.location = $('a#geoSwitch').attr("href").replace("geolocal", encodeURI(geolink));
                                }
                            } else {
                                    alert("Geocoder failed due to: " + status);
                            }
                        });
                    }

                    function displayError(positionError) {
                        alert("We were unable to retrieve your current position. Please try again.");
                    }

                    try {
                        if (typeof navigator.geolocation === 'undefined'){
                            gl = google.gears.factory.create('beta.geolocation');
                        } else {
                            gl = navigator.geolocation;
                        }
                    } catch(e) {}

                    if (gl) {
                        var options = {
                            enableHighAccuracy: true,
                            timeout: 5000,
                            maximumAge: 0
                        };
                        gl.getCurrentPosition(displayPosition, displayError, options);
                    } else {
                        alert("Geolocation services are not supported by your web browser.");
                    }
                }
            },
    advert: function(){
        if($('#n2s-advert-main').length){
            if($(window).scrollTop() > window.advpos){
                $('#n2s-advert-main').css({'position':'fixed','bottom':'15px','left':window.advposleft+'px'}).addClass('advFix');
                if($('#dateNaviLink').length){
                    if($('#dateNaviLink').height()>$(window).height()-$('#n2s-advert-main').height()-30){
                        $('#n2s-advert-main').hide();
                    } else {
                        if($('#n2s-advert-main').is(':hidden'))
                            $('#n2s-advert-main').show();
                    }
                }
            } else {
                $('#n2s-advert-main').removeAttr('style').removeClass('advFix');
            }
        }
    },
    ajax : {
        banner: function(){
            var off=$("#n2s-backImg").offset(),
                diff=$("#n2s-moveable").attr("rel")-$("#n2s-backImg").height(),
                x1=off.left,
                x2=off.left+$("#n2s-backImg").width(),
                y1=off.top-diff,
                y2=off.top,
                poslink=$('input[name=positbanner]').val();
            $("#n2s-backImg").hover(function() {
                        $("#n2s-rotations").show();
                        $("#n2s-imgupdate").show();
                        $("#n2s-moveable").css({cursor:"row-resize"});
                    },function(){
                        $("#n2s-rotations").hide();
                        $("#n2s-imgupdate").hide();        
                    });
            $("#n2s-moveable").on("mousedown", function() {
                    $(this).css({cursor:"move"});
                    });
            $("#n2s-moveable").on("mouseup", function() {
                    $(this).css({cursor:"row-resize"});
                    });
            $("#n2s-moveable").draggable({
                        drag: function() {
                            $("#n2s-rotations").text("");
                        },
                        stop:function(){
                            $("#n2s-rotations").css({background:'url("/images/ajax/ajax-loader9.gif") no-repeat scroll 6px center rgba(0, 0, 0, 0.5)'});
                            n2s.ajax.position(poslink,Math.round($(this).offset().top-off.top));
                        },scroll: false,
                        containment:[ x1, y1, x2, y2 ],
                        axis: "y"});
        },
        position: function(link,top){
            $.getJSON(link,{top: top},function(data){
                    if(data){
                        $("#n2s-rotations").css({background:'url("/images/roter.png") no-repeat scroll 12px center rgba(0, 0, 0, 0.5)'});
                        $("#n2s-rotations").text(data.message);
                        if(data.error)
                            $("#n2s-moveable").draggable({disabled:true});
                    }
                });
        }
    }
};