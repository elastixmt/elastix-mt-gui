$(document).ready(function(){
    $("#sip_nat_type").change(function(){
        natConfig();
        radio("tab-2");
    });
    $("#iax_jitterbuffer").change(function(){
        jitterConfig();
        radio("tab-3");
    });
    natConfig();
    jitterConfig();
    $(function() {
        $("#audio_codec").sortable();
        $("#audio_codec").disableSelection();
        $("#video_codec").sortable();
        $("#video_codec").disableSelection();
    });
});
function natConfig(){
    var nattype=$("#sip_nat_type option:selected").val();
    if(nattype=="public"){
        $(".nat_param").css("display","none");
    }else{
        $(".nat_param").css("display","table-row");
        if(nattype=="static"){
            $(".static_conf").css("display","table-row");
            $(".dynamic_conf").css("display","none");
        }else{
            $(".dynamic_conf").css("display","table-row");
            $(".static_conf").css("display","none");
        }
    }
}
function jitterConfig(){
    if($("#iax_jitterbuffer option:selected").val()=="yes"){
        $(".iax_jitter").css("display","table-row");
    }else{
        $(".iax_jitter").css("display","none");
    }
}
$('.add_prop').live('click', this, function(event) {
    var id=$(this).attr("id");
    var tech = id.substring(7);
    var module_name=$("input[name='mod_name']").val();
    $(this).closest("table").append(createInputCustom(tech,module_name));
    if(tech=="sip")
        radio("tab-2");
    else if(tech=="iax")
        radio("tab-3");
});
$('.remove_prop_sip').live('click', this, function(event) {
    $(this).closest('tr').remove();
    radio("tab-2");
});
$('.remove_prop_iax').live('click', this, function(event) {
    $(this).closest('tr').remove();
    radio("tab-3");
});
$('.add_local').live('click', this, function(event) {
    var module_name=$("input[name='mod_name']").val();
    $(".static_conf").first().before(createInputLocal(module_name));
    radio("tab-2");
});
$('.remove_local').live('click', this, function(event) {
    $(this).closest('tr').remove();
    radio("tab-2");
});
function createInputCustom(tech,module_name){
    var comp="<tr>";
    comp +="<td style='padding-left: 12px' colspan='4'><input type='text' name='"+tech+"_custom_name[]' value=''></input>";
    comp +=" = <input type='text' name='"+tech+"_custom_val[]' value=''></input>";
    comp +="<img class='remove_prop_"+tech+"' src='web/apps/"+module_name+"/images/remove1.png' title='Remove'/></td>"; 
    comp +="<tr>";
    return comp;
}
function createInputLocal(module_name){
    var comp="<tr>";
    comp +="<td ></td>";
    comp +="<td style='padding-left: 12px' colspan='3'><input type='text' name='localnetip[]' value=''></input>";
    comp +=" / <input type='text' name='localnetmask[]' value=''></input>";
    comp +="<img class='remove_local' src='web/apps/"+module_name+"/images/remove1.png' title='Remove'/></td>";
    comp +="<tr>";
    return comp;
}
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

