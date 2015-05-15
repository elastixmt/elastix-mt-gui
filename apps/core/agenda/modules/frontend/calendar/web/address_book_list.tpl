<html>
    <head>
        <title>Elastix</title>
        <link rel="stylesheet" href="{$path}themes/{$THEMENAME}/styles.css">
        <link rel="stylesheet" href="{$path}themes/{$THEMENAME}/help.css">
        {if $THEMENAME eq "elastixneo"}
        <link rel="stylesheet" media="screen" type="text/css" href="themes/{$THEMENAME}/header.css" />
        <link rel="stylesheet" media="screen" type="text/css" href="themes/{$THEMENAME}/content.css" />
        <link rel="stylesheet" media="screen" type="text/css" href="themes/{$THEMENAME}/applet.css" />
        <link rel="stylesheet" media="screen" type="text/css" href="themes/{$THEMENAME}/table.css" />
        {/if}
        {$HEADER_LIBS_JQUERY}
        <script src="{$path}libs/js/base.js"></script>
        <script src="{$path}modules/{$MODULE_NAME}/themes/default/js/javascript.js"></script>
    </head>
    <body>
        {if $THEMENAME eq "elastixneo"}
          <div>
            <div class="neo-module-title"><div class="neo-module-name-left"></div><span class="neo-module-name">
              {if $icon ne null}
              <img src="{$icon}" width="22" height="22" align="absmiddle" />
              {/if}
              &nbsp;{$title}</span><div class="neo-module-name-right"></div>
              </div>
          <div class="neo-module-content">
              <div class="div_msg_errors" id="message_error" style="display:none;">
              <div style="float:left;">
                  <span id="mb_title" style="color:red;font-weight:bold"></span>
                  <br />
                  <span id="mb_message"></span>
              </div>
              <div style="text-align:right; padding:5px">
                  <input type="button" onclick="hide_message_error();" value="{$md_message_title}"/>
              </div>
            </div>
              {$CONTENT}
           </div>
        {else}
            <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="message_board">
              <tbody style="display:none" id="table_error"><tr>
                <td valign="middle" class="mb_title" id="mb_title"></td>
                </tr>
                <tr>
                    <td valign="middle" class="mb_message" id="mb_message"></td>
                </tr>
            </tbody></table>
            <div class="moduleTitle">
              &nbsp;&nbsp;<img src="{$icon}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}
            </div>
            {$CONTENT}
        {/if}
    </body>
</html>
