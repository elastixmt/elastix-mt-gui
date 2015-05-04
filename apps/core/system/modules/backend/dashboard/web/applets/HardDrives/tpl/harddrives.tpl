<link rel="stylesheet" media="screen" type="text/css" href="web/apps/{$module_name}/applets/HardDrives/tpl/css/styles.css" />
<script type='text/javascript' src='web/apps/{$module_name}/applets/HardDrives/js/javascript.js'></script>
{foreach from=$part item=particion}
<div>
    {if $fastgauge}
	<div style="width: {$htmldiskuse_width}px; height: {$htmldiskuse_height}px;">
		<div style="position: relative; left: 33%; width: 33%; background: #6e407e;  height: 100%; border: 1px solid #000000;">
			<div style="position: relative; background: #3184d5; top: {$particion.height_free}%; height: {$particion.height_used}%">&nbsp;</div>
		</div>
	</div>
	{else}
	<img alt="ObtenerInfo_Particion" src="?menu={$module_name}&amp;rawmode=yes&amp;applet=HardDrives&amp;action=graphic&amp;percent={$particion.porcentaje_usado}" width="140" />	
	{/if}
    <div class="neo-applet-hd-innerbox">
      <div class="neo-applet-hd-innerbox-top">
       <img src="web/apps/{$module_name}/applets/HardDrives/images/light_usedspace.png" width="13" height="11" alt="used" /> {$particion.formato_porcentaje_usado}% {$LABEL_PERCENT_USED} &nbsp;&nbsp;<img src="web/apps/{$module_name}/applets/HardDrives/images/light_freespace.png" width="13" height="11" alt="used" /> {$particion.formato_porcentaje_libre}% {$LABEL_PERCENT_AVAILABLE}
      </div>
      <div class="neo-applet-hd-innerbox-bottom">
        <div><strong>{$LABEL_DISK_CAPACITY}:</strong> {$particion.sTotalGB}GB</div>
        <div><strong>{$LABEL_MOUNTPOINT}:</strong> {$particion.punto_montaje}</div>
        <div><strong>{$LABEL_DISK_VENDOR}:</strong> {$particion.sModelo}</div>
      </div>
    </div>
</div>
{/foreach}

<div class="neo-divisor"></div>
<div id="harddrives_dirspacereport">
<p>{$TEXT_WARNING_DIRSPACEREPORT}</p>
<button class="submit" id="harddrives_dirspacereport_fetch" >{$FETCH_DIRSPACEREPORT}</button>
</div>