<form style='margin-bottom:0;' method='POST' action='?menu=userlist'>
  <table width="100%" border="0" cellspacing="0" cellpadding="8" align="center">
      {if $USERLEVEL eq 'superadmin'}
        <tr class="letra12">
            <td >{$idOrganization.LABEL}: {$idOrganization.INPUT}</td>
        </tr>
      {/if}
      <tr class="letra12">
        <td >{$username.LABEL}: {$username.INPUT} {$SEARCH}</td>
      </tr>
   </table>
</form>