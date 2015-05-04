var hangup = "no";
var as = [];
var player;

$('#file_record').live('change', function() {
 var max_size = $("#max_size").val();
 var file_size = this.files[0].size;
    if(file_size> max_size){
       alert($("#alert_max_size").val()+"Max: "+max_size/1048576 + " MB.");
       $('#file_record').val("");
    }
      
});

audiojs.events.ready(function() {
       // var i=0;
    var as = audiojs.createAll({
        useFlash: false,
        trackEnded: function() {
        $('.audiojs').css('visibility','hidden');
        },
    });
        //i=0;
    $('.single').click(function(e) { 
        var flag_array = 0;
        if(typeof  player!== "undefined" && player){
            as[player].pause();
            as[player].load();
            $("#audiojs_wrapper"+player).css("visibility","hidden");
        }
        var fload =0;
            player = $(this).attr("id");
        $("#audiojs_wrapper"+player).css("visibility","visible");
        $("#audiojs_wrapper"+player).removeClass("error");
        as[player].load($('span', this).attr('data-src'));
        as[player].play();
    });

    $('.single2').live("click", function(e) { 
        $(".audiojs").css("visibility","hidden");
        $("#audiojs_wrapper0").css("visibility","visible");
        as[0].load($('span', this).attr('data-src'));
        as[0].play();
    });
        
});

//Verificar si ya existe ese nombre de recording
function checkName(){
    hangup = "no";
    $(".labelError").html($("#checking").val());
    var arrAction        = new Array();
    var recording_name   = $("#recording_name").val(); 
   
    var extension   = $("#on_extension").val();
    arrAction["recording_name"]  = recording_name;
    arrAction["action"]  = "checkName";
    arrAction["menu"]    = "recordings";
    arrAction["rawmode"] = "yes";
    $("#flag").val("check");
       request("index.php",arrAction,true,
            function(arrData,statusResponse,error)
            {
                if(arrData["name"]==true){
                    recordMsg(); 
                }else{
                    if(arrData["name"]=="empty"){
                        $(".stop").trigger('click');
                        $(".labelError").html("Insert Record Name");
                    }else{
                        if(hangup=="no"){
                            var retVal = confirm($("#dialog-confirm").val());
                            if( retVal == true ){
                                $("#cod").val(arrData["id"]);
                                    recordMsg(); 
                                // return true;
                            }else{
                                $(".stop").trigger('click');
                                //return false;
                            }
                        }else{
                            $(".stop").trigger('click');
                        }
                    return true;
                    }
                    return true;
                }
                return true;
            });
   
}

function recordMsg()
{
    var recording_name   = $("#recording_name").val(); 
    //Verificar que no exista otro recording con el mismo nombre
  
    $(".labelError").html($("#dialing").val());
    $("#flag").val("dial");
    var cod = $("#cod").val();
    var arrAction        = new Array();
    var flag   = $("#flag").val();
    var extension   = $("#on_extension").val();
    arrAction["recording_name"]  = recording_name;
    arrAction["extension"]  = extension;
    arrAction["action"]  = "record";
    arrAction["menu"]    = "recordings";
    arrAction["flag"]    = flag;
    arrAction["rawmode"] = "yes";
    request("index.php",arrAction,true,
        function(arrData,statusResponse,error){
            $("#flag").val("call");
            $(".labelError").html(arrData["record"]["msg"]);
            if(arrData["record"]["status"]=="error"){
                $(".stop").trigger('click');
                $(".labelError").html(arrData["record"]["msg"]);                
            }else{ 
                $("#filename").val(arrData["record"]["filename"]);
                if(arrData["record"]["id"]) 
                    $("#cod").val(arrData["record"]["id"]);
                else
                    $("#cod").val(cod);
                checkCallStatus();
                return true; 
            }  
                    return true; 
        });
}

function hangUp()
{   hangup = "yes";
    $(".labelError").html($("#hangup").val());
    var id = $("#cod").val();
    var arrAction        = new Array();
    var sound = "<span data-src='index.php?menu=recordings&action=download&id="+id+"&rawmode=yes' style='margin-left: 5px;top: 3px;position: relative; cursor:pointer;'><img src='web/apps/recordings/images/sound.png' width='16px'/></span>";
    
    var extension = $("#on_extension").val();
 
    arrAction["extension"]  = extension;
    arrAction["action"]  = "hangup";
    arrAction["menu"]    = "recordings";
    arrAction["rawmode"] = "yes";
    arrAction["name"] = $("#filename").val()+".wav";
    var flag = $("#flag").val();
    request("index.php",arrAction,true,
        function(arrData,statusResponse,error)
        {
            if(arrData["record"][0]=="Success"){
                if(flag=="call")  
                    $(".labelError").html("<div class='single2'>"+$("#success_record").val()+" <strong>"+$("#filename").val()+".wav</strong>"+sound+"</div>");
                else
                    $(".labelError").html($("#cancel_record").val());
                return true; 
            }else{
                if((flag!="call")&&(flag!="check"))  
                {
                    $(".labelError").html($("#cancel_record").val());
                }
                return true; 
            }  
           return true; 
        });
}



function checkCallStatus()
{
    var arrAction   = new Array();
    var extension   = $("#on_extension").val();
    var id = $("#cod").val();
    arrAction["extension"]  = extension;
    arrAction["action"]  = "check_call_status";
    arrAction["menu"]    = "recordings";
    arrAction["rawmode"] = "yes";
    var sound = "<span data-src='index.php?menu=recordings&action=download&id="+id+"&rawmode=yes' style='margin-left: 5px;top: 3px;position: relative; cursor:pointer;'><img src='web/apps/recordings/images/sound.png' width='16px'/></span>";
    
     request("index.php",arrAction,true,
	    
            function(arrData,statusResponse,error)
            {
                if(statusResponse=="HANGUP"){
                   
		  $(".stop").trigger('click');
		      $(".labelError").html("<div class='single2'>"+$("#success_record").val()+" <strong>"+$("#filename").val()+".wav</strong>"+sound+"</div>");
		   
                  return true;
                }
              //  return true; //continua la recursividad
            });
}

function jqCheckAll(id)
{
   $( ":checkbox.delete").attr('checked', $('.' + id).is(':checked'));
   
}

