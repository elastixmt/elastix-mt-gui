$(document).ready(function(){
    $("td select[name=type]").change(function(){
        var type=$("select[name=type] option:selected").val();
        if(type=="analog"){
            $(".type_did").css("display","table-row");
        }else{
            $(".type_did").css("display","none");
        }
    });
    
    $("td select[name=channel]").change(function(){
        var channel=$("select[name=channel] option:selected").val();
        if(channel!="none" && channel!=""){
            var act_chans=$("#select_chans").val();
            //se agrega el elemento a la lista
            $("select[name='arr_channel']").append("<option value="+channel+">"+channel+"</option>");
            //se quita el elemento de la lista de seleccion
            $("select[name=channel] option:selected").remove();
            
            $("#select_chans").val(act_chans+channel+",");
            $("select[name=channel]").val("none");
        }
    });
});

function validateSubmit(){
    var message = "";
    var arrAction = new Array();
    arrAction["menu"]="did";
    arrAction["action"]="validate_delete";
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error!=""){
                alert(error);
                return false;
            }else{
                message=arrData;
            }
    });
    var agree=confirm(message);
    if (agree)
        return true ;
    else
        return false ;
}

function select_country()
{
    var country=$("#country").find('option:selected').val();
    var message = "";
    var arrAction = new Array();
    arrAction["menu"]="organization";
    arrAction["action"]="get_country_code";
    arrAction["country"]=country;
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error!=""){
                alert(error);
            }else{
                $('input[name="country_code"]').val(arrData);
            }
    });
}

function quitar_channel(){
    var channel=$("select[name=arr_channel] option:selected").val();
    //se quita el elemento de la lista de seleccionados
    $("select[name=arr_channel] option:selected").remove();
    //se agrega el elemento de la lista de canales disponibles
    $("select[name='channel']").append("<option value="+channel+">"+channel+"</option>");
    var val=$("#select_chans").val();
    var arrVal=val.split(",");
    var option="";
    for (x in arrVal){
        if(arrVal[x]!=channel && arrVal[x]!="")
            option += arrVal[x]+",";
    }
    $("#select_chans").val(option);
}

function mostrar_select_chans(){
    var val=$("#select_chans").val();
    var arrVal=val.split(",");
    
    for (x in arrVal){
        if(arrVal[x]!=""){
            $("select[name='arr_channel']").append("<option value="+arrVal[x]+">"+arrVal[x]+"</option>");
        }
    }
    
    var chann=$("select[name='channel']");
    var options = $('option', chann);
        options.each(function() {
            if(arrVal.indexOf($(this).text())!=-1){
                $("select[name=channel] option[value='"+$(this).text()+"']").remove();
            }
        });
}
