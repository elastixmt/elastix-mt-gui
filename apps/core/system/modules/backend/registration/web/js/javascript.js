$(document).ready(function(){
    

});

function registration(){
    var contactName = $('#idPartner').val();
    var email       = $('#email').val();
    var phone       = $('#phone').val();
    var company     = $('#company').val();
    var address     = $('#address').val();
    var city        = $('#city').val();
    var country     = $('#idPartner option:selected').val();
    var idPartner   = $('#idPartner').val();
    error = false;
    txtError = "Please fill the correct values in fields: \n";
    if(contactName == ""){ /*solo letras*/
        error = true;
        txtError += "* Contact Name: Only text \n";
    }
    if(!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) || email == ""){ /*solo email*/
        error = true;
        txtError += "* Email: Only format email \n";
    }
    if(!(/^\w+$/.test(phone)) || phone == ""){ /*numeros y letras*/
        error = true;
        txtError += "* Phone: text or number \n";
    }
    if(company == ""){
        error = true;
        txtError += "* Company: text \n";
    }
    /*if(!(/^[A-Za-z\_\-\.\s\xF1\xD1]+$/.test(address)) || address == ""){
        error = true;
        txtError += "* Address: text \n";
    }*/
    if(city == ""){
        error = true;
        txtError += "* City: text \n";
    }
    if(country == "" || country == "none"){
        error = true;
        txtError += "* Country: Selected a country \n";
    }
    /*if(idPartner == ""){
        error = true;
        txtError += "* Country: text \n";
    }*/
    if(error)
        alert(txtError);
} 

