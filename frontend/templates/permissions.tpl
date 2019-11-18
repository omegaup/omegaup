{include file='redirect.tpl' inline}
{include file='head.tpl' htmlTitle="{#omegaupTitleUpdatePrivileges#}" inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="user-roles"></div>

<script type="text/javascript" src="{version_hash src="/js/dist/admin_roles.js"}"></script>
{include file='footer.tpl' inline}