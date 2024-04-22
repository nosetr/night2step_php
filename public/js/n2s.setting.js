/* Copyright 2012 inDeLA Sagl. All Rights Reserved. */
var sett = {
    getForm: function(obj){
        if($('#cls').length > 0)
            sett.reset();
        if(window.bSett===false){
            window.bSett=true;
            var id = $(obj).attr('id');
            var link = '/community/index/ajax';
            var load = '<img id="setloader" src="/images/ajax/ajax-loader1.gif" alt=""/>';
            var cont = $(obj).parent().parent().find('div.setItemContent');
            $(obj).prepend(load);
            $(obj).parent().parent().attr('id','cls');
            $(cont).find('span.curCont').hide();
            $.getJSON(link,{task:id},function(data){
                if(data){
                    $(cont).find('span.curCont').hide();
                    $('#setloader').remove();
                    $(obj).hide();
                    if(data.error){
                        $(cont).append(data.message);
                    }else{
                        $(cont).append(data.html);
                        n2s.plchldr.check();
                        $(".n2s-tooltip").tipTip({maxWidth: "auto", defaultPosition: "top"});
                    }
                    $("body,html").scrollTop($(cont).offset().top - 25);
                    window.bSett=false;
                }
        });
        }
    },
    reset: function(){
        if($('#cls').length > 0 && window.bSett===false){
            var obj = $('#cls');
            $('form').find('#birthdate').datepicker("destroy");
            $(obj).removeAttr('id').find('span.curCont').show().nextAll().remove();
            $(obj);
            $('a.edSett:hidden').each(function() {
                $(this).show();
            });
        }
    },
    post: function(form,data){
        var link = form.attr('action');
        var name = form.attr('name');
        var load = '<img id="setloader" src="/images/ajax/ajax-loader1.gif" alt=""/>';
        $('#albformarray').empty().append(load);
        $.post(link, data, function(data) {
            if(data.error){
                $('#albformarray').empty().append(data.message);
            } else {
                if(data.success){
                    if(name === 'deactive'){
                        n2s.access.logout();
                    } else {
                        $('#set_'+name).find('span.curCont').empty().append(data.html);
                        sett.reset();
                    }
                } else {
                    $('#albformarray').empty().append(data.html);
                }
            }
        }, 'json');
        return false;
    },
    deactive: function(){
        sett.reset();
        var load = '<img id="setloader" src="/images/ajax/ajax-loader1.gif" alt=""/>';
        $('#dactlink').hide();
        $('#dact').append(load);
        $.getJSON('/community/index/deactive',function(data){
            if(data.error){
                $('#dact').empty().append(data.message);
            } else {
                $('#setloader').hide();
                $('#farul').hide();
                $('.formarround').append(data.html);
                $(".n2s-tooltip").tipTip({maxWidth: "auto", defaultPosition: "top"});
            }
        });
    },
    deactreset: function(){
        $('#dactlink').show();
        $('#setloader').remove();
        $('#fardeac').remove();
        $('#farul').show();
    }
};