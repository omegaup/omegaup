{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#omegaupTitleUpdatePrivileges#}"}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="user-roles"></div>

<script type="text/javascript" src="{version_hash src="/js/dist/admin_roles.js"}"></script>
{include file='footer.tpl'}