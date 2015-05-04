$(document).ready(function(){
    $("#idOrganization").on("change", function() {
        $("#idformgrid").submit();
    });
    $('td input:checkbox[class=access_act]').change(function(){
        if($(this).is(":checked")){
            if($(this).attr("disabled")!="disabled"){
                $(this).val("on");
                $(this).attr("checked","checked");
                var other_act=$(this).parents("tr:first").children("td").children("input:checkbox[class=other_act]");
                other_act.removeAttr("disabled");
            }
        }else{
            $(this).val("off");
            if($(this).attr("disabled")!="disabled"){
                var other_act=$(this).parents("tr:first").children("td").children("input:checkbox[class=other_act]");
                other_act.attr("disabled","disabled");
                other_act.removeAttr("checked");
            }
        }
    });
});
