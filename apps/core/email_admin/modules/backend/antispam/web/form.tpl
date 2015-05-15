<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        {if $EDIT}<td align="left"><input class="button" type="submit" name="update" value="{$UPDATE}"></td>{/if}
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="left" width="18%"><b>{$status.LABEL}: </b></td>
        <td align="left" colspan="2">{$status.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left" width="18%"><b>{$level}: </b></td>
        <td align="left" width="20%">
            <div>
                <p style="margin: 0px;">
                    <label for="amount">{$level}:</label>
                    <span id="amount" name="amount" style="border:0; color:#f6931f; font-weight:bold;"></span>
                </p>
                <div id="slider-range-max"></div>
            </div>
        </td>
        <td align="left">{$LEGEND}</td>
    </tr>
    <tr class="letra12" height="30px">
        <td align="left" width="18%"><b>{$politica.LABEL}: </b></td>
        <td align="left" colspan="2">{$politica.INPUT} &nbsp;&nbsp;{$header.INPUT} &nbsp;&nbsp;{$time_spam.INPUT}</td>
    </tr>
</table>

<input type="hidden" value="{$levelNUM}" id="levelnum" name="levelnum" />
<input type="hidden" value="{$statusSpam}" id="statusSpam" name="statusSpam" />
<input type="hidden" value="{$statusSieve}" id="statusSieve" name="statusSieve" />
