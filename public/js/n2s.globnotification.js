var glnoti = {
    first: function (){
        var lim = $(window).height();
        $.getJSON('/community/notification/list',{limit: lim},
                function(data){
                    if(data.error){
                        if(data.action === 'stop'){
                            window.bSuppressScroll = true;
                            if(data.message){
                                $('#messList').prepend(data.message);
                        $('#ajaxload').hide();
                            }
                        } else {
                            $('#messList').prepend(data.message);
                        $('#ajaxload').hide();
                        }
                    } else {
                        $('#ajaxload').hide();
                        $('#messList').append(data.html);
                        window.aSuppressScroll = true;
                    }
            });
    },
    
    next: function (){
        var lastid = $('#messList .newsfeed-item').last().attr('id');
        var lim = $(window).height();
        $('#ajaxload').show();
        $.getJSON('/community/notification/list',{limit: lim,last: lastid},
                function(data){
                    if(data.error){
                        $('#ajaxload').hide();
                        if(data.action === 'stop'){
                            window.bSuppressScroll = true;
                            if(data.message){
                                $('#messList').prepend(data.message);
                            }
                        } else {
                            $('#messList').prepend(data.message);
                        }
                    } else {
                        $('#ajaxload').hide();
                        $('#messList').append(data.html);
                        window.bSuppressScroll = false;
                    }
            });
    },
    
    del: function (id){
        var loadIMG = '<img id="lIMG'+id+'" style="height: 6px; margin-right: 5px;" src="/images/ajax/ajax-loader(1).gif" />';
        $('#clickDel'+id).prepend(loadIMG);
        $.getJSON('/community/notification/ajax/task/remove',{id: id},
                function(data){
                    if(data.error){
                        $('#origItem'+id).prepend(data.message);
                        $('#lIMG'+id).remove();
                    } else {
                        $('#origItem'+id).fadeOut("fast",function(){
                            $('#lIMG'+id).remove();
                            $('#phRestore'+id).fadeIn();
                        });
                    }
            });
    },
    
    restor: function (id){
        $.getJSON('/community/notification/ajax/task/restore',{id: id},
                function(data){
                    if(data.error){
                        $('#phRestore'+id).prepend(data.message);
                    } else {
                        $('#phRestore'+id).fadeOut("fast",function(){
                            $('#origItem'+id).fadeIn();
                        });
                    }
            });
    }
};