/* Copyright 2012 inDeLA Sagl. All Rights Reserved. */
var actvt = {
    send: function(){
        var content = $('#actUPosSt').val();
        if(content === '' && window.afrto === false){
            $('#actUPosSt').focus();
        } else {
            $('#n2Jh-mAr').hide();
            $('#exALoad2').show();
            var cid = $('input[name="acticid"]').val();
            var target = $('input[name="actitarget"]').val();
            var task = $('input[name="active-task"]').val();
            var id = $('input[name="active-cid"]').val();
            $.getJSON('/ajax/postactiv',{task:task,id:id,cont:content,target:target,cid:cid},//first:first,link:window.allink
            function(data){
                if(data.error){
                    $('#n2Jh-mAr').show();
                    $('#exALoad2').hide();
                    actvt.hideex();
                } else {
                    actvt.getlast();
                }
            });
        }
    },
    getlast: function(){
        var last = $('ul#actstrim').find('li.act_strLi:first').attr('id');
        $.getJSON(window.allink,{last:last,first:true},function(data){
            if(data){
                $('ul#actstrim').find('li:first').after(data.html);
                $('#actUPosSt').addClass('PoAcDy').removeClass('PoAcStat').val('').height(15);
                $('#n2Jh-mAr').show();
                $('#exALoad2').hide();
                actvt.hideex();
            }
        });
    },
    getvideo: function(){
        if($('input[name="videoUrl"]').val() === ''){
            $('input[name="videoUrl"]').focus();
        } else {
            $('#vidAA').hide();
            $('#exALoad').show();
            var url = $('input[name="videoUrl"]').val();
            $.getJSON('/ajax/getvideo',{videoUrl:url},
            function(data){
                if(data){
                    $('#exALoad').hide();
                    $('input[name="videoUrl"]').val('');
                }
                if(data.error){
                    $('#vidAA').show();
                } else {
                    if(data.cid){
                        window.afrto = true;
                        $('input[name="acticid"]').val(data.cid);
                        $('input[name="actitarget"]').val('video');
                    }
                    $('#erhContA').empty().html(data.html);
                }
            });
        }
    },
    addex: function(t){
        if($("div#exACont-"+t).is(":hidden")){
            var list = $('#exA > .exACont:visible');
            list.hide();
            $('#vidAA').show();
            $('#erhContA').empty();
            $("div#exACont-"+t).show();
            $('input[name="acticid"]').val('');
            $('input[name="actitarget"]').val('');
            n2s.plchldr.check();
            $('input[name="videoUrl"]').focus();
        }
    },
    hideex: function(){
        $('#erhContA').empty();
        $('#vidAA').show();
        var list = $('#exA > .exACont:visible');
        list.hide();
        window.afrto = false;
        $('input[name="acticid"]').val('');
        $('input[name="actitarget"]').val('');
    },
    tarresize: function(obj){
        if($(obj).hasClass('PoAcDy')){
            $(obj).bind({
                change: function(){
                    if($(obj).val() === ''){
                        $(obj).addClass('PoAcDy').removeClass('PoAcStat');
                    } else {
                        $(obj).addClass('PoAcStat').removeClass('PoAcDy');
                    }
                }
            });
        }
    },
    getMap: function(id,obj){
        var loadhtml = '<div class="ajaxloadContent'+id+'"><img style="margin: 3px 5px 0px;" src="images/ajax/ajax-loader1.gif" /></div>';
        $('#mp'+obj).empty().removeClass('INFO_map').removeClass('opac').html(loadhtml);
        $.getJSON('/activities/ajax',{act:'map',id:id},
                function(data){
                    if(data)
                        $('#mp'+obj).empty();
                    if(data.error){
                        $('#mp'+obj).html(data.message);
                    } else {
                        $('#mp'+obj).html(data.html);
                    }
                });
    },
    scroll:{
        done:function(){
            $('#actdone').remove();
            actvt.scroll.get(window.allink);
        },
        get:function(flink){
            window.alSuppressScroll = false;
            $('#n2s-msg-loader').show();
            var count = $(window).height();
            var last = $('li.act_strLi:last').attr('id');
            $.getJSON(flink,{count:count,last:last,showusers:window.alstrus,page: window.alstrpage},function(data){
                if(data.error){
                    $('#n2s-msg-loader').hide();
                    if(data.action === 'stop'){
                        window.alSuppressScroll = false;
                    } else {
                        alert(data.message);
                    }
                } else {
                    $('#actstrim').append(data.html);
                    $('#n2s-msg-loader').hide();
                    $(".n2s-tooltip").tipTip({maxWidth: "auto", defaultPosition: "top"});
                    if($('textarea').length){$('textarea').autogrow();}
                    n2s.plchldr.check();
                    if(data.page)
                            window.alstrpage = data.page;
                    if(data.page && data.page === 10){
                        window.alSuppressScroll = false;
                    } else {
                        window.alSuppressScroll = true;
                    }
                }
            });            
        },
        next:function(flink){
            if($('textarea').length){$('textarea').autogrow();}
            n2s.plchldr.check();
            $(".n2s-tooltip").tipTip({maxWidth: "auto", defaultPosition: "top"});
            $(window).scroll(function(){
                if(($(window).scrollTop()>=$('body').height()-$(window).height()-400)&&window.alSuppressScroll===true){
                    actvt.scroll.get(flink);
                }
            });
        },
        first:function(flink){
            if(window.alstr===true){
                window.alstr = false;
                var count = $(window).height();
                $.getJSON(flink,{count: count},function(data){
                    if(data.error){
                        $('#n2s-msg-loader').hide();
                        if(data.action === 'stop'){
                            window.alSuppressScroll = false;
                        } else {
                            alert(data.message);
                        }
                    } else {
                        if(data.users)
                            window.alstrus = data.users;
                        window.alSuppressScroll = true;
                        $('#n2s-msg-loader').hide();
                        $.when($('#actstrim').append(data.html)).done(actvt.scroll.next(flink));
                    }
                });
            }
        }
    }
};