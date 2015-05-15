{literal}
<link rel="stylesheet" href="web/_common/js/jquery/css/blitzer/jquery-ui-1.8.24.custom.css">
{/literal}
<div id="contsetting">
    
    <div class="my_settings">

        <div class="row">
            <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><button class="button btn btn-default btn-sm" type="button" name="save_new" onclick='saveVacation()'> <span class="glyphicon glyphicon-ok"></span> {$ENABLE_VACATION_MESS_BTN}</div>
        </div>        
        
        <div class="row" >
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><p> </p></div>
        </div>

        <div class="row elx-modules-content">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3"><label>{$PERIOD_LABEL}</label></div>
                <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
                    <label>{$FROM.LABEL}</label>
                    {$FROM.INPUT}
                    
                    <label>{$TO.LABEL}</label>
                    {$TO.INPUT}
                
                    <label id="num_days"> 0 </label> <label><strong>{$LABEL_DAYS}</strong></label>


                </div>
            
            </div>

            <div class="row">
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$EMAIL_ADDRESS.LABEL}</label></div>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-9"><div class="input-group"><span class="input-group-addon">@</span>{$EMAIL_ADDRESS.INPUT}</div></div>
            </div>

            <div class="row">
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$EMAIL_SUBJECT.LABEL}</label></div>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-9">
                    {$EMAIL_SUBJECT.INPUT}
                    <a href="#" class="glyphicon glyphicon-exclamation-sign hidden-tooltip" data-toggle="tooltip" data-placement="auto" title="" data-original-title="Can not be empty"></a>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3"><label>{$EMAIL_CONTENT.LABEL}</label></div>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-6">
                    {$EMAIL_CONTENT.INPUT}
                </div>
            </div>
            <div class="row" >
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><p> </p></div>
            </div>
        </div>

    </div>
</div>
