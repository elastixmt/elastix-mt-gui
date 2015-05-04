{literal}
<script type='text/javascript' src="web/_common/js/jquery.liteuploader.js"></script>
<script type='text/javascript' src="web/_common/js/profile_uf.js"></script>
{/literal}

<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close elx_close_popup_profile" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 id="myModalLabel">{$TITLE_POPUP}({$nameProfile})</h3>
    </div>
    <div class="modal-body">
    
        <div class="row">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">{$picture.INPUT}</div>
            </div>   
            
        </div>
    
        <div class="row">
            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div id="previews">
                            <img id='preview' class="img-responsive" width="159px" alt='image' src='index.php?menu=_elastixutils&action=getImage&ID={$ID_PICTURE}&rawmode=yes'/>
                        </div>
                    </div>
                </div> 
                

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <p>
                            <button type="button" class="btn btn-default btn-xs" id="deleteImageProfile">{$DeleteImage}</button>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
                <div class="row">
                    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5"><label>{$userProfile_label}</label></div>
                    <div class="col-xs-12 col-sm-7 col-md-7 col-lg-7"><p>{$userProfile}</p></div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5"><label>{$extenProfile_label}</label></div>
                    <div class="col-xs-12 col-sm-7 col-md-7 col-lg-7"><p>{$extenProfile}</p></div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5"><label>{$faxProfile_label}</label></div>
                    <div class="col-xs-12 col-sm-7 col-md-7 col-lg-7"><p>{$faxProfile}</p></div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5"><label>{$languageProfile.LABEL}</label></div>
                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6"><p>{$languageProfile.INPUT}</p></div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <hr>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center text-info"><label id="elx_link_change_passwd">{$CHANGE_PASSWD_POPUP}</label></div>
            </div>
        </div>
        <div class="row" style="display: none;" id="elx_data_change_passwd">
            <div class="row">
                <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5"><label>{$currentPasswordProfile.LABEL}</label></div>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6"><p>{$currentPasswordProfile.INPUT}</p></div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5"><label>{$newPasswordProfile.LABEL}</label></div>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6"><p>{$newPasswordProfile.INPUT}</p></div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5"><label>{$repeatPasswordProfile.LABEL}</label></div>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6"><p>{$repeatPasswordProfile.INPUT}</p></div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
                    <button id="elx_save_change_passwd" disabled="disabled" type="button" class="btn btn-primary">{$SAVE_POPUP}</button>
                </div>
            </div>
        </div>

    </div>

</div><!-- /.modal-content -->
