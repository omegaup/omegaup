{extends file="profile.tpl"}
{block name="content"}
<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="user-email-edit"></div>
{js_include entrypoint="user_edit_email_form"}
{/block}
