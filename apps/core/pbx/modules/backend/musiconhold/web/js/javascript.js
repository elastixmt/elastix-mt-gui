$(document).ready(function(){
    if($("#mode_moh option:selected").val()=="custom" || ($("#mode_input").val()!="imput" &&  $("#moh_mode").val()=="custom")){
        $("#files").css("display","none");
        $(".sort").css("display","none");
    }else{
        $(".application").css("display","none");
    }

    $("#mode_moh").change(function(){
        if($("#mode_moh option:selected").val()=="files"){
            $("#files").css("display","");
            $(".sort").css("display","table-row");
            $(".application").css("display","none");
        }else{
            $("#files").css("display","none");
            $(".sort").css("display","none");
            $(".application").css("display","table-row");
        }
    });
});

$('.file_upload').live('change', function() {
 var max_size = $("#max_size").val();
 var file_size = this.files[0].size;
    if(file_size> max_size){
       alert($("#alert_max_size").val()+"Max: "+max_size/1048576 + " MB.");
       $('#file_record').val("");
    }
});
    
var add = function() {    
    index ++;
    if(isNaN(index)){
        index=2;
    }
     
    if (($("#mode_input").val()=="edit") && ($("#mostra_adv").val()=="")){
        index = $("#index").val();
    }
    
    var row = $('table#files tr#test').html();
    if(typeof  row!== "undefined" && row)
    {
        var arrFiles = $("#arrFiles").val();
        if(index==1)
            arrFiles = index;
        else{
            arrFiles = arrFiles+","+index;
            arrFiles = arrFiles.replace(",,",",");
        }
        $("#arrFiles").val(arrFiles);
        $("#mostra_adv").val("val");
        row = row.replace(/\__/g, index);
        var val = "<tr id="+index+">"+row+"</tr>"; 
        
        $('.add_file').before(val);
        $("#"+index).addClass("content-files");
    }
};

$('#add_file').live('click', this, function(event) {
        add();
});

$('.delete').live('click', this, function(event) {
    var arrFiles = $("#arrFiles").val();
    var id =  $(this).closest('tr').attr("id");
    arrFiles = arrFiles.replace(id,"");
    arrFiles = arrFiles.replace(",,",",");
    $(this).closest('tr').remove();
    $("#arrFiles").val(arrFiles);
});





