<form id="config" name="config" method="POST" enctype="multipart/form-data">
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/list.png" border="0" align="absmiddle" />&nbsp;&nbsp;{$title}</td>
</tr>
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">          
          <input class="button" type="submit" name="save" value="{$SAVE}"">
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
          <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
	    <tr>
                <td style="width:200px;">{$country_code.LABEL}: <span  class="required">*</span></td>
	        <td>{$country_code.INPUT}</td>
	    </tr>
	    <tr>
	        <td>{$mobile_prefixes.LABEL}: <span  class="required">*</span></td>
	        <td>{$mobile_prefixes.INPUT}</td>
	    </tr>
	    <tr>
	        <td>{$min_mobile_length.LABEL}: <span  class="required">*</span></td>
	        <td>{$min_mobile_length.INPUT}</td>
	    </tr>
	    <tr>
	        <td>{$max_mobile_length.LABEL}: <span  class="required">*</span></td>
	        <td>{$max_mobile_length.INPUT}</td>
	    </tr>
	</table>
  </td>	
</tr>			
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
</form>
