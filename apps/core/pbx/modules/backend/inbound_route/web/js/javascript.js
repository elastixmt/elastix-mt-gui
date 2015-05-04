$(document).ready(function(){
    if($("#primanager option:selected").val()=="no") 
	    $(".privacy").css("display","none");
    
    if($("#fax_detect option:selected").val()=="no") 
        $(".fax_detect").css("display","none");

    $("#primanager").change(function(){
        if($("#primanager option:selected").val()=="yes"){
            $(".privacy").css("display","table-row");
        }else{
            $(".privacy").css("display","none");
        }
    });
    
    $("#fax_detect").change(function(){
        if($("#fax_detect option:selected").val()=="yes"){
            $(".fax_detect").css("display","table-row");
        }else{
            $(".fax_detect").css("display","none");
        }
    });
    
	$("#goto").change(function(){
        var option = $("#goto option:selected").val();
        var domain = $("input[name=organization]").val();
        //obtenemos las opciones correspondientes a esa categoria
        var arrAction = new Array();
        arrAction["action"]   = "get_destination_category";
        arrAction["menu"]     = "ivr";
        arrAction["rawmode"]  = "yes";
        arrAction["organization"]  = domain;
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
});





