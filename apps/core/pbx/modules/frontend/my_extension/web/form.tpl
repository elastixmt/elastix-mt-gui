{literal}
<link rel="stylesheet" href="web/_common/js/jquery/css/smoothness/jquery-ui-1.8.24.custom.css">
{/literal}
<div id="contsetting">
    
    <div class="my_settings">
       <div class="row">
            <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><button class="button btn btn-default btn-sm" type="button" name="save_new" onclick='editExten()' title = "Save the Changes"> <span class="glyphicon glyphicon-ok"></span> Save Configuration</div>
            <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8"><input class="button btn btn-default btn-sm" type="submit" name="cancel" value="Cancel" title = "Cancel the changes"></div>
        </div>

        <div class="row" >
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><p> </p></div>
        </div>        

        <div class="row elx-modules-content">
            <div class="row">
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$DISPLAY_NAME_LABEL}</label></div>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-9"><p>{$clid_name}</p></div>
            </div>

            <div class="row">
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$DISPLAY_EXT_LABEL}</label></div>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-9"><p>{$extension}</p></div>
            </div>

            <div class="row">
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$DISPLAY_DEVICE_LABEL}</label></div>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-9"><p>{$device}</p></div>
            </div>

            <div class="row">
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$language_vm.LABEL}</label></div>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-9">
                    <p>{$language_vm.INPUT}</p>
                </div>
            </div>

            <div class="row" >
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$doNotDisturb.LABEL}</label></div>
                <div class="col-xs-7 col-sm-7 col-md-8 col-lg-8" id="radio_do_not_disturb">{$doNotDisturb.INPUT}</div>
            </div>

            <div class="row" >
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$callWaiting.LABEL}</label></div>
                <div class="col-xs-7 col-sm-7 col-md-8 col-lg-8" id="radio_call_waiting">{$callWaiting.INPUT}</div>
            </div>

            <div class="row" >
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 subtitle"><p>{$DISPLAY_CFC_LABEL}</p></div>
            </div>

            <div class="row" >
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3"><label>{$callForwardOpt.LABEL}</label></div>
                <div class="col-xs-5 col-sm-5 col-md-4 col-lg-3" id="radio_call_forward">{$callForwardOpt.INPUT}</div>
                <div class="col-xs-6 col-sm-6 col-md-7 col-lg-5">
                    {$callForwardInp.INPUT}
                    <a href="#" class="glyphicon glyphicon-exclamation-sign hidden-tooltip" data-toggle="tooltip" data-placement="auto" title="" data-original-title="Just numeric characters are valid"></a>
                </div>
            </div>

            <div class="row" >
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3"><label>{$callForwardUnavailableOpt.LABEL}</label></div>
                <div class="col-xs-5 col-sm-5 col-md-4 col-lg-3" id="radio_call_forward_unavailable">{$callForwardUnavailableOpt.INPUT}</div>
                <div class="col-xs-6 col-sm-6 col-md-7 col-lg-5">
                    {$callForwardUnavailableInp.INPUT}
                    <a href="#" class="glyphicon glyphicon-exclamation-sign hidden-tooltip" data-toggle="tooltip" data-placement="auto" title="" data-original-title="Just numeric characters are valid"></a>
                </div>
            </div>

            <div class="row" >
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3"><label>{$callForwardBusyOpt.LABEL}</label></div>
                <div class="col-xs-5 col-sm-5 col-md-4 col-lg-3" id="radio_call_forward_busy">{$callForwardBusyOpt.INPUT}</div>
                <div class="col-xs-6 col-sm-6 col-md-7 col-lg-5">
                    {$callForwardBusyInp.INPUT}
                    <a href="#" class="glyphicon glyphicon-exclamation-sign hidden-tooltip" data-toggle="tooltip" data-placement="auto" title="" data-original-title="Just numeric characters are valid"></a>
                </div>
            </div>
        
            <div class="row" >
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 subtitle"><p>{$DISPLAY_CMS_LABEL}</p></div>
            </div>

            <div class="row" >
                <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3"><label>{$recordIncoming.LABEL}</label></div>
                <div class="col-xs-12 col-sm-11 col-md-8 col-lg-8" id="radio_record_incoming">{$recordIncoming.INPUT}</div>
            </div>

            <div class="row" >
                <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3"><label>{$recordOutgoing.LABEL}</label></div>
                <div class="col-xs-12 col-sm-11 col-md-8 col-lg-8" id="radio_record_outgoing">{$recordOutgoing.INPUT}</div>
            </div>
        
            <div class="row" >
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 subtitle"><p>{$DISPLAY_VOICEMAIL_LABEL}</p></div>
            </div>

            <div class="row" >
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$status_vm.LABEL}</label></div>
                <div class="col-xs-7 col-sm-7 col-md-8 col-lg-8" id="radio_status">{$status_vm.INPUT}</div>
            </div>

            <div class="row" >
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$email_vm.LABEL}</label></div>
                <div class="col-xs-7 col-sm-7 col-md-8 col-lg-8">
                    <div class="input-group">
                        <span class="input-group-addon">@</span>
                        {$email_vm.INPUT}
                        <a href="#" class="glyphicon glyphicon-exclamation-sign hidden-tooltip" data-toggle="tooltip" data-placement="auto" title="" data-original-title="Invalid email"></a>
                    </div>
                </div>
            </div>
        
            <div class="row" >
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$password_vm.LABEL}</label></div>
                <div class="col-xs-7 col-sm-7 col-md-8 col-lg-8">
                    {$password_vm.INPUT}
                    <a href="#" class="glyphicon glyphicon-exclamation-sign hidden-tooltip" data-toggle="tooltip" data-placement="auto" title="" data-original-title="Just numeric characters are valid"></a>
                </div>
            </div>
        
            <div class="row" >
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$emailAttachment_vm.LABEL}</label></div>
                <div class="col-xs-7 col-sm-7 col-md-8 col-lg-8" id="radio_email_attachment">{$emailAttachment_vm.INPUT}</div>
            </div>

            <div class="row" >
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$playCid_vm.LABEL}</label></div>
                <div class="col-xs-7 col-sm-7 col-md-8 col-lg-8" id="radio_play_cid">{$playCid_vm.INPUT}</div>
            </div>
        
            <div class="row" >
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$playEnvelope_vm.LABEL}</label></div>
                <div class="col-xs-7 col-sm-7 col-md-8 col-lg-8" id="radio_play_envelope">{$playEnvelope_vm.INPUT}</div>
            </div>

            <div class="row" >
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$deleteVmail.LABEL}</label></div>
                <div class="col-xs-7 col-sm-7 col-md-8 col-lg-8" id="radio_delete_vmail">{$deleteVmail.INPUT}</div>
            </div>

            <div class="row" >
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><p> </p></div>
            </div>
        </div>
            
    </div>
</div>
