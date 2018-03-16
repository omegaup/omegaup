{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#omegaupTitleSupportDashboard#}"}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="admin-support"></div>

<script type="text/javascript" src="{version_hash src="/js/dist/admin_support.js"}"></script>
{include file='footer.tpl'}
