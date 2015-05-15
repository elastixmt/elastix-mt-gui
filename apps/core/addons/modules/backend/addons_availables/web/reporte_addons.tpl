{* Este DIV se usa para mostrar los mensajes de error *}
<div
    id="neo-addons-error-message"
    class="ui-corner-all"
    style="display: none;">
    <p>
        <span class="ui-icon" style="float: left; margin-right: .3em;"></span>
        <span id="neo-addons-error-message-text"></span>
    </p>
</div>
  <div class="neo-addons-header-row">
    <div class="neo-addons-header-row-filter">
      {$filter_by}:
      <select id="filter_by" class="neo-addons-header-row-select" name="filter_by" onchange="javascript:do_listarAddons(null)">
        <option value="available">{$available}</option>
        <option value="installed">{$installed}</option>
        <option value="purchased">{$purchased}</option>
        <option value="update_available">{$update_available}</option>
      </select>
    </div>
    <div class="neo-addons-header-row-filter">
      <span style="vertical-align:top;">{$name}:</span>
      <input type="text" id="filter_namerpm" value="" name="filter_namerpm" onkeypress="javascript:keyPressed(event)">
      <a onclick="javascript:do_listarAddons(null)" href="#">
      <img width="19" height="21" border="0" align="absmiddle" src="web/apps/{$module_name}/images/searchw.png" alt="">
      </a>
    </div>
    <div class="neo-addons-header-row-navigation">
        <img id="imgPrimero" style="cursor: pointer;" src="web/apps/{$module_name}/images/table-arrow-first.gif" width="16" height="16" alt='{$lblStart}' align='absmiddle' />
        <img id="imgAnterior"  style="cursor: pointer;" src="web/apps/{$module_name}/images/table-arrow-previous.gif" width="16" height="16" alt='{$lblPrevious}' align='absmiddle' />
        ({$showing} <span id="addonlist_start_range">?</span> - <span id="addonlist_end_range">?</span> {$of} <span id="addonlist_total">?</span>)
        <img id="imgSiguiente" style="cursor: pointer;" src="web/apps/{$module_name}/images/table-arrow-next.gif" width="16" height="16" alt='{$lblNext}' align='absmiddle' />
        <img id="imgFinal" style="cursor: pointer;" src="web/apps/{$module_name}/images/table-arrow-last.gif" width="16" height="16" alt='{$lblEnd}' align='absmiddle' />
    </div>
  </div>
<div id="addonlist">
<div style="text-align: center; padding: 40px;">
<img src="{$WEBCOMMON}images/loading.gif" />
</div>
</div>
     <div id="footer" style="background: url(web/apps/{$module_name}/images/addons_header_row_bg.png) repeat-x top; width: 100%; height:40px;"  >
     <div class="neo-addons-header-row-navigation">
        <img id="imgPrimeroFooter" style="cursor: pointer;" src="web/apps/{$module_name}/images/table-arrow-first.gif" width="16" height="16" alt='{$lblStart}' align='absmiddle' />
        <img id="imgAnteriorFooter"  style="cursor: pointer;" src="web/apps/{$module_name}/images/table-arrow-previous.gif" width="16" height="16" alt='{$lblPrevious}' align='absmiddle' />
        ({$showing} <span id="addonlist_start_range_footer">?</span> - <span id="addonlist_end_range_footer">?</span> {$of} <span id="addonlist_total_footer">?</span>)
        <img id="imgSiguienteFooter" style="cursor: pointer;" src="web/apps/{$module_name}/images/table-arrow-next.gif" width="16" height="16" alt='{$lblNext}' align='absmiddle' />
        <img id="imgFinalFooter" style="cursor: pointer;" src="web/apps/{$module_name}/images/table-arrow-last.gif" width="16" height="16" alt='{$lblEnd}' align='absmiddle' />
    </div>
    </div>
<!-- Neo Progress Bar -->
<div class="neo-modal-box">
  <div id="container">
    <div class="neo-progress-bar-percentage"><span class="neo-progress-bar-percentage-tag"></span></div>
    <div class="neo-progress-bar"><div class="neo-progress-bar-progress"></div></div>
    <span class="neo-progress-bar-label"><img src="{$WEBCOMMON}images/loading2.gif" align="absmiddle" />&nbsp;<span id="feedback"></span></span>
    <div class="neo-progress-bar-title"></div>
    <div class="neo-progress-bar-close"></div>
  </div>
</div>
<div class="neo-modal-blockmask"></div>
