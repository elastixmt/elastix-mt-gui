$(document).ready(function(){
    if($("#rg_confirm_call option:selected").val()=="no") 
	    $(".confirm").css("display","none");

    $("#rg_confirm_call").change(function(){
        if($("#rg_confirm_call option:selected").val()=="yes"){
            $(".confirm").css("display","table-row");
        }else{
            $(".confirm").css("display","none");
        }
    });
       
	$("#goto").change(function(){
        var option = $("#goto option:selected").val();
        //obtenemos las opciones correspondientes a esa categoria
        var arrAction = new Array();
        arrAction["action"]   = "get_destination_category";
        arrAction["menu"]     = "ivr";
        arrAction["rawmode"]  = "yes";
        arrAction["organization"]  = $("input[name='organization']").val();
        arrAction["option"]  = option;
        request("index.php",arrAction,false,
        function(arrData,statusResponse,error)
        {
            $("#destination option").remove();
            if(error!=""){
                alert(error);
            }else{
                for( x in arrData ){
                    var valor=arrData[x];
                    $("#destination").append("<option value="+x+">"+valor+"</option>"); 
                }
            }
        });       
    });
    
    $("#pickup_extensions").change(function(){
        var new_member=$("#pickup_extensions option:selected").val();
        if(new_member!="none"){
            var members=$("textarea[name='rg_extensions']").val();
            $("textarea[name='rg_extensions']").val(members+""+new_member+"\n");
            $("#pickup_extensions").val("none");
        }
    });
});





