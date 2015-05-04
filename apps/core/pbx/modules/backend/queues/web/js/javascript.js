$(document).ready(function(){
    $("td select[name^=pickup_]").change(function(){
        var name=$(this).attr("name");
        var type=name.substring(7);
        var new_member=$("select[name=pickup_"+type+"] option:selected").val();
        if(new_member!="none"){
            var members=$("textarea[name="+type+"_members]").val();
            $("textarea[name="+type+"_members]").val(members+new_member+",0\n");
            $("select[name=pickup_"+type+"]").val("none");
        }
    });
    
   $("#category").change(function(){
        var category=$("select[name='category'] option:selected").val();
        //obtenemos las opciones correspondientes a esa categoria
        var arrAction = new Array();
        arrAction["action"]   = "get_destination_category";
        arrAction["menu"]     = "queues";
        arrAction["rawmode"]  = "yes";
        arrAction["organization"]  = $("input[name='organization']").val();
        arrAction["category"]  = category;
        request("index.php",arrAction,false,
        function(arrData,statusResponse,error)
        {
            $("select[name='destination'] option").remove();
            if(error!=""){
                alert(error);
            }else{
                for( x in arrData ){
                    var valor=arrData[x];
                    $("select[name='destination']").append("<option value="+x+">"+valor+"</option>");
                }
            }
        });
    });
});

$(window).load(function () {
        $("div.neo-module-content").attr("style","");
});

function radio(id_radio){
    var alt=$("#content_"+id_radio).children("table").height();
    var alt_tab=alt+10;
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


