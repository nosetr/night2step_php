var comment = {
    fixIt: function (id){
        if ($(window).scrollTop() > $('#comBox'+id).offset().top){
            var comEntHeight = $('#fixAr'+id).height()+'px';
            $('#fixAr'+id).css({'position': 'fixed','top':'0','z-index':'10','width':window.comBoxWidth});
            $('#comBox'+id).css({'padding-top': comEntHeight});
        } else {
            $('#fixAr'+id).css({'position': 'relative'});
            $('#comBox'+id).css({'padding-top': '0'});
        }
    },
    set: function (commID,type,comm){
        if(window.commaction===true){
            window.commaction=false;
            var link = '/comment/index/';
            $('.ajaxloadTop'+commID).show();
            $.getJSON(link,{id: commID, type: type, string: comm},
                function(data){
                    if(data.error){
                        $('.ajaxloadTop'+commID).hide();
                        if(data.action !== 'stop'){
                            alert(data.message);
                        }
                    } else {
                        $('.n2s-commentsBox textarea.'+commID).val('').height(15);
                        $('.comBoxEnterAr'+commID).after(data.html);
                        $('.ajaxloadTop'+commID).hide();
                        $(".n2s-tooltip").tipTip({maxWidth: "auto", defaultPosition: "top"});
                        window.commaction=true;
                    }
            });
            $.ajaxSetup({cache: false});
        }
    },
    more: function (obj){
        if(window.commaction===true){
            window.commaction=false;
            var last = $(obj).attr("last");
            var type = $(obj).attr("rel");
            var commID = $(obj).attr("id");
            var link = '/comment/index/';
            $(obj).hide();
            $('.ajaxloadBottom'+commID).show();
            $.getJSON(link,{id: commID, type: type, last: last},
                function(data){
                    if(data.error){
                        $('.ajaxloadBottom'+commID).hide();
                        if(data.action !== 'stop'){
                            alert(data.message);
                        }
                    } else {
                        $('.ajaxloadBottom'+commID).hide();
                        $(obj).parent().append(data.html).attr('id', 'comListAll'+commID);
                        $(".n2s-tooltip").tipTip({maxWidth: "auto", defaultPosition: "top"});
                        window.commaction=true;
                    }
            });
            $.ajaxSetup({cache: false});
        }
    },
    comdel: function (commID){
        if(window.commaction===true){
            window.commaction=false;
            var link = '/comment/ajax/';
            var loadhtml = '<div class="ajaxloadContent'+commID+'"><img style="margin: 3px 5px 0px;" src="images/ajax/ajax-loader1.gif" alt=""/></div>';
            $('.comCont'+commID).after(loadhtml);
            $('.comCont'+commID).hide();
            $.getJSON(link,{task: 'comdel', comid: commID},
                function(data){
                    if(data.error){
                        $('.comCont'+commID).remove();
                        $('.ajaxloadContent'+commID).remove();
                        if(data.action !== 'stop'){
                            alert(data.message);
                        }
                    } else {
                        $('.ajaxloadContent'+commID).empty().html(data.html);
                        window.commaction=true;
                    }
            });
            $.ajaxSetup({cache: false});
        }
    },
    comspam: function (commID){
        if(window.commaction===true){
            window.commaction=false;
            var link = '/comment/ajax/';
            var loadhtml = '<div class="ajaxloadContent'+commID+'"><img style="margin: 3px 5px 0px;" src="images/ajax/ajax-loader1.gif" alt=""/></div>';
            $('.comCont'+commID).after(loadhtml);
            $('.comCont'+commID).hide();
            $.getJSON(link,{task: 'comspam', comid: commID},
                function(data){
                    if(data.error){
                        $('.comCont'+commID).remove();
                        $('.ajaxloadContent'+commID).remove();
                        if(data.action !== 'stop'){
                            alert(data.message);
                        }
                    } else {
                        $('.comCont'+commID).remove();
                        $('.ajaxloadContent'+commID).remove();
                        window.commaction=true;
                    }
            });
            $.ajaxSetup({cache: false});
        }
    },
    comrestore: function (commID){
        if(window.commaction===true){
            window.commaction=false;
            var link = '/comment/ajax/';
            var loadhtml = '<img style="margin: 3px 5px 0px;" src="images/ajax/ajax-loader1.gif" alt=""/>';
            $('.ajaxloadContent'+commID).html(loadhtml);
            $.getJSON(link,{task: 'comrestore', comid: commID},
                function(data){
                    if(data.error){
                        $('.comCont'+commID).show();
                        if(data.action !== 'stop'){
                            alert(data.message);
                        }
                    } else {
                        $('.ajaxloadContent'+commID).remove();
                        $('.comCont'+commID).show();
                        window.commaction=true;
                    }
            });
            $.ajaxSetup({cache: false});
        }
    },
    onchange: function (obj){
        $(obj).change(function() {
        }).keypress(function(e) {
            var comm = $.trim($(this).val());
            var type = $(this).attr("rel");
            var commID = $(this).attr("id");
            if (e.which === 13 && comm !== ''){
                comment.set(commID,type,comm);
            }
        });
    }
};