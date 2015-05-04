$(document).ready(function(){
    //
});

$('.add_tg').live('click', this, function(event) {
    var id_cond=$(this).attr("id");
    var time_cond = id_cond.substring(4);
    var id_div = $(this).closest("div").parent("div").attr("id");
    
    var li = $("div#test div."+time_cond+" ul li").html();
    if(typeof  li!== "undefined" && li)
    {
        li = li.replace(/\__/g, "["+id_div+"][]");
        var val = "<li class=li_"+time_cond+">"+li+"</li>";
        $("div#"+id_div+" div."+time_cond+" ul").append(val);
    }
    
    if(time_cond=="time"){
        $("div#"+id_div+" div."+time_cond+" ul li:last-child div.sTime").jtimepicker({
            minCombo: "Smin["+id_div+"][]",
            hourCombo: "Shour["+id_div+"][]",
            minClass: "smin",
            hourClass: "shour",
            minDefaultValue: "*",
            hourDefaultValue: "*",
        }); 
        $("div#"+id_div+" div."+time_cond+" ul li:last-child div.fTime").jtimepicker({
            minCombo: "Fmin["+id_div+"][]",
            hourCombo: "Fhour["+id_div+"][]",
            minClass: "fmin",
            hourClass: "fhour",
            minDefaultValue: "*",
            hourDefaultValue: "*",
        }); 
    }
});

$('.remove_tg').live('click', this, function(event) {
    $(this).closest('li').remove();
});

$('#add_group').live('click', this, function(event) {
    index ++;
    if(isNaN(index)){
        index = $("#index").val();
    }
    
    var div = $("div#test").html();
    if(typeof  div!== "undefined" && div)
    {
        $("#mostra_adv").val("val");
        div = div.replace(/\__/g, "["+index+"][]");
        div = div.replace(/remove/g, "add");
        div = div.replace(/Remove/g, "Add");
        var val = "<div id="+index+" class=div_tg>"+div+"</div>";
        $(this).closest("form").append(val);
    }
    
    $("div#"+index+" div.time ul li.li_time div.sTime").jtimepicker({
        minCombo: "Smin["+index+"][]",
        hourCombo: "Shour["+index+"][]",
        minClass: "smin",
        hourClass: "shour",
        minDefaultValue: "*",
        hourDefaultValue: "*",
    });
    $("div#"+index+" div.time ul li.li_time div.fTime").jtimepicker({
        minCombo: "Fmin["+index+"][]",
        hourCombo: "Fhour["+index+"][]",
        minClass: "fmin",
        hourClass: "fhour",
        minDefaultValue: "*",
        hourDefaultValue: "*",
    });
});

$('#delete_group').live('click', this, function(event) {
    $(this).closest('div').parent("div").remove();
});




