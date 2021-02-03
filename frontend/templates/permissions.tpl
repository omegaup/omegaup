{include file='redirect.tpl' inline}
{include file='head.tpl' htmlTitle="{#omegaupTitleUpdatePrivileges#}" inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="user-roles"></div>

{js_include entrypoint="admin_roles"}
{include file='footer.tpl' inline}