<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
        <title>Elastix</title>

<link rel="stylesheet" href="https://cdn.oesmith.co.uk/morris-0.4.3.min.css">

    <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/js/jquery-ui/css/no-theme/jquery-ui-1.10.3.custom.min.css">
    <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/font-icons/entypo/css/entypo.css">
    <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/font-icons/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic">
    <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/css/bootstrap.css">
    <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/css/neon-core.css">
    <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/css/neon-theme.css">
    <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/css/neon-forms.css">
    <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/css/custom.css">
    
    <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/styles.css" />
    <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/help.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="{$WEBPATH}themes/{$THEMENAME}/header.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="{$WEBPATH}themes/{$THEMENAME}/content.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="{$WEBPATH}themes/{$THEMENAME}/applet.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="{$WEBPATH}themes/{$THEMENAME}/sticky_note.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="{$WEBPATH}themes/{$THEMENAME}/table.css" />

    {$HEADER_LIBS_JQUERY}
        <script type='text/javascript' src="{$WEBCOMMON}js/base.js"></script>
        <script type='text/javascript' src="{$WEBCOMMON}js/sticky_note.js"></script>
        <script type='text/javascript' src="{$WEBCOMMON}js/iframe.js"></script>

        {$HEADER}
    {$HEADER_MODULES}

<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="https://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>
<script src="/admin/web/themes/tenant/js/jquery.easypiechart.js"></script>

    </head>
    <body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="mainBody page-body" {$BODYPARAMS}>
    <div class="page-container">

        {$MENU} <!-- Viene del tpl menu.tlp-->
        {if !empty($mb_message)}
        <div class="div_msg_errors" id="message_error">
                    <div style="height:24px">
                        <div class="div_msg_errors_title" style="padding-left:5px">
                            <b style="color:red;">&nbsp;{$mb_title}</b>
                        </div>
                        <div class="div_msg_errors_dismiss">
                            <input type="button" onclick="hide_message_error();" value="{$md_message_title}"/>
                        </div>
                    </div>
            <div style="padding:2px 10px 2px 10px">
            {$mb_message}
            </div>
        </div>
        {/if}
                {$CONTENT}
            </div>
            </div>
            
        </div>

        <!-- Footer -->
        <footer class="main" style="margin-left:22px;">
            <a href="http://www.elastix.org" style="color: #444; text-decoration: none;" target='_blank'>Elastix</a> is licensed under <a href="http://www.opensource.org/licenses/gpl-license.php" target='_blank' style="color: #444; text-decoration: none;" >GPL</a> by <a href="http://www.palosanto.com" target='_blank' style="color: #444; text-decoration: none;">PaloSanto Solutions</a>. 2006 - {$currentyear}.
        </footer>

        <br />

         <div id="neo-sticky-note" class="neo-display-none">
                  <div id="neo-sticky-note-text"></div>
                  <div id="neo-sticky-note-text-edit" class="neo-display-none">
                        <textarea id="neo-sticky-note-textarea"></textarea>
                        <div id="neo-sticky-note-text-char-count"></div>
                        <input type="button" value="{$SAVE_NOTE}" class="neo-submit-button" id="neo-submit-button" onclick="send_sticky_note()" />
                        <div id="auto-popup">AutoPopUp <input type="checkbox" id="neo-sticky-note-auto-popup" value="1"></div>
                  </div>
                  <div id="neo-sticky-note-text-edit-delete"></div>
                </div>


        <!-- Neo Progress Bar -->
        <div class="neo-modal-elastix-popup-box">
            <div class="neo-modal-elastix-popup-title"></div>
            <div class="neo-modal-elastix-popup-close"></div>
            <div class="neo-modal-elastix-popup-content"></div>
        </div>
        <div class="neo-modal-elastix-popup-blockmask"></div>

    <!-- Bottom Scripts -->
    <script type='text/javascript' src="{$WEBPATH}themes/{$THEMENAME}/js/gsap/main-gsap.js"></script>
    <script type='text/javascript' src="{$WEBPATH}themes/{$THEMENAME}/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
    <script type='text/javascript' src="{$WEBPATH}themes/{$THEMENAME}/js/bootstrap.js"></script>
    <script type='text/javascript' src="{$WEBPATH}themes/{$THEMENAME}/js/joinable.js"></script>
    <script type='text/javascript' src="{$WEBPATH}themes/{$THEMENAME}/js/resizeable.js"></script>
    <script type='text/javascript' src="{$WEBPATH}themes/{$THEMENAME}/js/neon-api.js"></script>
    <script type='text/javascript' src="{$WEBPATH}themes/{$THEMENAME}/js/jquery.validate.min.js"></script>
    <script type='text/javascript' src="{$WEBPATH}themes/{$THEMENAME}/js/neon-login.js"></script>
    <script type='text/javascript' src="{$WEBPATH}themes/{$THEMENAME}/js/neon-custom.js"></script>
    <script type='text/javascript' src="{$WEBPATH}themes/{$THEMENAME}/js/neon-demo.js"></script>
    </div>
    </body>
</html>
