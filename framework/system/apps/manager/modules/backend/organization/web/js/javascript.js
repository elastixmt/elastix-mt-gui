$(document).ready(function(){
    $("td select[name=did]").change(function(){
        var did=$("select[name=did] option:selected").val();
        if(did!="none" && did!=""){
            var act_dids=$("#select_dids").val();
            //se agrega el elemento a la lista
            $("select[name='arr_did']").append("<option value="+did+">"+did+"</option>");
            //se quita el elemento de la lista de seleccion
            $("select[name=did] option:selected").remove();
            
            $("#select_dids").val(act_dids+did+",");
            $("select[name=did]").val("none");
        }
    });
    
    $('.org_chk_limits').each(function(){
        if($(this).is(':checked')){
            var txtname=$(this).attr('name').replace("_chk","");
            $("input[name="+txtname+"]").prop("disabled",true);
        }
        if($("input[name='org_mode']").val()=="view"){
            $(this).prop("disabled",true);
        }else
            $(this).prop("disabled",false);
    });
});
//did assign function
$(function() {
    $( "#sortable1,#sortable2" ).sortable({
        connectWith: ".connectedSortable"
    }).disableSelection();
    $( "#sortable2" ).on("sortreceive", function( event, ui ) {
        var listDID = $(this).sortable('toArray').toString();
        $("input[name=listDIDOrg]").val(listDID);
    });
    $( "#sortable2" ).on("sortremove", function( event, ui ) {
        var listDID = $(this).sortable('toArray').toString();
        $("input[name=listDIDOrg]").val(listDID);
    });
});
function filer_did(){
    var country=$("#country").find('option:selected').val();
    var city=$("#city").find('option:selected').val();
    var arrAction = new Array();
    arrAction["menu"]="organization";
    arrAction["action"]="changeDIDfilter";
    arrAction["country"]=country;
    arrAction["city"]=city;
    arrAction["listDIDOrg"]=$("input[name='listDIDOrg']").val();
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error!=""){
                alert(error);
            }else{
                $("#sortable1 > li").remove();
                for(x in arrData){
                    $("#sortable1").append('<li class="ui-state-default" id="'+arrData[x]['id']+'">('+arrData[x]['country_code']+') '+arrData[x]['area_code']+'-'+arrData[x]['did']+'</li>');
                }
            }
    });
}
//did assign function

function org_chk_limit(name){
    if($("input[name="+name+"_chk]").is(':checked')){
        $("input[name="+name+"]").prop("disabled",true);
        if(name=="max_num_user"){
            $("input[name=max_num_exten_chk]").prop("checked",true);
            $("input[name=max_num_exten]").prop("disabled",true);
        }
    }else{
        $("input[name="+name+"]").prop("disabled",false);
    }
    
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
function change_state(){
    //el nuevo estado
    var state=$("#state_orgs option:selected").val();
    if(!(state=="suspend" || state=="unsuspend" || state=="terminate")){
        alert("Invalid organization state");
    }
    //obtenemos el conjunto de organizaciones seleccionadas
    var orgs="";
    $(".chk_id").each(function(){
        if($(this).is(':checked')){
            var idOrg = $(this).val();
            if(validateDigit(idOrg))
                orgs = orgs+idOrg+","; 
        }
    });
    
    if(orgs.length!=0){
        var msg=$("#msg_ch_alert").val();
        if(!confirm(msg.replace('STATE_NAME',state))){
            return false;
        }
        
        $('.neo-modal-elastix-popup-close').css("display","none");
        $('.neo-modal-elastix-popup-close').click(function() {
            $("#state_orgs").closest('form').submit();
        });
        
        
        ShowModalPopUP("Changes State Organization", 350, 350, htmlModal("Changing the states of checked organizations to "+state));
        
        var arrAction = new Array();
        arrAction["menu"]="organization";
        arrAction["action"]="change_org_state";
        arrAction["state"]=state;
        arrAction["idOrgs"]=orgs;
        arrAction["rawmode"]="yes";
        request("index.php", arrAction, false,
            function(arrData,statusResponse,error){
                $('.neo-modal-elastix-popup-close').css("display","block");
                $("#org_change_status").html("Process Finished");
                if(error!=""){
                    $("#org_change_error").html(error);
                }else{
                    $("#org_change_result").html(arrData);
                }
        });
    }else{
        alert("You must select at least one valid organization");
    }
}

function delete_orgs(){
    //obtenemos el conjunto de organizaciones seleccionadas
    var orgs="";
    $(".chk_id").each(function(){
        if($(this).is(':checked')){
            var idOrg = $(this).val();
            if(validateDigit(idOrg))
                orgs = orgs+idOrg+","; 
        }
    });
    
    if(orgs.length!=0){
        if(!confirm("Are you sure you wish to deleted checked organizations")){
            return false;
        }
        
        $('.neo-modal-elastix-popup-close').css("display","none");
        $('.neo-modal-elastix-popup-close').click(function() {
            $("#state_orgs").closest('form').submit();
        });
        
        ShowModalPopUP("Delete Organizations", 350, 350, htmlModal("Deleting checked organizations"));
        
        var arrAction = new Array();
        arrAction["menu"]="organization";
        arrAction["action"]="delete_org_2";
        arrAction["idOrgs"]=orgs;
        arrAction["rawmode"]="yes";
        request("index.php", arrAction, false,
            function(arrData,statusResponse,error){
                $('.neo-modal-elastix-popup-close').css("display","block");
                $("#org_change_status").html("Process Finished");
                if(error!=""){
                    $("#org_change_result").html(error);
                }else{
                    $("#org_change_result").html(arrData);
                }
        });
    }else{
        alert("You must select at least one valid organization");
    }
}

function htmlModal(msn){
    var html="<p>"+msn+"<p>";
    html +="<p>This process can take several minutes<p>";
    html +="<p style='color: green; font-weight:bold; text-align:center;' id='org_change_status'><p>";
    html +="<p id='org_change_result'><p>";
    html +="<p id='org_change_error' style='color: red;'><p>";
    return html;
}

function validateDigit(obj) {
    for (n = 0; n < obj.length; n++){
        if ( ! isDigit(obj.charAt(n))) {
            return false;
        }
    }
    return true;
}

function isDigit(ch) {
   if (ch >= '0' && ch <= '9')
      return true;
   return false;
}