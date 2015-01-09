<script language="JavaScript" type="text/javascript" src="{$relative_dir_rich_text}/richtext/html2xhtml.js"></script>
<script language="JavaScript" type="text/javascript" src="{$relative_dir_rich_text}/richtext/richtext_compressed.js"></script>
<script language="JavaScript" type="text/javascript">
//Usage: initRTE(imagesPath, includesPath, cssFile, genXHTML, encHTML)
initRTE("./{$relative_dir_rich_text}/richtext/images/", "./{$relative_dir_rich_text}/richtext/", "", true);
var rte_script = new richTextEditor('rte_script');
</script>

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
	  {if $mode eq 'input'}
          <input class="button" type="submit" name="save" value="{$SAVE}"">
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
          <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
          {elseif $mode eq 'edit'}
	  <input class="button" type="submit" name="apply_changes" value="{$APPLY_CHANGES}" onclick="return enviar_datos();">
          <input class="button" type="submit" name="delete" value="{$DELETE}" onClick="return confirmSubmit('{$CONFIRM_DELETE}');">
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
          <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
          {else}
          <input class="button" type="submit" name="edit" value="{$EDIT}">
          <input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_DELETE}');">
          <input class="button" type="button" name="cancel_view" value="{$CANCEL}" onclick="window.open('?menu=config_out','_parent');"></td>
          {/if}          
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
          <td>{$name.LABEL}: <span  class="required">*</span></td>
          <td colspan='2'>{$name.INPUT}</td>
      </tr>
      <tr>
          <td>{$active.LABEL}:</td>
          <td>{$active.INPUT}</td>
      </tr>
      <tr>
          <td>{$service_type.LABEL}: <span  class="required">*</span></td>
          <td>{html_options name=service_type id=service_type options=$service_types onchange="service_type_changed();" selected=$current_service_type}&nbsp;&nbsp;<span id="provider">{$providers}</span></td>
      </tr>
      <tr>
          <td id="opt_server1">{$server.LABEL}: <span  class="required">*</span></td>
          <td id="opt_server2">{$server.INPUT}</td>
      </tr>
      <tr>
          <td id="opt_user1">{$user.LABEL}: <span  class="required">*</span></td>
          <td id="opt_user2">{$user.INPUT}</td>
      </tr>
      <tr>
          <td id="opt_password1">{$password.LABEL}: <span  class="required">*</span></td>
          <td id="opt_password2">{$password.INPUT}</td>
      </tr>
      <tr>
          <td id="opt_port1">{$port.LABEL}: <span  class="required">*</span></td>
          <td id="opt_port2">{$port.INPUT}</td>
      </tr>
      <tr>
          <td id="opt_script1">{$script.LABEL}: <span  class="required">*</span></td>
          <td id="opt_script2">{$script.INPUT}</td>
      </tr>
      <tr>
          <td>{$append_country_code.LABEL}:</td>
          <td>{$append_country_code.INPUT}</td>
      </tr>
      <tr>
          <td id="opt_system_type1">{$system_type.LABEL}:</td>
          <td id="opt_system_type2">{$system_type.INPUT}</td>
      </tr>
      <tr>
          <td>{$clid.LABEL}:</td>
          <td>{$clid.INPUT}</td>
      </tr>
      <tr>
          <td>{$trunk_priority.LABEL}:</td>
          <td>{$trunk_priority.INPUT}</td>
      </tr>
      </table>
    </td>
  </tr>
</table>
<input type="hidden" name="id_trunk" id='id_trunk' value="{$id_trunk}" />
</form>
{literal}
<script language="JavaScript">
function service_type_changed() {
    var type = document.getElementById('service_type').value;

    xajax_retrieve_providers(type);
}

function provider_changed() {
    var provider = document.getElementById('provider_select').value;

    xajax_retrieve_provider(provider);
}
</script>
{/literal}
