function hideField(opcion)
{
    if(opcion == "TCP" || opcion == "UDP"){
        document.getElementById('port').style.display='';
        document.getElementById('type').style.display='none';
        document.getElementById('code').style.display='none';
        document.getElementById('protocol_number').style.display='none';   
    }else if(opcion == "ICMP"){
        document.getElementById('port').style.display='none';
        document.getElementById('type').style.display='';
        document.getElementById('code').style.display='';
        document.getElementById('protocol_number').style.display='none';
    }else{
        document.getElementById('port').style.display='none';
        document.getElementById('type').style.display='none';
        document.getElementById('code').style.display='none';
        document.getElementById('protocol_number').style.display='';
    }
}