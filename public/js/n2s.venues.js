/* Copyright 2012 inDeLA Sagl. All Rights Reserved. */
var vens = {
    addadminreq: function(user,id,type,task){
        if(window.jrtz === true){
            window.jrtz = false;
            $("#tiptip_holder").hide();
            if($('#reqadhtmlbut').length){
                $('#reqadhtmlbut').empty().append('<img style="margin: 7px 15px 0px 0px;" src="/images/ajax/ajax-loader1.gif" alt=""/>');
            } else {
                $('#dAdRq'+user).empty().append('<img style="margin: 7px 15px 0px 0px;" src="/images/ajax/ajax-loader1.gif" alt=""/>');
            }
            $.getJSON('/venues/ajax',{user:user,id:id,type:type,task:task},
                function(data){
                    if(data){
                        if($('#reqadhtmlbut').length){
                            window.location.reload();
                        } else {
                            $('#dAdRq'+user).empty().append(data.message);
                            $(".n2s-tooltip").tipTip({maxWidth: "auto", defaultPosition: "top"});
                            window.jrtz = true;
                        }
                    }
                });
        }
    },
    accadminreq: function(user,id,type){
        if(window.jrtz === true){
            window.jrtz = false;
            var task = 'accadminreq';
            $('#reqadhtmlbut').empty().append('<img style="margin: 7px 15px 0px 0px;" src="/images/ajax/ajax-loader1.gif" alt=""/>');
            $.getJSON('/venues/ajax',{user:user,id:id,type:type,task:task},
                function(data){
                    if(data.error){
                        $('#reqadhtmlbut').empty().append(data.message);
                        window.jrtz = true;
                    } else {
                        window.location.reload();
                    }
                });
        }
    },
    remselfadmin: function(user,id,type){
        if(window.jrtz === true){
            window.jrtz = false;
            var task = 'remselfadmin';
            $('#exAdConfTo').empty().append('<img style="margin: 7px 15px 0px 0px;" src="/images/ajax/ajax-loader1.gif" alt=""/>');
            $.getJSON('/venues/ajax',{user:user,id:id,type:type,task:task},
                function(data){
                    if(data.error){
                        $('#exAdConfTo').empty().append(data.message);
                        window.jrtz = true;
                    } else {
                        window.location.reload();
                    }
                });
        }
    }
};