{include file='redirect.tpl' inline}
{include file='head.tpl' htmlTitle="{#omegaupTitleAdminUsers#}" inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="admin-user"></div>

{js_include entrypoint="admin_user"}
{include file='footer.tpl' inline}
