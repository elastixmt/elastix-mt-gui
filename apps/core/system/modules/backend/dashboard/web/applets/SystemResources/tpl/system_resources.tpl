<link rel="stylesheet" media="screen" type="text/css" href="web/apps/{$module_name}/applets/SystemResources/tpl/css/styles.css" />
<script type='text/javascript' src='web/apps/{$module_name}/applets/SystemResources/js/javascript.js'></script>
<div style='height:165px; position:relative; text-align:center;'>
    <div style='width:155px; float:left; position: relative;' id='cpugauge'>
	    {if $fastgauge}
	    <div style="width: 140px; height: 140px;">
	        <div style="position: relative; left: 33%; width: 33%; background: #ffffff;  height: 100%; border: 1px solid #000000;">
	            <div style="position: relative; background: {$cpugauge.color}; top: {$cpugauge.height_free}%; height: {$cpugauge.height_used}%">&nbsp;</div>
	        </div>
	    </div>
	    {else}
	    <img alt="rbgauge" src="" />
	    {* ?menu={$module_name}&amp;rawmode=yes&amp;applet=SystemResources&amp;action=graphic&amp;percent={$cpugauge.fraction} *}   
	    {/if}
        <div class="neo-applet-sys-gauge-percent">{$cpugauge.percent}%</div><div>{$LABEL_CPU}</div>
        <input type="hidden" name="cpugauge_value" id="cpugauge_value" value="{$cpugauge.fraction}" />
    </div>
    <div style='width:154px; float:left; position: relative;' id='memgauge'>
        {if $fastgauge}
        <div style="width: 140px; height: 140px;">
            <div style="position: relative; left: 33%; width: 33%; background: #ffffff;  height: 100%; border: 1px solid #000000;">
                <div style="position: relative; background: {$memgauge.color}; top: {$memgauge.height_free}%; height: {$memgauge.height_used}%">&nbsp;</div>
            </div>
        </div>
        {else}
        <img alt="rbgauge" src="" />
        {* ?menu={$module_name}&amp;rawmode=yes&amp;applet=SystemResources&amp;action=graphic&amp;percent={$memgauge.fraction} *}   
        {/if}
        <div class="neo-applet-sys-gauge-percent">{$memgauge.percent}%</div><div>{$LABEL_RAM}</div>
        <input type="hidden" name="memgauge_value" id="memgauge_value" value="{$memgauge.fraction}" />
    </div>
    <div style='width:155px; float:right; position: relative;' id='swapgauge'>
        {if $fastgauge}
        <div style="width: 140px; height: 140px;">
            <div style="position: relative; left: 33%; width: 33%; background: #ffffff;  height: 100%; border: 1px solid #000000;">
                <div style="position: relative; background: {$swapgauge.color}; top: {$swapgauge.height_free}%; height: {$swapgauge.height_used}%">&nbsp;</div>
            </div>
        </div>
        {else}
        <img alt="rbgauge" src="" />
        {* ?menu={$module_name}&amp;rawmode=yes&amp;applet=SystemResources&amp;action=graphic&amp;percent={$swapgauge.fraction} *}   
        {/if}
        <div class="neo-applet-sys-gauge-percent">{$swapgauge.percent}%</div><div>{$LABEL_SWAP}</div>
        <input type="hidden" name="swapgauge_value" id="swapgauge_value" value="{$swapgauge.fraction}" />
    </div>
</div>
<div class='neo-divisor'></div>
<div class=neo-applet-tline>
    <div class='neo-applet-titem'><strong>{$LABEL_CPUINFO}:</strong></div>
    <div class='neo-applet-tdesc'>{$cpu_info}</div>
</div>
<div class=neo-applet-tline>
    <div class='neo-applet-titem'><strong>{$LABEL_UPTIME}:</strong></div>
    <div class='neo-applet-tdesc'>{$uptime}</div>
</div>
<div class='neo-applet-tline'>
    <div class='neo-applet-titem'><strong>{$LABEL_CPUSPEED}:</strong></div>
    <div class='neo-applet-tdesc'>{$speed}</div>
</div>
<div class='neo-applet-tline'>
    <div class='neo-applet-titem'><strong>{$LABEL_MEMORYUSE}:</strong></div>
    <div class='neo-applet-tdesc'>RAM: {$memtotal} SWAP: {$swaptotal}</div>
</div>
