var list = {
    fadOutAlbs: function (id){
        $('#mBox' + id).fadeOut('fast', function() {
            window.unvisib = id;
                window.navData = "navM"+id;
            $('html,body').animate({scrollTop: $(window).scrollTop()+5},'slow');
            $("#"+id).attr("onclick", "list.fadInAlbs('"+id+"')");
        });
    },
    fadInAlbs: function (id){
        $('#mBox' + id).fadeIn('fast', function() {
            window.unvisib = "";
            $('html,body').animate({scrollTop: $("#"+id).offset().top-10},'slow');
            $("#"+id).attr("onclick", "list.fadOutAlbs('"+id+"')");
        });
    },
    goToByScroll: function (to){
        $("html,body").animate({scrollTop: $("#"+to).offset().top-10},"slow");
        if($("#mBox"+to).is(":hidden") && $("#mBox"+to).length){
            this.fadInAlbs(to);
        }
    },
    hover: function (){
        $('#dateNaviLink .navHover').hover(function(){
            if ($(this).find("ul .activeNav").length === 0){
                $(this).find("ul").show();
                if($('.advFix').length &&
                    $('#dateNaviLink').height()>$(window).height()-$('.advFix').height()-30
                ){
                    $('.advFix').hide().addClass("hideHover");
                }
            }
        }, function(){
            if ($(this).find("ul .activeNav").length === 0){
                $(this).find("ul").hide();
                if($('.hideHover').length && $('.hideHover').attr('rel')!=='hidden'){
                    $('.advFix').show().removeClass("hideHover");
                }
            }
        });
    },
    navi: function (){
        if($('#dateNaviLink').length){
            $(".mBox:in-viewport").each(function() {
                if (window.unvisib !== $(this).attr("rel") && $(this).is(':visible')){
                    window.navData = "navM"+$(this).attr("rel");
                    $("#"+window.navData).addClass("activeNav");
                    $("#"+window.navData).parent().parent().removeClass("disabledY");
                    if($("#"+window.navData).parent().is(':hidden'))
                        $("#"+window.navData).parent().show();
                }
            });
            $(".mBox:hidden").each(function() {
                window.navData = "navM"+$(this).attr("rel");
                $("#"+window.navData).removeClass("activeNav");
                var parentUL = $("#"+window.navData).parent();
                var found = parentUL.find(".activeNav");
                if (parentUL.is(':visible') && found.length === 0){
                    parentUL.hide();
                    $("#"+window.navData).parent().parent().addClass("disabledY");
                    list.hover();
                }
            });
            $(".mBox:above-the-top").each(function() {
                window.navData = "navM"+$(this).attr("rel");
                $("#"+window.navData).removeClass("activeNav");
                var parentUL = $("#"+window.navData).parent();
                var found = parentUL.find(".activeNav");
                if (parentUL.is(':visible') && found.length === 0){
                    parentUL.hide();
                    $("#"+window.navData).parent().parent().addClass("disabledY");
                    list.hover();
                }
            });
            $(".mBox:below-the-fold").each(function() {
                window.navData = "navM"+$(this).attr("rel");
                $("#"+window.navData).removeClass("activeNav");
                var parentUL = $("#"+window.navData).parent();
                var found = parentUL.find(".activeNav");
                if (parentUL.is(':visible') && found.length === 0){
                    parentUL.hide();
                    $("#"+window.navData).parent().parent().addClass("disabledY");
                    list.hover();
                }
            });
        }
    },
    next: function (link){
        var minLon = $('#minLon').val();
        var maxLon = $('#maxLon').val();
        var minLat = $('#minLat').val();
        var maxLat = $('#maxLat').val();
        window.aSuppressScroll = true;
        $.getJSON(link,{minlon: minLon, maxlon: maxLon, minlat: minLat, maxlat: maxLat},
            function(data){
                if(data.error){
                    $('#ajaxload').hide();
                    if(data.action === 'stop'){
                        window.bSuppressScroll = true;
                    } else {
                        alert(data.message);
                    }
                } else {
                    if(data.radius)
                        $('#radius').html(data.radius);
                    $('#ajaxload').hide();
                    $('#timeArrey').append(data.html);
                    if(data.nav && $('#dateNaviLink').length){
                        if(data.newnav){
                            $('#dateNaviLink').append(data.nav);
                        }else{
                            $('#dateNaviLink ul:last').append(data.nav);
                        }
                    }
                    window.bSuppressScroll = false;
                }
                list.navi();
        });
    },
    first: function (flink){
        if(window.slistsearch===true){
            window.flistsearch=false;
            window.slistsearch=false;
            $.getJSON(flink,{first: true},function(data){
                    if(data.error){
                        $('#ajaxload').hide();
                        if(data.action === 'stop'){
                            window.bSuppressScroll = true;
                            if(data.message){
                                $('#timeMessage').html(data.message);
                            }
                            list.navi();
                        } else {
                            alert(data.message);
                        }
                    } else {
                        if(data.radius)
                            $('#radius').html(data.radius);
                        var linkload = flink+'/count/'+$(window).height();
                        if(data.message){
                            $('#timeMessage').html(data.message);
                        }
                        $.when($('#timeArrey').append(data.html)).done(list.next(linkload));
                    }
                });
        }
    }
};
var geosearch = {
    find: function (flink){
        var geocoder;
        var geolink;
        var geoval=$('input[name=geosearch]').val();
        if(geoval===''){
            $('input[name=geosearch]').focus();
        }else{
            window.slistsearch=true;
            geocoder = new google.maps.Geocoder();
            geocoder.geocode({address: geoval}, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                        if (results[0]) {
                            geolink = results[0].formatted_address;
                            $('#geosearchpos').html(geolink);
                            //window.location = $('a#geoSwitch').attr("href").replace("geolocal", encodeURI(geolink));
                            $('#geosearchinput').hide();
                            $('#resetLocButton').show();
                            $("#geosearchposition").show();
                            $('#ajaxload').show();
                            $('#timeMessage').empty();
                            $('#timeArrey').empty();
                            if($('#dateNaviLink').length)
                                $('#dateNaviLink').empty();
                            if(window.arhref){
                                if(window.gSearchDef){
                                    $('#archiveLink').parent().attr('href', window.arhref.replace(window.gSearchDef,encodeURI(geolink)));
                                } else {
                                    $('#archiveLink').parent().attr('href', window.arhref+encodeURI(geolink));
                                }
                            }
                            list.first(flink+'/geosearch/'+geolink);
                            $('input[name=geosearch]').val('');
                        }
                } else {
                        alert("Geocoder failed due to: " + status);
                }
            });
        }
    },
    reset: function (flink){
        var geocoder;
        var geolink;
        var geoval=$('input[name=UserRegionMain]').val();
        window.slistsearch=true;
        geocoder = new google.maps.Geocoder();
        geocoder.geocode({address: geoval}, function(results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        geolink = results[0].formatted_address;
                        $('#geosearchpos').html(geolink);
                        //window.location = $('a#geoSwitch').attr("href").replace("geolocal", encodeURI(geolink));
                        $('#geosearchinput').hide();
                        $('#resetLocButton').hide();
                        $("#geosearchposition").show();
                        $('#ajaxload').show();
                        $('#timeMessage').empty();
                        $('#timeArrey').empty();
                        if($('#dateNaviLink').length)
                            $('#dateNaviLink').empty();
                        if(window.arhref){
                            if(window.gSearchDef){
                                $('#archiveLink').parent().attr('href', window.arhref.replace(window.gSearchDef,encodeURI(geolink)));
                            } else {
                                $('#archiveLink').parent().attr('href', window.arhref+encodeURI(geolink));
                            }
                        }
                        list.first(flink+'/geosearch/'+geolink);
                        $('input[name=geosearch]').val('');
                    }
            } else {
                    alert("Geocoder failed due to: " + status);
            }
            });
    }
};