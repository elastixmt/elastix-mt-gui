$(document).ready(function(){
    $("input[id^='button#']").click(function() {
        var arrData = $(this).attr('id').split("#");
        var domain  = arrData[1];
        var trunkid = arrData[2];
        var ani_prefix = $("input[id^='text#" + domain + "#" + trunkid + "']").attr("value");
        var arrAction  = new Array();
        arrAction["action"]   = "save_edit";
        arrAction["menu"]     = "ani";
        arrAction["rawmode"]  = "yes";
        arrAction["ani_domain"]    = domain;
        arrAction["ani_trunkid"]   = trunkid;
        arrAction["ani_prefix"]    = ani_prefix;
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                var title = $($("#message_error").children()[0]).find("b");
                var bodym = $("#message_error").children()[1];
                
                if(error){
                    $(title).html("ERROR: ");
                    $(bodym).html(error);
                }
                else{
                    $(title).html("");
                    $(bodym).html(arrData);    
                }                    
                $("#message_error").show();
            });      
    });

    if($("#show_div_error").attr("value")=="1")
        $("#message_error").show();
    else
        $("#message_error").hide();
});






