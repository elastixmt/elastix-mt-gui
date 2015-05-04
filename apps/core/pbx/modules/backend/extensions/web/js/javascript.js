$(document).ready(function(){
    enabled_voicemail();
	$("#create_vm").change(function (){
		if($("#create_vm").is(":checked")){
			$("#create_vm").val("yes");
			$("#create_vm").attr("checked","checked");
            $("tr[class='voicemail']").children("td").children().each(function(){
                $(this).removeAttr("disabled");
            });
            var option=$("#vmx_locator option:selected").val(); 
            if(option=="enabled"){
                $("tr[class='voicemail vm_locator']").children("td").children().each(function(){
                    $(this).removeAttr("disabled");
                });
            }
            if($("input[name='vmx_operator']").val()=="on"){
                $("input[name='vmx_extension_0']").attr("disabled","disabled");
            }
		}else{
			$("#create_vm").val("off");
            $("tr[class='voicemail']").children("td").children().each(function(){
                $(this).attr("disabled","disabled");
            });
            $("tr[class='voicemail vm_locator']").children("td").children().each(function(){
                $(this).attr("disabled","disabled");
            });
		}
		radio('tab-3');
	});
	
	$(".adv_opt").click(function(){
		if($("#mostra_adv").val()=="no"){
			$("#mostra_adv").val("yes");
			$(".show_more").attr("style","visibility: visible;");
		}else{
			$("#mostra_adv").val("no");
			$(".show_more").attr("style","display: none;");
		}
		radio('tab-2');
	});
    
    $("#vmx_locator").change(function (){
        var option=$("#vmx_locator option:selected").val(); 
        if(option=="enabled"){
            $("tr[class='voicemail vm_locator']").children("td").children().each(function(){
                $(this).removeAttr("disabled");
            });
        }else{
            $("tr[class='voicemail vm_locator']").children("td").children().each(function(){
                $(this).attr("disabled","disabled");
            });
        }
        radio('tab-3');
    });
    
    $("input[name='chkoldvmx_operator']").change(function(){
        if($("input[name='chkoldvmx_operator']").is(":checked")){
            $("input[name='vmx_extension_0']").attr("disabled","disabled");
        }else{
            $("input[name='vmx_extension_0']").removeAttr("disabled");
        }
    });
});

$(window).load(function () {
        $("div.neo-module-content").attr("style","");
});

function enabled_voicemail(){
    $("#create_vm").val("off");
    $("tr[class='voicemail']").children("td").children().each(function(){
        $(this).attr("disabled","disabled");
    });
    $("tr[class='voicemail vm_locator']").children("td").children().each(function(){
        $(this).attr("disabled","disabled");
    });
    
    if($("#create_vm").is(":checked")){
        $("#create_vm").val("yes");
        $("#create_vm").attr("checked","checked");
        $("tr[class='voicemail']").children("td").children().each(function(){
            $(this).removeAttr("disabled");
        });
        var option=$("#vmx_locator option:selected").val(); 
        if(option=="enabled"){
            $("tr[class='voicemail vm_locator']").children("td").children().each(function(){
                $(this).removeAttr("disabled");
            });
        }
    }
    
    if($("input[name='vmx_operator']").val()=="on"){
        $("input[name='vmx_extension_0']").attr("disabled","disabled");
    }
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