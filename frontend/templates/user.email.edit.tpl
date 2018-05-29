{extends file="profile.tpl"}
{block name="content"}
<script type="text/json" id="profile">{$profile.userinfo|json_encode}</script></script>
<div id="userEmailEdit"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/user_edit_email_form.js"}"></script>
{/block}
 