<div id="paneldiv">  
    <div id='list_folders'>
        <div id="leftdiv">
            <div id="b1_1">
                <div class="folder" id='folder_list_title'>{$FOLDER_LIST_TITLE}</div>
                {foreach from=$MAILBOX_FOLDER_LIST key=k item=M_FOLDER}
                    {if $M_FOLDER eq $CURRENT_MAILBOX}
                        <div class="folder folder-item" data-foldername="{$M_FOLDER}" style="color:#dd271d;">{$M_FOLDER}</div>
                    {else}
                        <div class="folder folder-item" data-foldername="{$M_FOLDER}">{$M_FOLDER}</div>
                    {/if}
                {/foreach}
                <div style='display:none; padding:2px;'><input type="text" class="form-control" name='new_mailbox_name'></div>
                <div class="folder"><a href='#' onclick='new_folder()'>{$NEW_FOLDER}</a></div>
            </div>
        </div>
        <div id="display1" class="ra_disp1_10">
            <div id="icn_disp1" class="ra_disp1_10" >
                <span style='width:30px;' class="glyphicon glyphicon-folder-open"></span>
            </div>  
        </div>
    </div>
    <div id="centerdiv">
        <div id="b2_1">
            <div id="mail_toolbar" >
                <div id="tools-mail_toolbar" style="overflow: visible!important;">
                    <div id="tools-mail_toolbar-1" style='margin-left:45px; min-height:40px; padding-top: 5px;'>
                        <div class="elx_email_pag_bar elx_email_pag_btn" id="email_refresh">
                            <span class="glyphicon glyphicon-refresh" style='color:blue'></span>
                        </div>
                        <div class="elx_email_pag_bar elx_email_pag_btn">
                            <img src='web/apps/home/images/new.png' onclick="elx_newEmail(false)" class='elx-toolmail1-img'></img>
                        </div>
                        <div class="elx_email_pag_bar elx_email_pag_btn">
                            <img src='web/apps/home/images/delete.png' id="email_trash" class='elx-toolmail1-img'></img>
                        </div>
                        <div class="elx_email_pag_bar elx_email_pag_btn" style='display:none'>
                            <img src='web/apps/home/images/reply.png' class='elx-toolmail1-img'></img>
                        </div>
                        <div id='elx_email_mark_as' class='elx_email_pag_bar elx_email_pag_btn'>
                            <div class="btn-group">
                                <img src='web/apps/home/images/tag.png' class='elx-toolmail1-img dropdown-toggle' data-toggle="dropdown"></img>
                                <ul class="dropdown-menu" role="menu">
                                {foreach from=$ELX_MAIL_MARK_OPT key=k item=opt}
                                    <li><a href="#" onclick='mark_email_msg_as("{$k}")'>{$opt}</a></li>
                                {/foreach}
                                </ul>
                            </div>
                        </div>
                        <div id='elx_email_mv' class='elx_email_pag_bar elx_email_pag_btn'>
                            <div class="btn-group" > 
                                <img src='web/apps/home/images/mvmsg.png' class='elx-toolmail1-img dropdown-toggle' data-toggle="dropdown"></img>
                                <ul id='elx_email_mv_ul' class="dropdown-menu" role="menu">
                                {foreach from=$MOVE_FOLDERS key=k item=mv_folder}
                                    <li><a href="#" data-nameFolder="{$k}" class='elx_amvfolder'>{$mv_folder}</a></li>
                                {/foreach}
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div id="tools-mail_toolbar-2">
                        <div id='elx_lmail_action1' class='elx_email_pag_bar'>                        
                            <div class="btn-group" style='padding-left:5px'>
                                <input type='checkbox' class='inp1' id='select_all_emailview' onclick='select_all_emailview()'/>
                                {$VIEW} : <span id='elx_sel_view_filter'>{$ELX_MAIL_FILTER_OPT[$SELECTED_VIEW_FILTER]} </span>
                                <span class="dropdown-toggle caret" data-toggle="dropdown"></span>
                                <ul class="dropdown-menu" role="menu">
                                {foreach from=$ELX_MAIL_FILTER_OPT key=k item=view_filter}
                                    <li><a href="#" id='elx_email_vsel_{$k}' onclick='search_email_message_view("{$k}")'>{$view_filter}</a></li>
                                {/foreach}
                                </ul>
                            </div>
                            <input type='hidden' name='elx_sel_view_filter_h' value='{$SELECTED_VIEW_FILTER}' data-value='{$ELX_MAIL_FILTER_OPT[$SELECTED_VIEW_FILTER]}'>
                        </div>
                    </div>
                </div>
                <div id='elx-bodymsg-tools' style='display:none; overflow: visible!important; margin-left:45px'>
                    <div id='elx-bodymsg-tools-view' style='display:none;overflow: visible!important;'>
                        <div class="elx_email_pag_bar">
                            <button type="button" class="btn btn-default btn-sm btn-bodymsg-tools" onclick='return_mailbox()'>
                                <span class="glyphicon glyphicon-backward"></span> 
                            </button>
                        </div>
                        <div class='elx_email_pag_bar'>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm">
                                    <span >{$ACTION_MSG}:</span>
                                </button>
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle btn-bodymsg-tools" data-toggle="dropdown">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    {foreach from=$ELX_EMAIL_MSG_ACT key=k item=opt}
                                        <li><a href="#" onclick='actions_email_msg("{$k}")'>{$opt}</a></li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                        <div id='elx_email_msg_arrows' class='elx_email_pag_bar' style='float:right'>
                            <button type="button" class="btn btn-default btn-sm btn-bodymsg-tools" onclick='elx_email_prev_msg()'>
                                <span class="glyphicon glyphicon-arrow-left"></span> 
                            </button>
                            <button type="button" class="btn btn-default btn-sm btn-bodymsg-tools" onclick='elx_email_next_msg()'>
                                <span class="glyphicon glyphicon-arrow-right"></span> 
                            </button>
                        </div>
                    </div>
                    <div id='elx-bodymsg-tools-sent' style='display:none;overflow: visible!important;'>
                        <div class="elx_email_pag_bar">
                            <button type="button" class="btn btn-default btn-sm btn-bodymsg-tools" onclick='composeEmail("mailmodule")' >
                                <span>{$SEND_MAIL_LABEL}</span> 
                            </button>
                        </div>
                        <div class="elx_email_pag_bar">
                            <button type="button" class="btn btn-default btn-sm btn-bodymsg-tools" id='elx_attachButton'>
                                <span>{$ATTACH_LABEL}</span>
                                <input type='file' name='attachFileButton' id='attachFileButton'>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="elx_div_email_listmsg">
                <div id="elx_list_mail_messages" class='email_contentdiv'>
                    {if empty($MAILS)}
                        <div class="elx_row elx_unseen_email" style="text-align:center">{$NO_EMAIL_MSG}</div>
                    {else}
                        {foreach from=$MAILS key=k item=MAIL}
                            {foreach from=$MAIL key=j item=col_mail}
                                {$col_mail}
                            {/foreach}
                        {/foreach}
                    {/if}
                </div>
                <div id='elx_mail_pagingbar' >
                    <div id='elx_mail_pagingbar_icons'>
                        <div id='elx_mail_pagingbar_currentpg'>{$PAGINA} <span>{$CURRENT_PAGMAIL}</span></div>
                        <span class="glyphicon glyphicon-step-backward elx_mail_pagingbar_icon" data-actionpage='start'></span>
                        <span class="glyphicon glyphicon-chevron-left elx_mail_pagingbar_icon" data-actionpage='prev'></span>
                        <span class="glyphicon glyphicon-chevron-right elx_mail_pagingbar_icon" data-actionpage='next'></span>
                        <span class="glyphicon glyphicon-step-forward elx_mail_pagingbar_icon" data-actionpage='end'></span>
                    </div>
                    <div id='elx_mail_pagingbar_nummails'>
                        <span>{$TOTAL_MAILS}</span> {$MESSAGES_LABEL}
                    </div>
                </div>
                <input type='hidden' name='elx_currentpage' value="{$CURRENT_PAGMAIL}">
                <input type='hidden' name='elx_numpages' value="{$NUM_PAGMAIL}">
                <input type='hidden' name='action_paging' value="">
            </div>
            <div id="elx_elx_viewcmpmsg" style='display:none'>
                <div id="elx_bodymail" class='email_contentdiv'>
                </div>
            </div>
        </div>
    </div>
</div>
<input type='hidden' name='current_mailbox' value='{$CURRENT_MAILBOX}'>
{if $IMAP_ALERTS || $ERROR_FIELD}
    {literal}
        <script type="text/Javascript">
    {/literal}
        var strimap_alert = '{$IMAP_ALERTS}'+'{$ERROR_FIELD}';
    {literal}
        showElxUFMsgBar('error',strimap_alert);
        </script>
    {/literal}
{/if}
