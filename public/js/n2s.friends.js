/* Copyright 2012 inDeLA Sagl. All Rights Reserved. */
var friends = {
    delReq: function (id){
        var loadIMG = '<img id="lIMG'+id+'" style="height: 8px; margin-right: 5px;" src="/images/ajax/ajax-loader1.gif" alt=""/>';
        $('#delReq'+id).prepend(loadIMG);
        $.getJSON('/community/friends/ajax/task/remove',{id: id},
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
        //window.setTimeout("friends.nextik("+id+");",1000);
    },
    
    rejectReq: function (id){
        var loadIMG = '<img id="lIMG'+id+'" style="height: 8px; margin-right: 5px;" src="/images/ajax/ajax-loader1.gif" alt=""/>';
        $('#rejectReq'+id).prepend(loadIMG);
        $.getJSON('/community/friends/ajax/task/reject',{id: id},
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
    
    restorAc: function (id){
        $.getJSON('/community/friends/ajax/task/restore',{id: id},
                function(data){
                    if(data.error){
                        $('#phRestore'+id).prepend(data.message);
                    } else {
                        $('#phRestore'+id).fadeOut("fast",function(){
                            $('#origItem'+id).fadeIn();
                        });
                    }
            });      
    },
    
    restorRq: function (id){
        $.getJSON('/community/friends/ajax/task/restorereject',{id: id},
                function(data){
                    if(data.error){
                        $('#phRestore'+id).prepend(data.message);
                    } else {
                        $('#phRestore'+id).fadeOut("fast",function(){
                            $('#origItem'+id).fadeIn();
                        });
                    }
            });      
    },
    
    addFr: function (id){
        var loadIMG = '<img id="lIMG'+id+'" style="height: 8px; margin-right: 5px;" src="/images/ajax/ajax-loader1.gif" alt=""/>';
        $('#addReq'+id).prepend(loadIMG);
        $.getJSON('/community/friends/ajax/task/add',{id: id},
                function(data){
                    if(data.error){
                        $('#origItem'+id).prepend(data.message);
                        $('#lIMG'+id).remove();
                    } else {
                        $('#msgReqTXT'+id).fadeOut("fast",function(){
                            $('#controllRq'+id).empty();
                            $('#msgReqSuc'+id).fadeIn();
                        });
                    }
            });      
    }
};