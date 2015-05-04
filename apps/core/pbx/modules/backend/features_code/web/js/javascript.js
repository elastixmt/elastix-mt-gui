$(document).ready(function(){
    $('td select[class=select]').change(function(){
        var name=$(this).attr("name");
        var feature=name.substring(0,name.length-5);
        var option=$("select[name="+name+"] option:selected").val();
        //if(option=='ena_default' || option=='disabled'){
            var fc=$(this).parents("tr:first").children("td").children("input:text[name="+feature+"]");
            //obtenemos el valor por default
            var arrAction = new Array();
            arrAction["action"]   = "fc_get_default_code";
            arrAction["menu"]     = "features_code";
            arrAction["rawmode"]  = "yes";
            arrAction["fc_name"]  = feature;
            arrAction["organization"] = $("#organization option:selected").val();
            request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                if(error!=""){
                    alert(error);
                }else{
                    if(option=='ena_custom'){
                        fc.removeAttr("readonly");
                        fc.css({background: '#FFFFFF '});
                        if(arrData["code"]==null || arrData["code"]=="")
                            fc.val("");
                        else
                            fc.val(arrData["code"]);
                    }else{
                        fc.attr("readonly","readonly");
                        if(option=='ena_default'){
                            fc.css({background: '#D8D8D8 '});
                            fc.val(arrData["default_code"]);
                        }else{
                            fc.css({background: '#FFFFCC '});
                            if(arrData["code"]==null || arrData["code"]=="")
                                fc.val(arrData["default_code"]);
                            else
                                fc.val(arrData["code"]);
                        }
                    }
                }
            });
    });
	fc_use_deafault();
});

function fc_use_deafault(){
    $('td').children('select[class=select]').each(function(){
        var name=$(this).attr("name");
        var feature=name.substring(0,name.length-5);
        if($(this).val()=='ena_default' || $(this).val()=='disabled'){
            var fc=$(this).parents("tr:first").children("td").children("input:text[name="+feature+"]");
            fc.attr("readonly","readonly");
            if($(this).val()=='disabled')
                fc.css({background: '#FFFFCC '});
            else
                fc.css({background: '#D8D8D8 '});
        }
    });
    var arrFeature=new Array("pickup","blind_transfer","attended_transfer","one_touch_monitor","disconnect_call");
    for(var i=0; i< arrFeature.length; i++){
        $("input:text[name="+arrFeature[i]+"]").attr("readonly","readonly");
        $("input:text[name="+arrFeature[i]+"]").css({background: '#D8D8D8 '});
    }
}

