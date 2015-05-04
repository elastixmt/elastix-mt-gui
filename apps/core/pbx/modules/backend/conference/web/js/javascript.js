var conf_invite=false;
var conf_mute=false;
var conf_kick=false;
$(document).ready(function(){
	if($("#schedule").val()=="off") 
        $(".schedule").css("display","none");
    
    $("input[name='chkoldschedule']").change(function(){
        if($("input[name='chkoldschedule']").is(':checked')){
            $(".schedule").css("display","table-row");
        }else{
            $(".schedule").css("display","none");
        }
    });
    
   if(typeof $("#conf_action").val()!=="undefined"){
        if($("#conf_action").val()=="report")
            getConferenceMemb();
        else
            updateShowCallers();
   }   
});


function getConferenceMemb(){
    var arrAction        = new Array();
    arrAction["action"]  = "getConferenceMemb";
    arrAction["menu"]    = "conference";
    arrAction["limit"]   = $("#grid_limit").val();
    arrAction["offset"]  = $("#grid_offset").val();
    arrAction["state_conf"] = $("#state_conf option:selected").val();
    arrAction["name_conf"]  = $("input[name='name_conf']").val();
    arrAction["type_conf"]  = $("#type_conf option:selected").val();
    arrAction["organization"]  = $("#organization option:selected").val();
    arrAction["rawmode"] = "yes";
    request("index.php",arrAction,true,
        function(arrData,statusResponse,error)
        {
            if(error!=""){
                return true; //algo fallo detenemos la recursividad
            }else{
                if(statusResponse=="CHANGED"){
                    $(".conf_memb").each(function(){
                        var bookId=$(this).attr("id");
                        if(typeof arrData[bookId] !== "undefined"){
                            $(this).parent("td").next("td").html(arrData[bookId]["status"]);
                            $(this).parent("td").html(arrData[bookId]["count"]);
                        }
                    });
                }
                return false; //continua la recursividad
            }
        });
}

function updateShowCallers(){
    var arrAction        = new Array();
    arrAction["action"]  = "updateShowCallers";
    arrAction["menu"]    = "conference";
    arrAction["id_conf"] = $("input[name='id_conf']").val();
    arrAction["organization"]  = $("input[name='organization']").val();
    arrAction["limit"]   = $("#grid_limit").val();
    arrAction["offset"]  = $("#grid_offset").val();
    arrAction["rawmode"] = "yes";
    request("index.php",arrAction,true,
        function(arrData,statusResponse,error)
        {
            if(error!=""){
                alert(error);
                return true; //algo fallo detenemos la recursividad
            }else{
                if(statusResponse=="CHANGED"){
                    //creamos el conjunto de datos a presentar en el reporte
                    var new_rows="";
                    var num_memb=arrData.length;
                    for(var x in arrData){
                        var row = "<tr class='neo-table-data-row'>";
                        if(num_memb = x+1)
                            last='table-data_last_row';
                        else
                            last='table-data';
                        col="";
                        col=col+createtd(arrData[x][0],last);
                        col=col+createtd(arrData[x][1],last);
                        col=col+createtd(arrData[x][2],last);
                        col=col+createtd(arrData[x][3],last);
                        col=col+createtd(arrData[x][4],last);
                        col=col+createtd(arrData[x][5],last);
                        new_rows=new_rows+row+col+"</tr>";
                    } 
                    //removemos todas las filas en ese momento
                    //no soportado en otros temas de elastix
                    $('tr[class*=table-data]').remove();
                    $('input[name=mute_caller]').parent().parent().parent().append(new_rows);
                }
                return false; //continua la recursividad
            }
        });
}

function createtd(contenido,last){
    return "<td class='neo-table-data-row "+last+"'>"+contenido+"</td>";
}

function inviteCaller(){
    if(conf_invite==false){
        conf_invite=true;
        var arrAction        = new Array();
        arrAction["action"]  = "inviteCaller";
        arrAction["menu"]    = "conference";
        arrAction["id_conf"] = $("input[name='id_conf']").val();
        arrAction["organization"]  = $("input[name='organization']").val();
        arrAction["exten"]   = $("#invite_caller option:selected").val();
        arrAction["rawmode"] = "yes";
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                conf_invite=false;
                $("#invite_caller").val("");
                if(error!=""){
                    alert(error);
                }else{
                    alert(arrData);
                }
            });
    }else{
        alert("Application busy");
    }
} 

function muteAll(){
    if(conf_mute==false){
        conf_mute=true;
        var arrAction        = new Array();
        arrAction["action"]  = "muteCallers";
        arrAction["menu"]    = "conference";
        arrAction["id_conf"] = $("input[name='id_conf']").val();
        arrAction["organization"]  = $("input[name='organization']").val();
        arrAction["type"] = "all";
        arrAction["rawmode"] = "yes";
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                conf_mute=false;
                if(error!=""){
                    alert(error);
                }else{
                    alert(arrData);
                }
            });
    }else{
        alert("Application busy");
    }
}

function muteCaller(){
    if(conf_mute==false){
        conf_mute=true;
        var arrAction        = new Array();
        arrAction["action"]  = "muteCallers";
        arrAction["menu"]    = "conference";
        arrAction["id_conf"] = $("input[name='id_conf']").val();
        arrAction["organization"]  = $("input[name='organization']").val();
        arrAction["type"] = "some";
        arrAction["rawmode"] = "yes";
        $(".conf_mute").each(function(){
            var useid=$(this).attr("name").substr(5);
            if($(this).is(':checked')){
                arrAction["mute_"+useid]="on";
            }else{
                arrAction["mute_"+useid]="off";
            }
        });
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                conf_mute=false;
                if(error!=""){
                    alert(error);
                }else{
                    alert(arrData);
                }
            });
    }else{
        alert("Application busy");
    }
}

function kickAll(message){
    if(conf_kick==false){
        var agree=confirm(message);
        if (agree){
            conf_kick=true;
            var arrAction        = new Array();
            arrAction["action"]  = "kickCallers";
            arrAction["menu"]    = "conference";
            arrAction["id_conf"] = $("input[name='id_conf']").val();
            arrAction["organization"]  = $("input[name='organization']").val();
            arrAction["type"] = "all";
            arrAction["rawmode"] = "yes";
            request("index.php",arrAction,false,
                function(arrData,statusResponse,error)
                {
                    conf_kick=false;
                    if(error!=""){
                        alert(error);
                    }else{
                        alert(arrData);
                    }
                });
        }
    }else{
        alert("Application busy");
    }
}

function kickCaller(message){
    if(conf_kick==false){
        var agree=confirm(message);
        if (agree){
            conf_kick=true;
            var arrAction        = new Array();
            arrAction["action"]  = "kickCallers";
            arrAction["menu"]    = "conference";
            arrAction["id_conf"] = $("input[name='id_conf']").val();
            arrAction["organization"]  = $("input[name='organization']").val();
            arrAction["type"] = "some";
            arrAction["rawmode"] = "yes";
            $(".conf_kick").each(function(){
                var useid=$(this).attr("name").substr(5);
                if($(this).is(':checked')){
                    arrAction["kick_"+useid]=useid;
                }
            });
            request("index.php",arrAction,false,
                function(arrData,statusResponse,error)
                {
                    conf_kick=false;
                    if(error!=""){
                        alert(error);
                    }else{
                        alert(arrData);
                    }
                });
        }
    }else{
        alert("Application busy");
    }
}