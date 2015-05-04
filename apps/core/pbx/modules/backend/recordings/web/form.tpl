<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr id="save">
        <td align="left" colspan='2'><input class="button" type="submit" name="save" value="{$SAVE}" {$DISABLED}/></td>
    </tr>
    <tr>
        <table class="tabForm" style="font-size: 16px;" width="100%" border='0'>
           {if $USERLEVEL ne 'superadmin'} 
            <tr class="letra12">
                <td colspan='2' style='padding-bottom: 2%;'>
                    <input type="radio" name="option_record" id="record_by_phone" value="by_record" {$check_record} onclick="Activate_Option_Record()" />
                    {$record} &nbsp;&nbsp;&nbsp;
                    <input type="radio" name="option_record" id="record_by_file" value="by_file" {$check_file} onclick="Activate_Option_Record()" />
                    {$file_upload}
                </td>
            </tr>
            <tr class="letra12" id='record_option'>
                <td align="left" width='15%'>{$recording_name_Label}</td>
                <td align="left">
                  <input size='30' name="recording_name" id="recording_name"  autofocus maxlength="46"  type="text" value="{$filename}" style="padding-left:1%"/>&nbsp;[.wav]
                </td>
            </tr>
            <tr class="letra12" id='record_option_exten'>
                <td align="left" width='13%'>{$record_on_extension}</td>
                <td align="left">
                    {$EXTENSION}
                    <input type="hidden" name="on_extension" id="on_extension"  value="{$EXTENSION}" />
                </td>
            </tr>
            <tr class="letra12" id='record_button' > 
                <td colspan="2">
                <div id="clock1" >
                    <div class="labelRecord">Recording...</div>
                    <div class="labelError"></div>
                    <div name="recording" onClick="checkName()" id="recording" class="start" style="border: 1px solid gray;">
                        <img src="web/apps/recordings/images/record.png" width="18px"/>
                        <span style='color: red; position: relative; bottom: 4px; font-weight: bold;'>REC</span>
                    </div>
                    <div name="stop" id="stop" class="stop" onClick="hangUp()" class="start" style="border: 1px solid gray; width:8%;">
                        <img src="web/apps/recordings/images/stop.png" width="18px" />
                        <span style='color: black; position: relative; bottom: 4px; font-weight: bold;'>STOP</span>
                    </div>
                </div>
                <div id="contplay" style="margin-left: 4%; margin-top: -1%;"><audio preload></audio></div>
                  <!--  <input class="button" title={$record} type="submit" name="record" id="record" value="{$record}"  {$DISABLED}/>-->
                </td>
            </tr> 
            {/if}
            {if $USERLEVEL eq 'superadmin'} 
            <tr class="letra12" id='upload_option'>
                <td align="left" width='13%'>{$organization.LABEL} : </td>
                <td>{$organization.INPUT}</td>
            </tr>
            {/if}
            <tr class="letra12" id='upload_option'>
                <td align="left" width='13%'>{$record_Label} : </td>
                <td align="left">
                    <input name="file_record" id="file_record" type="file" value="{$file_record_name}" size='30' {$DISABLED}/>
                </td>
            </tr>
        </table>
    </tr>
{if $USERLEVEL ne 'superadmin'} 
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm" id="info">
        <tr><td><i>{$INFO}</i></td></tr>
    </table>
{/if}
</table>
<input type='hidden' name='dialing' id='dialing' value="{$dialing}" />
<input type='hidden' name='domain' id='domain' value="{$domain}" />
<input type='hidden' name='checking' id='checking' value="{$checking}" />
<input type='hidden' name='filename' id='filename' value='' />
<input type='hidden' name='flag' id='flag' value='' />
<input type='hidden' name='dialog-confirm' id='dialog-confirm' value='{$overwrite_record}' />
<input type='hidden' name='success_record' id='success_record' value='{$success_record}' />
<input type='hidden' name='cancel_record' id='cancel_record' value='{$cancel_record}' />
<input type='hidden' name='hangup' id='hangup' value='{$hangup}' />
<input type='hidden' name='max_size' id='max_size' value='{$max_size}' />
<input type='hidden' name='alert_max_size' id='alert_max_size' value='{$alert_max_size}' />

<input type='hidden' name='cod' id='cod' value='' />
{if $USERLEVEL ne 'superadmin'} 
{literal}
    <script type="text/javascript">
        $('#clock1').stopwatch();     
    </script>
    <script type="text/javascript">
        Activate_Option_Record();

        function Activate_Option_Record()
        {
            var record_by_phone = document.getElementById('record_by_phone');
            var record_by_file = document.getElementById('record_by_file');
            if(record_by_phone.checked==true)
            {
                document.getElementById('record_option').style.display = '';
                document.getElementById('record_option_exten').style.display = '';
                document.getElementById('record_button').style.display = '';
                document.getElementById('info').style.display = '';
                document.getElementById('upload_option').style.display = 'none';
                document.getElementById('save').style.display = 'none';
            } else {
                document.getElementById('record_option').style.display = 'none';
                document.getElementById('record_option_exten').style.display = 'none';
                document.getElementById('record_button').style.display = 'none';
                document.getElementById('info').style.display = 'none';
                document.getElementById('upload_option').style.display = '';
                document.getElementById('save').style.display = '';
            }
        }
    </script>
{/literal}
{/if}
