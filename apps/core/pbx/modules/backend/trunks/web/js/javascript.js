var trunk_flag=false;
$(document).ready(function(){
    $("#arrDestine").val(getArrRows()); 
    $("a[class^='adv_opt_']").click(function(){
        var type = $(this).attr("class").substring(8);
        
        if($("#mostra_adv_" + type).val()=="no"){
            $("#mostra_adv_" + type).val("yes");
            $(".show_more_"  + type).attr("style","visibility: visible;");
        }else{
            $("#mostra_adv_" + type).val("no");
            $(".show_more_" + type).attr("style","display: none;");
        }
        radio('tab-' + type);
    });
    
    $("td select[name=general_org]").change(function(){
        var org=$("select[name=general_org] option:selected").val();
        if(org!="none" && org!=""){
            var act_orgs=$("#select_orgs").val();
            //se agrega el elemento a la lista
            $("select[name='arr_org']").append("<option value="+org+">"+org+"</option>");
            //se quita el elemento de la lista de seleccion
            $("select[name=general_org] option:selected").remove();
            
            $("#select_orgs").val(act_orgs+org+",");
            $("select[name=general_org]").val("none");
        }
    });
    if($("#mode_input").val()=="edit" || $("#mode_input").val()=="input")
        mostrar_select_orgs();
    
    $("#general_sec_call_time").change(function(){
        if($("#general_sec_call_time option:selected").val()=="yes"){
            $(".general_sec_call_time").css("display","table-row");
            var alt=$("#content_tab-general").children("#div_body_tab").height();
            var alt_tab=alt+16;
            $(".tabs").css({'height':alt_tab});
        }else{
            $(".general_sec_call_time").css("display","none");
            var alt=$("#content_tab-general").children("#div_body_tab").height();
            $(".tabs").css({'height':alt});
        }
    });
    if($("#general_sec_call_time option:selected").val()=="no") 
        $(".general_sec_call_time").css("display","none");
    
    $('td select[class=state_trunk]').change(function(){
        if(trunk_flag==false){
            trunk_flag=true;
            var trunkId=$(this).attr("id");
            var action=$("#"+trunkId+" option:selected").val();
            var arrAction = new Array();
            arrAction["action"]   = "actDesactTrunk";
            arrAction["menu"]     = "trunks";
            arrAction["rawmode"]  = "yes";
            arrAction["id_trunk"]  = trunkId.substring(4);
            arrAction["trunk_action"]  = action;
            request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                var tmp="off";
                if(action=="on")
                    tmp="on";
                
                if(error!=""){
                    trunk_flag=false;
                    alert(error);
                }else{
                    trunk_flag=false;
                    $("#"+trunkId).val(tmp);
                    alert(arrData);
                }
            });
        }
    });  
    
    if(typeof $("#mode_input").val()==="undefined")
        getCurrentNumCalls();
});

$(window).load(function () {
    $("div.neo-module-content").attr("style","");
});

if($("#mode_input").val()=="input")
   var index=0;

function getArrRows(){
  var rows =0;
  var lastRow = getNumRows();
  var valIndex = "";
	$('table#destine tr.content-destine').each(function() {
	    rows++;
	if(rows==lastRow)
	  valIndex += rows;
	else
	  valIndex += rows+",";
	  
  }); 
  return valIndex;
}

function getNumRows(){
        var rows =0;
 	$('table#destine tr.content-destine').each(function() {
	    rows++;
	}); 
	return rows;
}

var add = function() {
    index ++;
    if(isNaN(index))
    index=1;
    
    if (($("#mode_input").val()=="edit")&& ($("#mostra_adv").val()==""))
        index = $("#index").val();

    var row = $('table#destine tr#test').html();
    if(typeof  row!== "undefined" && row)
    {
        var arrDestine = $("#arrDestine").val();
        if(index==1)
            arrDestine = index;
        else{
            arrDestine = arrDestine+","+index;
            arrDestine = arrDestine.replace(",,",",");
        }
        $("#arrDestine").val(arrDestine);
        $("#mostra_adv").val("val");

        row = row.replace(/\__/g, index);
        var val = "<tr id="+index+">"+row+"</tr>"; 
        $('table#destine tbody').append(val);
        $("#goto"+index).addClass("goto");
        $("#"+index).addClass("content-destine");
    }
};

