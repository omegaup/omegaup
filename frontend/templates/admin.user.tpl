{include file='redirect.tpl' inline}
{include file='head.tpl' htmlTitle="{#omegaupTitleAdminUsers#}" inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="admin-user"></div>

<script type="text/javascript" src="{version_hash src="/js/dist/admin_user.js"}"></script>
{include file='footer.tpl' inline}
