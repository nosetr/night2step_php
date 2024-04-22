/* Copyright 2012 inDeLA Sagl. All Rights Reserved. */
var events = {
    goToByScroll: function (id,page,task){
        if(window.bSuppressScroll===true){
            window.bSuppressScroll=false;
            $('#ajaxload').show();
            $.getJSON('/events/userevents',{page:page,id:id,task:task},
                function(data){
                    if(data.error){
                        $('#ajaxload').hide();
                        window.bSuppressScroll=false;
                    } else {
                        $('#ajaxload').hide();
                        $('#uEvList').append(data.html);
                        window.bSuppressScroll=true;
                    }
                });
        }
    },
    join: function(id,act){
        $('#joinEvent-loading').show();
        $('#joinEvent-text').hide();
        $('#joinEvent-buttons').hide();
        $.getJSON('/events/ajax',{id:id,act:act},
                function(data){
                    if(data){
                        $('#joinEvent-loading').hide();
                        if(data.message === 'deljoin'){
                            $('#joinEvent-buttons').show();
                        } else {
                            $('#joinEvent-text b').empty().append(data.message);
                            $('#joinEvent-text').show();
                        }
                        $('#n2s-evJoin-count').html(data.count);
                        $('#n2s-evJoin-list-array').remove();
                    }
                });
    },
    glist: function(id,task){
        $('#EventGList-loading').show();
        $('#EventGList-text').hide();
        $('#EventGList-button').hide();
        $.getJSON('/events/ajax/act/glist',{id:id,task:task},
                function(data){
                    if(data){
                        $('#EventGList-loading').hide();
                        if(data.message === 'deljoin'){
                            $('#EventGList-button').show();
                        } else {
                            $('#EventGList-text b').empty().append(data.message);
                            $('#EventGList-text').show();
                            if(!data.error)
                                events.join(id,'join');
                        }
                    }
                });        
    },
    goToByScrollGList: function (id){
        if(window.bSuppressScroll===true){
            window.bSuppressScroll=false;
            $('#ajaxload').show();
            $.getJSON('/events/glist',{page:window.page,id:id},
                function(data){
                    if(data.error){
                        $('#ajaxload').hide();
                        window.bSuppressScroll=false;
                    } else {
                        $('#ajaxload').hide();
                        $('#messList').append(data.html);
                        window.page = window.page+1;
                        window.bSuppressScroll=true;
                    }
                });
        }
    }
};