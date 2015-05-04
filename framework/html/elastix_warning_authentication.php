<?php
$theme         = isset($content['theme'])?$content['theme']:"elastixneo";
$size = "100%";
$styleBody = "";
$styleDiv = "";
switch($theme){
   case "elastixwave":
          $image = "/themes/elastixwave/images/logo_elastix.gif";
          break;
    case "default":
          $image = "/images/logo_elastix.png";
          break;
    case "elastixneo":
          $image = "/themes/elastixneo/images/elastix_logo_mini2.png";
          $size = "1270px";
          $styleBody = "style='background-image:url(/themes/elastixneo/images/bgbodytest.png);'";
          $styleDiv = "style='background:#FFFFFF; height:100%; width:1270px;'";
          break;
    default:
          $image = "/images/logo_elastix_new3.gif";
          break;
}
$theme         = "/themes/$theme";
$currentYear   = date("Y");
$msg           = isset($content['msg'])?$content['msg']:"";
$title         = isset($content['title'])?$content['title']:"";
?>

<html>
<head>
<title>Elastix - <?php echo $title; ?></title>
<link rel="stylesheet" href="<?php echo $theme; ?>/styles.css">
</head>

<body bgcolor="#ffffff" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" <?php echo $styleBody; ?> >
  <table cellspacing="0" cellpadding="0" width="<?php echo $size; ?>" border="0" class="menulogo2" height="74">
    <tr>
       <td class="menulogo" valign="top">
           <a target="_blank" href="http://www.elastix.org">
               <img border="0" src="<?php echo $image; ?>"/>
           </a>
       </td>
    </tr>
  </table>
  <div align="center" <?php echo $styleDiv; ?> >
    <?php echo $msg; ?>
  <div/>
  <br /><br />
  <div align="center" class="copyright"><a href="http://www.elastix.org" target='_blank'>Elastix</a> is licensed under <a href="http://www.opensource.org/licenses/gpl-license.php" target='_blank'>GPL</a> by <a href="http://www.palosanto.com" target='_blank'>PaloSanto Solutions</a>. 2006 - <?php echo $currentYear; ?>.</div>
  <br />
</body>
</html>