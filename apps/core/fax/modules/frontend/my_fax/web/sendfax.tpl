{literal}
<script type='text/javascript' src="web/_common/js/jquery.liteuploader.js"></script>
{/literal}

<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 id="myModalLabel">{$TITLE_POPUP}</h3>
    </div>
    
    <div class="modal-body">
        
        <div class="row">
            <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5"><label>{$faxDeviceLabel}</label></div>
            <div class="col-xs-12 col-sm-7 col-md-7 col-lg-7"><p>{$faxDevice}</p></div>
        </div>
        
        <div class="row">
            <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5"><label>{$destinationFaxNumber.LABEL} <span class="glyphicon-asterisk mandatory"></span></label></div>
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6"><p>{$destinationFaxNumber.INPUT}</p></div>
        </div>
        
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="checkbox">
                    <label>
                        <input id="elx-chk-attachment-file" type="checkbox"> {$file_upload}
                    </label>
                </div>
            </div>
        </div>
        
        <div class="row visible" id="elx-body-fax-label">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><label>{$faxContent.LABEL}</label></div>
        </div>
        
        <div class="row visible" id="elx-body-fax-content">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><p>{$faxContent.INPUT}</p></div>
        </div>
        
        <div class="row oculto" id="elx-attached-fax-file">
            <div class="col-lg-3"><label>{$faxFile.LABEL}<label></div>
            <div class="col-lg-9">{$faxFile.INPUT}</div>
        </div>
        
        <div class="row" >
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><p> </p></div>
        </div>
        
        <div class="row oculto" id="elx-notice-fax-file">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <p class="text-danger">{$note}</p>
            </div>
        </div>

    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{$CANCEL}</button>
        <button type="button" class="btn btn-primary" onclick="sendNewFax()">{$SEND_FAX}</button>
    </div>

</div><!-- /.modal-content -->
        

