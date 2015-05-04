$(document).ready(function(){
    $("[id^=goto_]").change(function(){
        var cond = $(this).parent("td:first").attr("class");
        if(cond=="match")
            var ap="m";
        else
            var ap="f";
        var option = $("#goto_"+ap+" option:selected").val();
        //obtenemos las opciones correspondientes a esa categoria
        var arrAction = new Array();
        arrAction["action"]   = "get_destination_category";
        arrAction["menu"]     = "ivr";
        arrAction["rawmode"]  = "yes";
        arrAction["option"]  = option;
        arrAction["organization"]  = $("input[name='organization']").val();
        request("index.php",arrAction,false,
        function(arrData,statusResponse,error)
        {
            $("#destination_"+ap+" option").remove();
            if(error!=""){
                alert(error);
            }else{
                for( x in arrData ){
                    var valor=arrData[x];
                    $("#destination_"+ap).append("<option value="+x+">"+valor+"</option>"); 
                }
            }
        });       
    });
});




