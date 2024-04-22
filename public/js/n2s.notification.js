var noti = {
    showMsg: function (){
        var rel = $('#notifList').attr('rel');
        if($('#notifList').is(":visible") && rel === 'msg') {
            $('#notifList').hide();
        }else{
            $('#msgCNT').empty();
            noti.timeoutstop();
            $('#ERMSG').remove();
            $('#notiShowAllLink').attr('href','/community/messages');
            if ($('#msgLST').length) {
                $('#notiLoad').hide();
            }else {
                $('#nList').empty();
                $('#notiLoad').show();
            }
            $('#notifList').show().css('left' , '-90px');
            $('#notifList').attr('rel', 'msg');
            $.getJSON('/community/messages/ajax/task/check',{},
                function(data){
                    if(data.error){
                        $('#notiLoad').hide();
                        $('#nList').empty().append(data.message);
                    } else {
                        $('#notiLoad').hide();
                        $('#nList').empty().append(data.html);
                    }
            });
        }
        
        $(document).click(function(event) {
            if(($(event.target).parents().index($('#notif')) === -1)) {
                if($('#notifList').is(":visible")) {
                    $('#notifList').hide();
                }
            }
        }); 
    },
    showFr: function (){
        var rel = $('#notifList').attr('rel');
        if($('#notifList').is(":visible") && rel === 'fr') {
            $('#notifList').hide();
        }else{
            $('#frCNT').empty();
            noti.timeoutstop();
            $('#ERMSG').remove();
            $('#notiShowAllLink').attr('href','/community/friends');
            if ($('#frLST').length) {
                $('#notiLoad').hide();
            }else {
                $('#nList').empty();
                $('#notiLoad').show();
            }
            $('#notifList').show().css('left' , '-115px');
            $('#notifList').attr('rel', 'fr');
            $.getJSON('/community/friends/ajax/task/check',{},
                function(data){
                    if(data.error){
                        $('#notiLoad').hide();
                        $('#nList').empty().append(data.message);
                    } else {
                        $('#notiLoad').hide();
                        $('#nList').empty().append(data.html);
                    }
            });
        }
        
        $(document).click(function(event) {
            if(($(event.target).parents().index($('#notif')) === -1)) {
                if($('#notifList').is(":visible")) {
                    $('#notifList').hide();
                }
            }
        });
    },
    showGlob: function (){
        var rel = $('#notifList').attr('rel');
        if($('#notifList').is(":visible") && rel === 'glob') {
            $('#notifList').hide();
        }else{
            $('#glCNT').empty();
            noti.timeoutstop();
            $('#ERMSG').remove();
            $('#notiShowAllLink').attr('href','/community/notification');
            if ($('#glLST').length) {
                $('#notiLoad').hide();
            }else {
                $('#nList').empty();
                $('#notiLoad').show();
            }
            $('#notifList').show().css('left' , '-143px');
            $('#notifList').attr('rel', 'glob');
            $.getJSON('/community/notification/ajax/task/check',{},
                function(data){
                    if(data.error){
                        $('#notiLoad').hide();
                        $('#nList').empty().append(data.message);
                    } else {
                        $('#notiLoad').hide();
                        $('#nList').empty().append(data.html);
                    }
            });
            
        }
        
        $(document).click(function(event) {
            if(($(event.target).parents().index($('#notif')) === -1)) {
                if($('#notifList').is(":visible")) {
                    $('#notifList').hide();
                }
            }
        });
    },
    check: function(){
        window.setTimeout("noti.nextcheck();",60000);//60000-1min
    },
    nextcheck: function(){
        $.getJSON('/community/notification/ajax/task/checkcount',{},
                function(data){
                    if(data.newcount){
                        if(data.frcount && $('#frCNT').val() !== data.frcount)
                            $('#frCNT').empty().append(data.frcount);
                        if(data.glcount && $('#glCNT').val() !== data.glcount)
                            $('#glCNT').empty().append(data.glcount);
                        if(data.msgcount && $('#msgCNT').val() !== data.msgcount)
                            $('#msgCNT').empty().append(data.msgcount);
                        if(data.message && (typeof window.ttmsg === 'undefined' || window.ttmsg !== data.message)){
                            noti.timeoutstop();
                            window.tmi = 0;
                            noti.timeoutset(data.message);
                        }
                    }
            });
        noti.check();
    },
    timeoutset: function(msg){
        window.ttmsg = msg;
        var t = [ntit, window.ttmsg];
        var l = t.length;
        var g = window.tmi%l;
        var x = t[g];
        $("title").html(x);

        window.timeoutId = setTimeout("noti.timeoutset(window.ttmsg);", 1000);
        window.tmi++;
    },
    timeoutstop: function(){
        if (typeof window.timeoutId !== 'undefined'){
            clearTimeout(window.timeoutId);
            $("title").html(window.ntit);
        }
    },
    changeVal: function (el,data){
        $(el).empty().append(data);
    }
};