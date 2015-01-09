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
        <td align="left">          {if $mode eq 'input'}
          <input class="button" type="submit" name="save" value="{$SAVE}" onclick="return save_campaign();">
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
          {elseif $mode eq 'edit'}
          {if $edit}
	          <input class="button" type="submit" name="apply_changes" value="{$APPLY_CHANGES}" onclick="return enviar_datos();">
	   {/if}
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
          <td>{$clid.LABEL}: </span></td>
          <td>{$clid.INPUT}</td>
      </tr>
      <tr>
           <td colspan='4'>&nbsp;</td>
      </tr>
      <tr>
          <td>{$fecha_ini.LABEL}: <span  class="required">*</span></td>
          <td>{$fecha_ini.INPUT}</td>
      </tr>
      <tr>
          <td>{$hora_str.LABEL}: <span  class="required">*</span></td>
          <td align='left' colspan='2'>{$hora_ini_HH.INPUT}&nbsp;:&nbsp;{$hora_ini_MM.INPUT}&nbsp;{$hora_ini_HH.LABEL}</td>
      </tr>
      <tr>
          <td>&nbsp;</td>
          <td align='left' colspan='2'>{$hora_fin_HH.INPUT}&nbsp;:&nbsp;{$hora_fin_MM.INPUT}&nbsp;{$hora_fin_HH.LABEL}</td>
      </tr>
      <tr>
           <td colspan='4'>&nbsp;</td>
      </tr>
      {if !$queue}
      <tr>
           <td>{$message.LABEL}: <span  class="required">*</span></td>
           <td>{$message.INPUT}</td>
      </tr>
      <tr>
           <td>{$count.LABEL}:</td>
	   <td>{$count.INPUT}&nbsp;&nbsp;{$messages.LABEL}:&nbsp;&nbsp;{$messages.INPUT}</td>
      </tr>
      <tr>
           <td colspan='4'>&nbsp;</td>
      </tr>
      {if $mode eq 'input'}
      <tr>
           <td>{$pause.LABEL}:</td>
           <td>{$pause.INPUT}</td>
      </tr>
      <tr>
           <td colspan='4'>&nbsp;</td>
      </tr>
      <tr>
    	<td>{$PHONE_FILE}: </td>
    	<td  colspan='4'><input type='file' name='phonefile'></td>
      </tr>
      <tr>
           <td>{$use_list.LABEL}:</td>
           <td>{$use_list.INPUT}</td>
      </tr>
      {/if}
      {/if}
      <tr>
           <td>{$trunk.LABEL}:</td>
           <td>{$trunk.INPUT}</td>
      </tr>
      </table>
    </td>
  </tr>
</table>
<input type="hidden" name="id_campaign" id='id_campaign' value="{$id_campaign}" />
<input type="hidden" name="values_form" id='values_form' value="" />    
</form>
{literal}
    <script language="JavaScript" src="modules/ixx_sms_send/libs/sms_{/literal}{$language}{literal}.js"></script>
    <script language="JavaScript" src="modules/ixx_sms_send/libs/sms.js"></script>
    <script language="JavaScript">contar('campaign','message','count','messages');</script>
{/literal}
