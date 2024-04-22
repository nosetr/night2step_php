/* Copyright 2012 inDeLA Sagl. All Rights Reserved. */
var message = {
    sendtxt: function (view){
        var txt = $('#messText').val();
        if (txt === ''){
            $('#messText').focus();
        } else {
            $('#secondAjaxload').show();
            $('#im_send').hide();
            $.getJSON('/community/messages/ajax/task/send',{user: view,msg: txt},
                function(data){
                    if(data){
                        if(data.error){
                            $('#secondAjaxload').hide();
                            $('#im_send').show();
                            $('#messText').val('').height(39);
                            $('#errorMsg').empty().append(data.message);
                        } else {
                            $('#secondAjaxload').hide();
                            $('#im_send').show();
                            $('#messList').prepend(data.html);
                            $('#messText').val('').height(39);
                            $('#messText').focus();
                        }
                        $('body,html').animate({scrollTop: $('h1').offset().top}, 800);
                    }
            });
        }        
    },
    next: function (link){
        var lastid = $('#messList .newsfeed-item').last().attr('id');
        var lim = $(window).height();
        if ($('#UsersCheck').length) {
            var list = $('#UsersCheck').val();
        } else {
            var list = false;
        }
        $.getJSON(link,{limit: lim,last: lastid,ulist: list},
            function(data){
                if(data.error){
                    $('#ajaxload').hide();
                    if(data.action === 'stop'){
                        window.bSuppressScroll = true;
                    } else {
                        alert(data.message);
                    }
                } else {
                    $('#ajaxload').hide();
                    $('#messList').append(data.html);
                    window.bSuppressScroll = false;
                    if(data.userlist && $('#UsersCheck').length){
                        var lid = list + data.userlist;
                        $('#UsersCheck').val(lid);
                    }
                }
        });
    },
    first: function (flink){
        var lim = $(window).height();
        $.getJSON(flink,{limit: lim},function(data){
                if(data.error){
                    $('#ajaxload').hide();
                    if(data.action === 'stop'){
                        window.bSuppressScroll = true;
                        if(data.message){
                            if($('#timeMessage').length){
                                $('#timeMessage').append(data.message);
                            } else {
                                $('#timeArrey').append(data.message);
                            }
                        }
                    } else {
                        alert(data.message);
                    }
                } else {
                    if(data.html){
                        $('#ajaxload').hide();
                        $('#timeArrey').append(data.html);
                        if(data.userlist){
                            $("#UsersCheck").val(data.userlist);
                        }
                        window.aSuppressScroll = true;
                    }
                }
            });
    }
};