<link rel="stylesheet" href="web/_common/js/jquery/css/smoothness/jquery-ui-1.8.24.custom.css">
<script type='text/javascript' src="web/_common/js/jquery.liteuploader.js"></script>

<div id="contsetting">
    
    <div class="my_settings">
    
        <div class="row">
            <div class="col-md-12">
                {if $ELX_ACTION eq 'new'}
                    <button class="btn btn-default btn-sm" type="button" name="save_new" onclick='saveNewContact()'> <span class="glyphicon glyphicon-ok"></span> Save</button>
                {else}
                    <button class="btn btn-default btn-sm" type="button" name="save_edit" onclick='saveEditContact()'> <span class="glyphicon glyphicon-ok"></span> Save</button>
                {/if}
                <button class="btn btn-default btn-sm" type="button" name="cancel" onclick='cancelContact()'> Cancel</button>
            </div>
        </div>

        <div class="row" >
            <div class="col-md-6"><p> </p></div>
        </div>


        <div class="row elx-modules-content">
            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-lg-6"><label>{$contact_type.LABEL}</label></div>
                        <div class="col-lg-5 contact_type" id="contact_type">{$contact_type.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$first_name.LABEL} <span class="glyphicon-asterisk mandatory"></span></label></div>
                        <div class="col-lg-6">
                            {$first_name.INPUT}
                            <a href="#" class="glyphicon glyphicon-exclamation-sign hidden-tooltip" data-toggle="tooltip" data-placement="auto" title="" data-original-title="{$TOOLTIP_FIRS_NAME}"></a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$last_name.LABEL} <span class="glyphicon-asterisk mandatory"></span></label></div>
                        <div class="col-lg-6">
                            {$last_name.INPUT}
                            <a href="#" class="glyphicon glyphicon-exclamation-sign hidden-tooltip" data-toggle="tooltip" data-placement="auto" title="" data-original-title="{$TOOLTIP_LAST_NAME}"></a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$work_phone_number.LABEL} <span class="glyphicon-asterisk mandatory"></span></label></div>
                        <div class="col-lg-6">
                            {$work_phone_number.INPUT}
                            <a href="#" class="glyphicon glyphicon-exclamation-sign hidden-tooltip" data-toggle="tooltip" data-placement="auto" title="" data-original-title="{$TOOLTIP_POHNE}"></a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$cell_phone_number.LABEL}</label></div>
                        <div class="col-lg-6">{$cell_phone_number.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$home_phone_number.LABEL}</label></div>
                        <div class="col-lg-6">{$home_phone_number.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$fax_number_1.LABEL}</label></div>
                        <div class="col-lg-6">{$fax_number_1.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$fax_number_2.LABEL}</label></div>
                        <div class="col-lg-6">{$fax_number_2.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$email.LABEL}</label></div>
                        <div class="col-lg-6">
                            <div class="input-group">
                                <span class="input-group-addon">@</span>
                                {$email.INPUT}
                                <a href="#" class="glyphicon glyphicon-exclamation-sign hidden-tooltip" data-toggle="tooltip" data-placement="auto" title="" data-original-title="{$TOOLTIP_EMAIL}"></a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$province.LABEL}</label></div>
                        <div class="col-lg-6">{$province.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$city.LABEL}</label></div>
                        <div class="col-lg-6">{$city.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$address.LABEL}</label></div>
                        <div class="col-lg-6">{$address.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$company.LABEL}</label></div>
                        <div class="col-lg-6">{$company.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$contact_person.LABEL}</label></div>
                        <div class="col-lg-6">{$contact_person.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$contact_person_position.LABEL}</label></div>
                        <div class="col-lg-6">{$contact_person_position.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$notes.LABEL}</label></div>
                        <div class="col-lg-6">{$notes.INPUT}</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6"><label>{$picture.LABEL}</label></div>
                        <div class="col-lg-6">{$picture.INPUT}<input type="hidden" name="image" value=""></div>
                    </div>
                </div>
                <div class="col-lg-4" id="previews">
                    <div class="row" >
                        <div class="col-md-12"><p> </p></div>
                    </div>
                    <img id='preview' class="img-responsive" alt='image' src='index.php?menu=contacts&action=getImageExtContact&image={$ID_PICTURE}&rawmode=yes'/>
                </div>
            </div>

            <div class="row" >
                <div class="col-md-6"><p> </p></div>
            </div>
        </div>
            
    </div>
</div>
