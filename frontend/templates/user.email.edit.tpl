{extends file="profile.tpl"}
{block name="content"}
<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="userEmailEdit"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/user_edit_email_form.js"}"></script>
{/block}
