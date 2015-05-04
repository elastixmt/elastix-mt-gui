<form method="post">
    <table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td>
                <table width="100%" cellpadding="4" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <input class="button" name="in_actualizar_conf_red" value="{$CONFIGURATION_UPDATE}" type="submit" />&nbsp;&nbsp;
                            {if $SERVICE_STARING}
                                <input class="button" name="in_finalizar"           value="{$SERVICE_STOP}"         type="submit" />
                            {else}
                                <input class="button" name="in_iniciar"             value="{$SERVICE_START}"        type="submit" />&nbsp;&nbsp;
                            {/if}
                        </td>
                        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
                    <tr> 
                        <td width="26%"><b>{$STATUS}:</b></td>
                        <td><b>{$DHCP_STATUS}</b></td>
                    </tr>
                    <tr> 
                        <td width="26%"><b>{$START_RANGE_OF_IPS}: <span  class="required">*</span></b></td>
                        <td> 
                            {$in_ip_ini_1.LABEL}{$in_ip_ini_1.INPUT}
                            <b>{$in_ip_ini_2.LABEL}</b>{$in_ip_ini_2.INPUT}
                            <b>{$in_ip_ini_3.LABEL}</b>{$in_ip_ini_3.INPUT}
                            <b>{$in_ip_ini_4.LABEL}</b>{$in_ip_ini_4.INPUT}
                        </td>
                    </tr>
                    <tr> 
                        <td width="26%"><b>{$END_RANGE_OF_IPS}: <span  class="required">*</span></b></td>
                        <td> 
                            {$in_ip_fin_1.LABEL}{$in_ip_fin_1.INPUT}
                            <b>{$in_ip_fin_2.LABEL}</b>{$in_ip_fin_2.INPUT}
                            <b>{$in_ip_fin_3.LABEL}</b>{$in_ip_fin_3.INPUT}
                            <b>{$in_ip_fin_4.LABEL}</b>{$in_ip_fin_4.INPUT}
                        </td>
                    </tr>
                    <tr> 
                        <td width="26%"><b>{$in_lease_time.LABEL} </b><span  class="required">*</span></td>
                        <td>{$in_lease_time.INPUT}&nbsp;&nbsp;({$OF_1_TO_50000_SECONDS})</td>
                    </tr>
                    <tr> 
                        <td width="26%"><b>{$DNS_1}: </td>
                        <td> 
                            {$in_dns1_1.LABEL}{$in_dns1_1.INPUT}
                            <b>{$in_dns1_2.LABEL}</b>{$in_dns1_2.INPUT}
                            <b>{$in_dns1_3.LABEL}</b>{$in_dns1_3.INPUT}
                            <b>{$in_dns1_4.LABEL}</b>{$in_dns1_4.INPUT}&nbsp;&nbsp;({$OPTIONAL})
                        </td>
                    </tr>
                    <tr> 
                        <td width="26%"><b>{$DNS_2}: </b></td>
                        <td> 
                            {$in_dns2_1.LABEL}{$in_dns2_1.INPUT}
                            <b>{$in_dns2_2.LABEL}</b>{$in_dns2_2.INPUT}
                            <b>{$in_dns2_3.LABEL}</b>{$in_dns2_3.INPUT}
                            <b>{$in_dns2_4.LABEL}</b>{$in_dns2_4.INPUT}&nbsp;&nbsp;({$OPTIONAL})
                        </td> 
                    </tr>
                    <tr> 
                        <td width="26%"><b>{$WINS}: </b></td>
                        <td>
                            {$in_wins_1.LABEL}{$in_wins_1.INPUT}
                            <b>{$in_wins_2.LABEL}</b>{$in_wins_2.INPUT}
                            <b>{$in_wins_3.LABEL}</b>{$in_wins_3.INPUT}
                            <b>{$in_wins_4.LABEL}</b>{$in_wins_4.INPUT}&nbsp;&nbsp;({$OPTIONAL})
                        </td> 
                    </tr>
                    <tr> 
                        <td width="26%"><b>{$GATEWAY}: </b></td>
                        <td> 
                            {$in_gw_1.LABEL}{$in_gw_1.INPUT}
                            <b>{$in_gw_2.LABEL}</b>{$in_gw_2.INPUT}
                            <b>{$in_gw_3.LABEL}</b>{$in_gw_3.INPUT}
                            <b>{$in_gw_4.LABEL}</b>{$in_gw_4.INPUT}&nbsp;&nbsp;({$OPTIONAL})
                        </td>
                    </tr>
                   <!-- <tr> 
                        <td width="22%"><b>{$GATEWAY}: <span  class="required">*</span></b></td>
                        <td> 
                            {$in_gwm_1.LABEL}{$in_gwm_1.INPUT}
                            <b>{$in_gwm_2.LABEL}</b>{$in_gwm_2.INPUT}
                            <b>{$in_gwm_3.LABEL}</b>{$in_gwm_3.INPUT}
                            <b>{$in_gwm_4.LABEL}</b>{$in_gwm_4.INPUT}
                        </td>
                    </tr>-->
                </table>
            </td>
        </tr>
    </table>
</form>
