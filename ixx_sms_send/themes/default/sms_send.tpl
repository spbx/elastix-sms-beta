<input type="hidden" id="phone_type">
<input type="hidden" id="phone_id">
<input type='hidden' id='estaus_reloj' value='apagado' />
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/list.png" border="0" align="absmiddle">&nbsp;&nbsp;{$TITLE}</td>
        <td align="right" nowrap class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
    <tr>
        <td colspan=2>
            <table width="100%" cellpadding="0" cellspacing="0" border="0" class="tabForm">
		  <tr>
		    <td>
			<table cellpadding="0" cellspacing="0" border="0"  border="0">
	                <tr class="letra12">
	                     <td>{$call_to.LABEL}: <span  class="required">*</span></td>
	      	             <td>{$call_to.INPUT}&nbsp;&nbsp;<a href='javascript: popup_phone_number("modules/calendar/phone_numbers.php");'>{$ADDRESS_BOOK}</a></td>
	                </tr>
	                <tr class="letra12">
	                     <td>{$text.LABEL}: <span  class="required">*</span></td>
	       	             <td>{$text.INPUT}</td>
	                </tr>
	                <tr class="letra12">
	                     <td>{$encolar.LABEL}:</td>
		             <td>{$encolar.INPUT}</td>
	                </tr>
	                <tr class="letra12">
	                     <td colspan="2">&nbsp;</td>
	                </tr>
	                <tr class="letra12">
	                     <td>{$count.LABEL}:</td><td>{$count.INPUT}&nbsp;&nbsp;{$messages.LABEL}:&nbsp;&nbsp;{$messages.INPUT}</td>
	                </tr>
	                <tr class="letra12">
	                     <td colspan="2">&nbsp;</td>
	                </tr>
	                <tr class="letra12">
	                     <td>{$trunk.LABEL}:</td>
		             <td>{$trunk.INPUT}</td>
	                </tr>
	                <tr class="letra12">
	                    <td colspan="2">&nbsp;</td>
	                </tr>
			<tr>
		       	    <td align="left" colspan="2">
		      	      <input class="button" type="button" value="{$SEND}" onclick="send_sms()">&nbsp;&nbsp;&nbsp;&nbsp;
		              <input class="button" type="submit" name="cancel" value="{$CANCEL}">
       			    </td>
        	      	</tr>
			<tr>
		             <td colspan="2" id='relojArena'></td>
			</tr>
			</table>
		     </td>	
                 </tr>			
            </table>
        </td>
      </tr>
</table>
{literal}
    <script language="JavaScript" src="modules/ixx_sms_send/libs/sms_{/literal}{$language}{literal}.js"></script>
    <script language="JavaScript" src="modules/ixx_sms_send/libs/sms.js"></script>
{/literal}
