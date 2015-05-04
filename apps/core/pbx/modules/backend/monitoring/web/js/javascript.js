audiojs.events.ready(function() {
       // var i=0;
    var as = audiojs.createAll({
        useFlash: false,
        trackEnded: function() {
        $('.audiojs').css('visibility','hidden');
        },
    });
        //i=0;
    $('.single').click(function(e) { 
        var flag_array = 0;
        if(typeof  player!== "undefined" && player){
            as[player].pause();
            as[player].load();
            $("#audiojs_wrapper"+player).css("visibility","hidden");
        }
        var fload =0;
            player = $(this).attr("id");
        $("#audiojs_wrapper"+player).css("visibility","visible");
        $("#audiojs_wrapper"+player).removeClass("error");
        as[player].load($('span', this).attr('data-src'));
        as[player].play();
    });

    $('.single2').live("click", function(e) { 
        $(".audiojs").css("visibility","hidden");
        $("#audiojs_wrapper0").css("visibility","visible");
        as[0].load($('span', this).attr('data-src'));
        as[0].play();
    });
});