$('.add').live('click', this, function(event) {
    add();
    radio("tab-general");
});

$('.delete').live('click', this, function(event) {
     //var index = $('table#destine tbody tr').length;    
     //if (index!=2){
       
	var arrDestine = $("#arrDestine").val();
	var id =  $(this).closest('tr').attr("id");
	arrDestine = arrDestine.replace(id,"");
	arrDestine = arrDestine.replace(",,",",");
	$(this).closest('tr').remove();
	$("#arrDestine").val(arrDestine);
    // }
    radio("tab-general"); 
});

function radio(id_radio){
    var alt=$("#content_"+id_radio).children("#div_body_tab").height();
    var alt_tab=alt+16;
    if(id_radio=="tab-peer")
        var alt_tab=alt+45;
    $(".tabs").css({'height':alt_tab});
    $(".content").css({"z-index":"0"});
    $("div.tab > .content > *").css({"opacity":"0"});
    $("#content_"+id_radio).css({"z-index":"1"});
    $("#content_"+id_radio+" > *").css({"opacity":"1"});
    //div de las tabs
    var d_label=$("#"+id_radio).parent();
    $(".neo-table-header-row-filter").css("background","none");
    $(".neo-table-header-row-filter").css("color","BLACK");
    d_label.css("background","-moz-linear-gradient(center top , #777777, #999999)");
    d_label.css("background","-webkit-gradient(linear,0% 40%,0% 70%,from(#777),to(#999))");
    d_label.css("background","linear-gradient(center top , #777777, #999999)");
    d_label.css("border-color"," #888888"); 
    d_label.css("color"," #FFFFFF"); 
}

function quitar_org(){
    var org=$("select[name=arr_org] option:selected").val();
    //se quita el elemento de la lista de seleccionados
    $("select[name=arr_org] option:selected").remove();
    //se agrega el elemento de la lista de canales disponibles
    $("select[name='general_org']").append("<option value="+org+">"+org+"</option>");
    var val=$("#select_orgs").val();
    var arrVal=val.split(",");
    var option="";
    for (x in arrVal){
        if(arrVal[x]!=org && arrVal[x]!="")
            option += arrVal[x]+",";
    }
    $("#select_orgs").val(option);
}

function mostrar_select_orgs(){
    var val=$("#select_orgs").val();
    var arrVal=val.split(",");
    
    for (x in arrVal){
        if(arrVal[x]!=""){
            $("select[name='arr_org']").append("<option value="+arrVal[x]+">"+arrVal[x]+"</option>");
        }
    }
    
    var chann=$("select[name='general_org']");
    var options = $('option', chann);
        options.each(function() {
            if(arrVal.indexOf($(this).text())!=-1){
                $("select[name=general_org] option[value='"+$(this).text()+"']").remove();
            }
        });
}

function getCurrentNumCalls(){
    var limit=$("#grid_limit").val();
    var offset=$("#grid_offset").val();
    var arrAction        = new Array();
    arrAction["action"]  = "get_num_calls";
    arrAction["menu"]    = "trunks";
    arrAction["limit"]   = limit
    arrAction["offset"]  = offset;
    arrAction["rawmode"] = "yes";
    request("index.php",arrAction,true,
        function(arrData,statusResponse,error)
        {
            if(error!=""){
                return true; //algo fallo detenemos la recursividad
            }else{
                $(".sec_trunk").each(function(){
                    var trunkId=$(this).children("p.num_calls").attr("id");
                    if(typeof arrData[trunkId] !== "undefined")
                        $(this).children("p.num_calls").remove();
                        $(this).prepend(arrData[trunkId]["p"]);
                        $(this).find("p.elapsed_time").children("span").html(arrData[trunkId]["elapsed_time"]);
                        $(this).find("p.count_calls").children("span").html(arrData[trunkId]["count_calls"]);
                        $(this).find("p.state").children("span").html(arrData[trunkId]["state"]);
                        $(this).find("p.fail_calls").children("span").html(arrData[trunkId]["fail_calls"]);
                        if(arrData[trunkId]["state"]=="YES" && trunk_flag==false)
                            $("#sel_"+trunkId).val("on");
                });
                return false; //continua la recursividad
            }
        });
}