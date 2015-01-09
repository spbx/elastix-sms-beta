<script language="JavaScript" type="text/javascript" src="{$relative_dir_rich_text}/richtext/html2xhtml.js"></script>
<script language="JavaScript" type="text/javascript" src="{$relative_dir_rich_text}/richtext/richtext_compressed.js"></script>
<script language="JavaScript" type="text/javascript">
//Usage: initRTE(imagesPath, includesPath, cssFile, genXHTML, encHTML)
initRTE("./{$relative_dir_rich_text}/richtext/images/", "./{$relative_dir_rich_text}/richtext/", "", true);
var rte_script = new richTextEditor('rte_script');
</script>

<form id="campaign" name="campaign" method="POST" enctype="multipart/form-data">
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/list.png" border="0" align="absmiddle" />&nbsp;&nbsp;{$title}</td>
</tr>
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          {if $mode eq 'input'}
          <input class="button" type="submit" name="save" value="{$SAVE}"">
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
          {elseif $mode eq 'edit'}
          <input class="button" type="submit" name="apply_changes" value="{$APPLY_CHANGES}" onclick="return enviar_datos();">
          {if !$queue}
          <input class="button" type="submit" name="delete" value="{$DELETE}" onClick="return confirmSubmit('{$CONFIRM_DELETE}');">
	   {/if}
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
          <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
          {else}
          <input class="button" type="submit" name="edit" value="{$EDIT}">
          <input class="button" type="button" name="desactivar" value="{$DESCATIVATE}"  onClick="if(confirmSubmit('{$CONFIRM_CONTINUE}'))desactivar_campania();">

	   {if !$queue}
          <input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_DELETE}');">
	   {/if}

          <input class="button" type="button" name="cancel_view" value="{$CANCEL}" onclick="window.open('?menu=campaign_out','_parent');"></td>
          {/if}          
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
          <td width="170px">{$nombre.LABEL}: <span  class="required">*</span></td>
          <td colspan='2'>{$nombre.INPUT}</td>
      </tr>
      <tr>
          <td width="170px">{$cdr.LABEL}:</td>
          <td colspan='2'>{$cdr.INPUT}</td>
      </tr>
      <tr>
    	<td>{$PHONE_FILE}:</td>
    	<td  colspan='4'><input type='file' name='phonefile'></td>
      </tr>
      </table>
    </td>
  </tr>
</table>
<input type="hidden" name="id_campaign" id='id_campaign' value="{$id_campaign}" />
<input type="hidden" name="values_form" id='values_form' value="" />    
</form>
{literal}
    <script type="text/javascript">
	function contar(form,name,count,limit) {
	  n = document.forms[form][name].value.length;
	  t = limit;
	  if (n > t) {
	    document.forms[form][name].value = document.forms[form][name].value.substring(0, t);
	  }
	  else {
	    document.forms[form][count].value = t-n;
	  }
	}
    </script>
{/literal}
