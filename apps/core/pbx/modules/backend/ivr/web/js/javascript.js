$(document).ready(function(){
    //if($("#mode_input").val()=="input")
        //add();
    $("select.goto").trigger('change');
    $("#arrDestine").val(getArrRows()); 
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
});
$('.delete').live('click', this, function(event) {
    var arrDestine = $("#arrDestine").val();
    var id =  $(this).closest('tr').attr("id");
    arrDestine = arrDestine.replace(id,"");
    arrDestine = arrDestine.replace(",,",",");
    $(this).closest('tr').remove();
    $("#arrDestine").val(arrDestine);
    // }
});
$(".goto").live('change', this, function(event) {
    var name = $(this).attr("name");
    var id = name.split("goto");
    
    var num = id[1];
    var option = $("#goto"+num+" option:selected").val();
    //obtenemos las opciones correspondientes a esa categoria
    var arrAction = new Array();
    arrAction["action"]   = "get_destination_category";
    arrAction["menu"]     = "ivr";
    arrAction["rawmode"]  = "yes";
    arrAction["organization"]  = $("input[name=organization]").val();
    arrAction["option"]  = option;
    request("index.php",arrAction,false,
    function(arrData,statusResponse,error)
    {
        $("#destine"+num+" option").remove();
        if(error!=""){
            alert(error);
        }else{
            for( x in arrData ){
                var valor=arrData[x];
                if(valor==$("#optionDestine"+num).val())
                    $("#destine"+num).append("<option value="+x+" selected>"+valor+"</option>");
                else
                    $("#destine"+num).append("<option value="+x+">"+valor+"</option>"); 
            }
        }
    });       
});
function radio(id_radio){
    var alt=$("#content_"+id_radio).children("table").height();
    var alt_tab=alt+10;
    $(".tabs").css({'height':alt_tab});
    $(".content").css({'height':'0'});
    $("#content_"+id_radio).css({'height':''});
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



