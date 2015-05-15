
	<div class="content_box">
		<div id="table_box">
			<table style="font-size: 16px;" width="99%" border="0">
				<tr class="letra12" height="30px" >
					<td align="left" width="90px"><b>{$event.LABEL}: <span  class="required">*</span></b></td>
					<td align="left" colspan="2">{$event.INPUT}</td>
				</tr>
				<tr class="letra12" id="desc">
					<td align="left"><b>{$description.LABEL}: </b></td>
					<td align="left" colspan="2">{$description.INPUT}</td>
				</tr>
				<tr class="letra12" height="30px">
					<td align="left" width="90px"><b>{$Start_date}: <span  class="required">*</span></b></td>
					<td align="left" width="175px">{$date.INPUT}</td>
					<td align="left"><b>{$Color}:</b>
						<div id="colorSelector" style="padding-top: 11px; display: inline;"><div style="background-color: #3366CC"></div></div>
					</td>
				</tr>
				<tr class="letra12">
					<td align="left" width="90px"><b>{$End_date}: <span  class="required">*</span></b></td>
					<td align="left" width="175px" colspan="3">{$to.INPUT}</td>
				</tr>
				<tr id="rowReminderEvent">
					<td align="left" colspan="2">
						<div id="divReminder">
							<input id="CheckBoxRemi" type="checkbox" class="CheckBoxClass" onclick="changeBoxReminder();"/>
							<label id="lblCheckBoxRemi" for="CheckBoxRemi" class="CheckBoxLabelClass">{$Call_alert}</label>
						</div>
					</td>
				</tr>
				<tr class="letra12" style="display: none; ">
					<td align="left" width="90px"><b>{$reminder.LABEL}: </b></td>
					<td align="left" id="remi">{$reminder.INPUT}</td>
				</tr>
				<tr class="letra12 remin"  colspan="3" style="{$visibility_alert}">
					<td align="right" colspan="3"><div id="label_call"></td>
				</tr>
				<tr class="letra12 remin" height="30px" id="check" style="{$visibility_alert}">
					<td align="left"><b>{$call_to.LABEL}: <span  class="required">*</span></b></td>
					<td align="left" colspan="2">{$call_to.INPUT}&nbsp;&nbsp;
						<span id="add_phone">
								<a href="javascript: popup_phone_number('?menu={$module_name}&amp;action=phone_numbers&amp;rawmode=yes');">{$add_phone}</a>
						</span>
					</td>
				</tr>
				<tr class="letra12 remin subElemento" height="30px" style="{$visibility_alert}">
					<td align="left"><b>{$ReminderTime.LABEL}: <span  class="required">*</span></b></td>
					<td align="left">{$ReminderTime.INPUT}&nbsp;&nbsp;</td>
				</tr>
				<tr class="letra12 remin subElemento" height="30px" style="{$visibility_alert}">
					<td align="left" colspan="3">
						<b>{$tts.LABEL}: <span  class="required">*</span>&nbsp;&nbsp;&nbsp;</b>
						<b><span class="counter">140</span></b>
						<a id="listenTTS" style="cursor: pointer;" onclick="clicklistenTTS();">
							<img src="modules/{$module_name}/images/speaker.png" style="position: relative; right: 0px;" alt="{$Listen}" title="{$Listen_here}"/>
						</a>
						<div>{$tts.INPUT}</div>
					</td>
				</tr>
				<tr id="rowNotificateEmail">
					<td align="left" colspan="3">
						<div id="divNotification">
							<input id="CheckBoxNoti" type="checkbox" class="CheckBoxClass" onclick="changeBoxNotification();"/>
							<label id="lblCheckBoxNoti" for="CheckBoxNoti" class="CheckBoxLabelClass">{$Notification_Alert}</label>
						</div>
					</td>
				</tr>
				<tr class="letra12" style="display: none;">
					<td align="left" width="90px"><b>{$notification.LABEL}: </b></td>
					<td align="left" id="noti">{$notification.INPUT}</td>
				</tr>
				<tr class="letra12" id="notification_email" style="display: none;">
					<td align="left" colspan="3">
						<div>
							<b id="notification_email_label">{$notification_email}: <span  class="required">*</span></b>
						</div>
						<div class="ui-widget">
							<textarea id="tags" cols="48px" rows="2" style="color: #333333; font-size:12px; width: 365px; height: 36px; "></textarea>
						</div>
					</td>
				</tr>
			</table>
			<div class="letra12 noti_email" id="email_to" style="{$visibility_emails}" align="center">
				<table id="grilla" style="font-size: 16px;" width="90%" border="0">
				</table>
			</div>
			<table width="100%" border="0" cellspacing="0" cellpadding="3" align="center">
				<tr class="letra12">
					<td align="left">
						<div id="new_box" style="display:none">
							<input id="save" class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
							<input id="cancel" class="button cancel" type="button" name="cancel" value="{$CANCEL}" onclick="closenewbox();">
						</div>
						<div id="view_box" style="display:none">
							<input id="edit" class="button" type="button" name="edit" value="{$EDIT}" onclick="editEvent();">
							<input id="delete" class="button" type="button" name="delete" value="{$DELETE}" onclick="deleteEvent();">
							<input id="cancel" class="button cancel" type="button" name="cancel" value="{$CANCEL}" onclick="closenewbox();">
						</div>
						<div id="edit_box" style="display:none">
							<input id="save" class="button" type="submit" name="save_edit" value="{$SAVE}">&nbsp;&nbsp;
							<input id="cancel" class="button cancel" type="button" name="cancel" value="{$CANCEL}" onclick="closenewbox();">
						</div>
					</td>
				</tr>
			</table>
			<input class="button" type="hidden" name="id_event" value="" id="id_event" />
			<input type="hidden" id="phone_type" name="phone_type" value="" />
			<input type="hidden" id="phone_id" name="phone_id" value="" />
			<input type="text" id="emails" name="emails" value="" style="display: none;" />
		</div>
	</div>	
<input class="button" type="hidden" name="lblEdit" value="{$LBL_EDIT}" id="lblEdit" />
<input class="button" type="hidden" name="lblLoading" value="{$LBL_LOADING}" id="lblLoading">
<input class="button" type="hidden" name="lblDeleting" value="{$LBL_DELETING}" id="lblDeleting">
<input class="button" type="hidden" name="lblSending" value="{$LBL_SENDING}" id="lblSending">
<input class="button" type="hidden" name="typeen" value="{$START_TYPE}...." id="typeen" />
<input class="button" type="hidden" name="dateServer" value="{$DATE_SERVER}" id="dateServer" />
<input class="button" type="hidden" name="colorHex" id="colorHex" value="#3366CC" />