/* Copyright 2012 inDeLA Sagl. All Rights Reserved. */
var srch = {
    goto: function(event){
        event.preventDefault();
        var inp = $('input[name=multisearch]');
        if(inp.val()===''){
            inp.focus();
        } else {
            var lnk = '/search/index/q/'+inp.val();
            window.location.replace(lnk);
        }
    },
    press: function(obj){
        $(obj).bind('keyup',function(e){
            var q = $(this).val();
            if(e.which===13 && q.length > 1){
                window.location.replace("/search/index/q/"+$(obj).val());
            }
        });
    }
};

